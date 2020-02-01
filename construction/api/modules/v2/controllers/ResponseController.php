<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Profile;
use api\modules\v2\models\ProfileRROD;
use api\modules\v2\models\Response;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * API Response controller
 */
class ResponseController extends Controller
{
    /**
     * Constants
     */

    const CHECK_RIGHTS_RBAC = false;  // Enable check rights by rbac model

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'view' => ['get'],
                ],
                'actions' => [
                    'create' => ['post'],
                ],
                'actions' => [
                    'update' => ['put', 'patch'],
                ],
                'actions' => [
                    'delete' => ['delete'],
                ],
                'actions' => [
                    'delete-by-param' => ['delete'],
                ],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // restrict access to
                    'Origin' => ['*'],
                    // Allow only POST and PUT methods
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    // Allow only headers 'X-Wsse'
                    //'Access-Control-Request-Headers' => ['X-Wsse'],
                    //'Access-Control-Request-Headers' => ['*'],
                    // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                    'Access-Control-Allow-Credentials' => true,
                    //
                    'Access-Control-Allow-Headers' => ['authorization', 'DNT', 'User-Agent', 'Keep-Alive', 'Content-Type', 'accept,orig'],
                    // Allow OPTIONS caching
                    //'Access-Control-Max-Age' => 3600,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    //'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                    //'Access-Control-Expose-Headers' => [],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }


    /**
     * GET Method. Response table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        $getParams = Yii::$app->getRequest()->get();

        // check user is a guest
        if (array_key_exists('token', $getParams)) {
            $userByToken = \Yii::$app->user->loginByAccessToken($getParams['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }
        } else {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        // Get array with user Roles
        $userRole =[];
        $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
        foreach($userAssigned as $userAssign){
            array_push($userRole, $userAssign->roleName);
        }
        //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

        // Check rights
        // If user have create right that his allowed to other actions to the Response table
        if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor') && !\Yii::$app->user->can('createMediator')) {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        }
        /*
        $flagRights = false;
        foreach(array('admin', 'customer', 'contractor', 'mediator') as $value) {
            if (in_array($value, $userRole)) {
                $flagRights = true;
            }
        }
        if (!$flagRights) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        */

        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayResponseAssoc = array ('id' => 'id', 'status_response_id' => 'status_response_id', 'request_id' => 'request_id', 'description' => 'description', 'cost' => 'cost', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            if (in_array('admin', $userRole)) {
                $query = Response::find();  // get all records
            } else {
                $query = Response::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Response();
            foreach ($arrayResponseAssoc as $nameResponseAssoc => $valueResponseAssoc) {
                if (array_key_exists($valueResponseAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameResponseAssoc)) {
                        $modelValidate->$nameResponseAssoc = $getParams[$arrayResponseAssoc[$nameResponseAssoc]];
                        if (!$modelValidate->validate($nameResponseAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameResponseAssoc => $getParams[$arrayResponseAssoc[$nameResponseAssoc]]]);
                    }
                }
            }

            $modelResponse = $query->orderBy('id')
                ->with('profiles')
                ->with('statusResponse')
                ->asArray()
                ->all();

            // get properties from Response object
            $RequestResponse = array(
                'method' => 'GET',
                'status' => 0,
                'type' => 'success',
                'id' => $modelResponse[0]['id'],
                'status_response' => $modelResponse[0]['statusResponse']['name'],
                'request_id' => $modelResponse[0]['request_id'],
                'description' => $modelResponse[0]['description'],
                'cost' => $modelResponse[0]['cost'],
                'period' => $modelResponse[0]['period'],
                'profile_id' => $modelResponse[0]['profiles']['id'],
                'fio' => $modelResponse[0]['profiles']['fio'],
                'firm_name' => $modelResponse[0]['profiles']['firm_name'],
                'avatar' => $modelResponse[0]['profiles']['avatar']
            );
            //array_push($RequestResponse, ArrayHelper::toArray($modelResponse));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole)) {
                $query = Response::find();  // get all records
            } else {
                $query = Response::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelResponse = $query->orderBy('id')
                ->with('profiles')
                ->with('statusResponse')
                ->asArray()
                ->all();

            // get properties from Response object
            $RequestResponse = array(
                'method' => 'GET',
                'status' => 0,
                'type' => 'success',
                'id' => $modelResponse[0]['id'],
                'status_response' => $modelResponse[0]['statusResponse']['name'],
                'request_id' => $modelResponse[0]['request_id'],
                'description' => $modelResponse[0]['description'],
                'cost' => $modelResponse[0]['cost'],
                'period' => $modelResponse[0]['period'],
                'profile_id' => $modelResponse[0]['profiles'][0]['id'],
                'fio' => $modelResponse[0]['profiles'][0]['fio'],
                'firm_name' => $modelResponse[0]['profiles']['firm_name'],
                'avatar' => $modelResponse[0]['profiles'][0]['avatar']
            );
            //array_push($RequestResponse, ArrayHelper::toArray($modelResponse));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. Response table.
     * Insert records
     *
     * @return json
     */
    public function actionCreate()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor') && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayResponseAssoc = array ('id' => 'id', 'status_response_id' => 'status_response_id', 'request_id' => 'request_id', 'description' => 'description', 'cost' => 'cost', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            $modelResponse = new Response();

            // fill in the properties in the Response object
            foreach ($arrayResponseAssoc as $nameResponseAssoc => $valueResponseAssoc) {
                if (array_key_exists($valueResponseAssoc, $bodyRaw)) {
                    if ($modelResponse->hasAttribute($nameResponseAssoc)) {
                        if ($nameResponseAssoc != 'id') {
                            $modelResponse->$nameResponseAssoc = $bodyRaw[$valueResponseAssoc];

                            if (!$modelResponse->validate($nameResponseAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueResponseAssoc));

                            $modelResponse->created_by = $userByToken->id;
                            $modelResponse->created_at = time();
                            $modelResponse->updated_at = time();
                        }
                    }
                }
            }

            // Search record by id user in the profile table
            $queryProfile = Profile::find()->where(['user_id' => $userByToken->id]);
            $modelProfile = $queryProfile->one();

            if ($modelResponse->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagResponse = $modelResponse->save(false); // insert into Response table

                    if ($flagResponse) {
                        // Save record into profile_rrod table
                        $modelResponse->link('profiles', $modelProfile);
                    }

                    if ($flagResponse == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть сохранен'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть сохранен'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Отклик успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelResponse))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Отклик успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. Response table.
     * Update record by id parameter
     *
     * @return json
     */
    public function actionUpdate()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Spacialization table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor') && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию обновления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayResponseAssoc = array ('id' => 'id', 'status_response_id' => 'status_response_id', 'request_id' => 'request_id', 'description' => 'description', 'cost' => 'cost', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            if (array_key_exists($arrayResponseAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayResponseAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database                
                if (in_array('admin', $userRole)) {
                    $queryResponse = Response::find()->where(['id' => $bodyRaw[$arrayResponseAssoc['id']]]);  // get all records
                } else {
                    $queryResponse = Response::find()->where(['AND', ['id' => $bodyRaw[$arrayResponseAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelResponse = $queryResponse->one();

                if (empty($modelResponse)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Отклик по id'));
                }

                foreach ($arrayResponseAssoc as $nameResponseAssoc => $valueResponseAssoc) {
                    if (array_key_exists($valueResponseAssoc, $bodyRaw)) {
                        if ($modelResponse->hasAttribute($nameResponseAssoc)) {
                            $modelResponse->$nameResponseAssoc = $bodyRaw[$arrayResponseAssoc[$nameResponseAssoc]];
                            if (!$modelResponse->validate($nameResponseAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelResponse->created_by = $userByToken->id;
                            $modelResponse->updated_at = time();
                        }
                    }
                }

                // Search record by id user in the profile table
                $queryProfile = Profile::find()->where(['user_id' => $userByToken->id]);
                $modelProfile = $queryProfile->one();

                // Save Response object
                if ($modelResponse->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagResponse = $modelResponse->save(false); // update Response table

                        // delete old records from profile_rrod table
                        ProfileRROD::deleteAll(['response_id' => $modelResponse->id]);
                        // Save record into profile_rrod table
                        $modelResponse->link('profiles', $modelProfile);

                        if ($flagResponse) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть обновлен'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть обновлен'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Отклик успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. Response table.
     * Delete records by id parameter
     * or by another parameters
     *
     * @return json
     */
    public function actionDelete()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Spacialization table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor') && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayResponseAssoc = array ('id' => 'id', 'status_response_id' => 'status_response_id', 'request_id' => 'request_id', 'description' => 'description', 'cost' => 'cost', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            if (array_key_exists($arrayResponseAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayResponseAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryResponse = Response::find()->where(['id' => $bodyRaw[$arrayResponseAssoc['id']]]);  // get all records
                } else {
                    $queryResponse = Response::find()->where(['AND', ['id' => $bodyRaw[$arrayResponseAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelResponse = $queryResponse->one();

                if (empty($modelResponse)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Отклик по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelResponse)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Response table
                    $countResponseDelete = $modelResponse->delete($modelResponse->id);

                    // delete old records from profile_rrod table
                    ProfileRROD::deleteAll(['response_id' => $modelResponse->id]);

                    if ($countResponseDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Отклик успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. Response table.
     * Delete records by another parameters
     *
     * @return json
     */
    public function actionDeleteByParam()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        if (is_array($bodyRaw)) {
            if (array_key_exists('token', $bodyRaw)) {
                $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
                if (empty($userByToken)) {
                    //return $this->goHome();
                    return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Get array with user Roles
            $userRole =[];
            $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
            foreach($userAssigned as $userAssign){
                array_push($userRole, $userAssign->roleName);
            }
            //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

            // Check rights
            // If user have create right that his allowed to other actions to the Spacialization table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor') && !\Yii::$app->user->can('createMediator')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (!$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayResponseAssoc = array ('id' => 'id', 'status_response_id' => 'status_response_id', 'request_id' => 'request_id', 'description' => 'description', 'cost' => 'cost', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryResponse = Response::find();  // get all records
            } else {
                $queryResponse = Response::find()->where(['created_by'=> $userByToken->id]);   // get records created by this user
            }
            $modelValidate = new Response();
            foreach ($arrayResponseAssoc as $nameResponseAssoc => $valueResponseAssoc) {
                if (array_key_exists($valueResponseAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameResponseAssoc)) {
                        $modelValidate->$nameResponseAssoc = $bodyRaw[$arrayResponseAssoc[$nameResponseAssoc]];
                        if (!$modelValidate->validate($nameResponseAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueResponseAssoc));

                        $queryResponse->andWhere([$nameResponseAssoc => $bodyRaw[$arrayResponseAssoc[$nameResponseAssoc]]]);
                    }
                }

            }
            $modelsResponse = $queryResponse->all();

            if (!empty($modelsResponse) && !empty($modelValidate)) {
                foreach ($modelsResponse as $modelResponse) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from Response table.
                         $countResponseDelete = $modelResponse->delete();

                        // delete old records from profile_rrod table
                        ProfileRROD::deleteAll(['response_id' => $modelResponse->id]);

                        if ($countResponseDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть удален'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отклик не может быть удален'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Отклик успешно удален'));
            }
        }
    }

}
