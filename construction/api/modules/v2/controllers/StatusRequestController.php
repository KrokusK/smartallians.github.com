<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\StatusRequest;
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
 * API StatusRequest controller
 */
class StatusRequestController extends Controller
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
     * GET Method. StatusRequest table.
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
        // If user have create right that his allowed to other actions to the StatusRequest table
        /*if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor')) {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        }
        */
        $flagRights = false;
        foreach(array('admin') as $value) {
            if (in_array($value, $userRole)) {
                $flagRights = true;
            }
        }
        if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));

        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayStatusRequestAssoc = array ('id' => 'id', 'name' => 'name');

            $query = StatusRequest::find();
            $modelValidate = new StatusRequest();
            foreach ($arrayStatusRequestAssoc as $nameStatusRequestAssoc => $valueStatusRequestAssoc) {
                if (array_key_exists($valueStatusRequestAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameStatusRequestAssoc)) {
                        $modelValidate->$nameStatusRequestAssoc = $getParams[$arrayStatusRequestAssoc[$nameStatusRequestAssoc]];
                        if (!$modelValidate->validate($nameStatusRequestAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameStatusRequestAssoc => $getParams[$arrayStatusRequestAssoc[$nameStatusRequestAssoc]]]);
                    }
                }
            }

            $modelStatusRequest = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from StatusRequest object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelStatusRequest));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            $query = StatusRequest::find();

            $modelStatusRequest = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from StatusRequest object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelStatusRequest));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. StatusRequest table.
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
            /*
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            }
            */
            $flagRights = false;
            foreach(array('admin') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayStatusRequestAssoc = array ('id' => 'id', 'name' => 'name');

            $modelStatusRequest = new StatusRequest();

            // fill in the properties in the StatusRequest object
            foreach ($arrayStatusRequestAssoc as $nameStatusRequestAssoc => $valueStatusRequestAssoc) {
                if (array_key_exists($valueStatusRequestAssoc, $bodyRaw)) {
                    if ($modelStatusRequest->hasAttribute($nameStatusRequestAssoc)) {
                        if ($nameStatusRequestAssoc != 'id') {
                            $modelStatusRequest->$nameStatusRequestAssoc = $bodyRaw[$valueStatusRequestAssoc];

                            if (!$modelStatusRequest->validate($nameStatusRequestAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueStatusRequestAssoc));
                        }
                    }
                }
            }

            if ($modelStatusRequest->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagStatusRequest = $modelStatusRequest->save(false); // insert into StatusRequest table

                    if ($flagStatusRequest == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть сохранен'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть сохранен'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Статус заявки успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelStatusRequest))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Статус заявки успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. StatusRequest table.
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
            /*
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию обновления'));
            }
            */
            $flagRights = false;
            foreach(array('admin') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayStatusRequestAssoc = array ('id' => 'id', 'name' => 'name');

            if (array_key_exists($arrayStatusRequestAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayStatusRequestAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryStatusRequest = StatusRequest::find()->where(['id' => $bodyRaw[$arrayStatusRequestAssoc['id']]]);
                $modelStatusRequest = $queryStatusRequest->one();

                if (empty($modelStatusRequest)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Статус заявки по id'));
                }

                foreach ($arrayStatusRequestAssoc as $nameStatusRequestAssoc => $valueStatusRequestAssoc) {
                    if (array_key_exists($valueStatusRequestAssoc, $bodyRaw)) {
                        if ($modelStatusRequest->hasAttribute($nameStatusRequestAssoc)) {
                            $modelStatusRequest->$nameStatusRequestAssoc = $bodyRaw[$arrayStatusRequestAssoc[$nameStatusRequestAssoc]];
                            if (!$modelStatusRequest->validate($nameStatusRequestAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));
                        }
                    }
                }

                // Save StatusRequest object
                if ($modelStatusRequest->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagStatusRequest = $modelStatusRequest->save(false); // update StatusRequest table

                        if ($flagStatusRequest) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть обновлен'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть обновлен'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Статус заявки успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. StatusRequest table.
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
            /*
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            */
            $flagRights = false;
            foreach(array('admin') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayStatusRequestAssoc = array ('id' => 'id', 'name' => 'name');

            if (array_key_exists($arrayStatusRequestAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayStatusRequestAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryStatusRequest = StatusRequest::find()->where(['id' => $bodyRaw[$arrayStatusRequestAssoc['id']]]);
                $modelStatusRequest = $queryStatusRequest->one();

                if (empty($modelStatusRequest)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Статус заявки по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelStatusRequest)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from StatusRequest table
                    $countStatusRequestDelete = $modelStatusRequest->delete($modelStatusRequest->id);

                    if ($countStatusRequestDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Статус заявки успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. StatusRequest table.
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
            /*
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            */
            $flagRights = false;
            foreach(array('admin') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayStatusRequestAssoc = array ('id' => 'id', 'name' => 'name');

            // Search record by id in the database
            $queryStatusRequest = StatusRequest::find();
            $modelValidate = new StatusRequest();
            foreach ($arrayStatusRequestAssoc as $nameStatusRequestAssoc => $valueStatusRequestAssoc) {
                if (array_key_exists($valueStatusRequestAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameStatusRequestAssoc)) {
                        $modelValidate->$nameStatusRequestAssoc = $bodyRaw[$arrayStatusRequestAssoc[$nameStatusRequestAssoc]];
                        if (!$modelValidate->validate($nameStatusRequestAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueStatusRequestAssoc));

                        $queryStatusRequest->andWhere([$nameStatusRequestAssoc => $bodyRaw[$arrayStatusRequestAssoc[$nameStatusRequestAssoc]]]);
                    }
                }

            }
            $modelsStatusRequest = $queryStatusRequest->all();

            if (!empty($modelsStatusRequest) && !empty($modelValidate)) {
                foreach ($modelsStatusRequest as $modelStatusRequest) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from StatusRequest table.
                         $countStatusRequestDelete = $modelStatusRequest->delete();

                        if ($countStatusRequestDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть удален'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус заявки не может быть удален'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Статус заявки успешно удален'));
            }
        }
    }

}
