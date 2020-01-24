<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\DeparturePlace;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * API DeparturePlace controller
 */
class DeparturePlaceController extends Controller
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
        ];
    }


    /**
     * GET Method. DeparturePlace table.
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
        $userRole = [];
        $userAssigned = Yii::$app->authManager->getAssignments($userByToken->id);
        foreach($userAssigned as $userAssign){
            array_push($userRole, $userAssign->roleName);
        }
        //return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => $userRole));

        // Check rights
        // If user have create right that his allowed to other actions to the DeparturePlace table
        if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createProvider') && !\Yii::$app->user->can('createMediator')) {
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
            $arrayDeparturePlaceAssoc = array ('id' => 'id', 'city_id' => 'city_id', 'name' => 'name');

            if (in_array('admin', $userRole)) {
                $query = DeparturePlace::find();  // get all records
            } else {
                $query = DeparturePlace::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new DeparturePlace();
            foreach ($arrayDeparturePlaceAssoc as $nameDeparturePlaceAssoc => $valueDeparturePlaceAssoc) {
                if (array_key_exists($valueDeparturePlaceAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameDeparturePlaceAssoc)) {
                        $modelValidate->$nameDeparturePlaceAssoc = $getParams[$arrayDeparturePlaceAssoc[$nameDeparturePlaceAssoc]];
                        if (!$modelValidate->validate($nameDeparturePlaceAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameDeparturePlaceAssoc => $getParams[$arrayDeparturePlaceAssoc[$nameDeparturePlaceAssoc]]]);
                    }
                }
            }

            $modelDeparturePlace = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from DeparturePlace object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelDeparturePlace));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole)) {
                $query = DeparturePlace::find();  // get all records
            } else {
                $query = DeparturePlace::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelDeparturePlace = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from DeparturePlace object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelDeparturePlace));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. DeparturePlace table.
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createProvider')) {
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
            $arrayDeparturePlaceAssoc = array ('id' => 'id', 'city_id' => 'city_id', 'name' => 'name');

            $modelDeparturePlace = new DeparturePlace();

            // fill in the properties in the DeparturePlace object
            foreach ($arrayDeparturePlaceAssoc as $nameDeparturePlaceAssoc => $valueDeparturePlaceAssoc) {
                if (array_key_exists($valueDeparturePlaceAssoc, $bodyRaw)) {
                    if ($modelDeparturePlace->hasAttribute($nameDeparturePlaceAssoc)) {
                        if ($nameDeparturePlaceAssoc != 'id') {
                            $modelDeparturePlace->$nameDeparturePlaceAssoc = $bodyRaw[$valueDeparturePlaceAssoc];

                            if (!$modelDeparturePlace->validate($nameDeparturePlaceAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueDeparturePlaceAssoc));

                            $modelDeparturePlace->created_by = $userByToken->id;
                        }
                    }
                }
            }

            if ($modelDeparturePlace->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagDeparturePlace = $modelDeparturePlace->save(false); // insert into DeparturePlace table

                    if ($flagDeparturePlace == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть сохранено'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть сохранено'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Место отправления успешно сохранено', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDeparturePlace))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Место отправления успешно сохранено'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. DeparturePlace table.
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createProvider')) {
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
            $arrayDeparturePlaceAssoc = array ('id' => 'id', 'city_id' => 'city_id', 'name' => 'name');

            if (array_key_exists($arrayDeparturePlaceAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayDeparturePlaceAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database                
                if (in_array('admin', $userRole)) {
                    $queryDeparturePlace = DeparturePlace::find()->where(['id' => $bodyRaw[$arrayDeparturePlaceAssoc['id']]]);  // get all records
                } else {
                    $queryDeparturePlace = DeparturePlace::find()->where(['AND', ['id' => $bodyRaw[$arrayDeparturePlaceAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelDeparturePlace = $queryDeparturePlace->one();

                if (empty($modelDeparturePlace)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдено Место отправления по id'));
                }

                foreach ($arrayDeparturePlaceAssoc as $nameDeparturePlaceAssoc => $valueDeparturePlaceAssoc) {
                    if (array_key_exists($valueDeparturePlaceAssoc, $bodyRaw)) {
                        if ($modelDeparturePlace->hasAttribute($nameDeparturePlaceAssoc)) {
                            $modelDeparturePlace->$nameDeparturePlaceAssoc = $bodyRaw[$arrayDeparturePlaceAssoc[$nameDeparturePlaceAssoc]];
                            if (!$modelDeparturePlace->validate($nameDeparturePlaceAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelDeparturePlace->created_by = $userByToken->id;
                        }
                    }
                }

                // Save DeparturePlace object
                if ($modelDeparturePlace->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagDeparturePlace = $modelDeparturePlace->save(false); // update DeparturePlace table

                        if ($flagDeparturePlace) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть обновлено'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть обновлено'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Место отправления успешно сохранено'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. DeparturePlace table.
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
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createProvider')) {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
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
            $arrayDeparturePlaceAssoc = array ('id' => 'id', 'city_id' => 'city_id', 'name' => 'name');

            if (array_key_exists($arrayDeparturePlaceAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayDeparturePlaceAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryDeparturePlace = DeparturePlace::find()->where(['id' => $bodyRaw[$arrayDeparturePlaceAssoc['id']]]);  // get all records
                } else {
                    $queryDeparturePlace = DeparturePlace::find()->where(['AND', ['id' => $bodyRaw[$arrayDeparturePlaceAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelDeparturePlace = $queryDeparturePlace->one();

                if (empty($modelDeparturePlace)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдено Место отправления по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelDeparturePlace)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from DeparturePlace table
                    $countDeparturePlaceDelete = $modelDeparturePlace->delete($modelDeparturePlace->id);

                    if ($countDeparturePlaceDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Место отправления успешно удалено'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. DeparturePlace table.
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
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
                }
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createProvider')) {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
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
            $arrayDeparturePlaceAssoc = array ('id' => 'id', 'city_id' => 'city_id', 'name' => 'name');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryDeparturePlace = DeparturePlace::find();  // get all records
            } else {
                $queryDeparturePlace = DeparturePlace::find()->where(['created_by'=> $userByToken->id]);   // get records created by this user
            }
            $modelValidate = new DeparturePlace();
            foreach ($arrayDeparturePlaceAssoc as $nameDeparturePlaceAssoc => $valueDeparturePlaceAssoc) {
                if (array_key_exists($valueDeparturePlaceAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameDeparturePlaceAssoc)) {
                        $modelValidate->$nameDeparturePlaceAssoc = $bodyRaw[$arrayDeparturePlaceAssoc[$nameDeparturePlaceAssoc]];
                        if (!$modelValidate->validate($nameDeparturePlaceAssoc)) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueDeparturePlaceAssoc));

                        $queryDeparturePlace->andWhere([$nameDeparturePlaceAssoc => $bodyRaw[$arrayDeparturePlaceAssoc[$nameDeparturePlaceAssoc]]]);
                    }
                }

            }
            $modelsDeparturePlace = $queryDeparturePlace->all();

            if (!empty($modelsDeparturePlace) && !empty($modelValidate)) {
                foreach ($modelsDeparturePlace as $modelDeparturePlace) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from DeparturePlace table.
                         $countDeparturePlaceDelete = $modelDeparturePlace->delete();

                        if ($countDeparturePlaceDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Место отправления успешно удалено'));
            }
        }
    }

}
