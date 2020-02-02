<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\StatusDelivery;
use api\modules\v2\models\UserRequestData;
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
 * API StatusDelivery controller
 */
class StatusDeliveryController extends Controller
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
     * GET Method. StatusDelivery table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(array('admin'));
            // get request params
            $getParams = $modelUserRequestData->getRequestParams();
            // init model StatusDelivery
            $modelStatusDelivery = new StatusDelivery();
            // Search data
            return $modelStatusDelivery->getDataStatusDelivery($getParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }


    /**
     * POST Method. StatusDelivery table.
     * Insert records
     *
     * @return json
     */
    public function actionCreate()
    {

        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(array('admin'));
            // get request params
            $postParams = $modelUserRequestData->getRequestParams();
            // init model StatusDelivery
            $modelStatusDelivery = new StatusDelivery();
            // Save object by params
            return $modelStatusDelivery->addDataStatusDelivery($postParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * PUT, PATCH Method. StatusDelivery table.
     * Update record by id parameter
     *
     * @return json
     */
    public function actionUpdate()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(array('admin'));
            // get request params
            $putParams = $modelUserRequestData->getRequestParams();
            // Get model StatusDelivery by id
            $modelStatusDelivery = new StatusDelivery();
            $modelStatusDeliveryById = $modelStatusDelivery->getDataStatusDeliveryById($putParams);
            // Update object by id
            return $modelStatusDeliveryById->updateDataStatusDelivery($putParams);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * DELETE Method. StatusDelivery table.
     * Delete records by id parameter
     * or by another parameters
     *
     * @return json
     */
    public function actionDelete()
    {
        try {
            // init model with user and request params
            $modelUserRequestData = new UserRequestData();
            // Check rights
            $modelUserRequestData->checkUserRightsByRole(array('admin'));
            // get request params
            $delParams = $modelUserRequestData->getRequestParams();
            // Get model StatusDelivery by id
            $modelStatusDelivery = new StatusDelivery();
            $modelStatusDeliveryById = $modelStatusDelivery->getDataStatusDeliveryById($delParams, false);
            if (empty($modelStatusDeliveryById)) {
                // Delete object by other params
                return $modelStatusDelivery->deleteDataStatusDelivery($putParams);
            } else {
                // Delete object by id
                return $modelStatusDeliveryById->deleteDataStatusDelivery($putParams);
            }
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
    /*
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

            $flagRights = false;
            foreach(array('admin') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayStatusDeliveryAssoc = array ('id' => 'id', 'name' => 'name');

            if (array_key_exists($arrayStatusDeliveryAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayStatusDeliveryAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryStatusDelivery = StatusDelivery::find()->where(['id' => $bodyRaw[$arrayStatusDeliveryAssoc['id']]]);
                $modelStatusDelivery = $queryStatusDelivery->one();

                if (empty($modelStatusDelivery)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Статус поставки по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelStatusDelivery)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from StatusDelivery table
                    $countStatusDeliveryDelete = $modelStatusDelivery->delete($modelStatusDelivery->id);

                    if ($countStatusDeliveryDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус поставки не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус поставки не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Статус поставки успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус поставки не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }
    */

    /**
     * DELETE Method. StatusDelivery table.
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
            $arrayStatusDeliveryAssoc = array ('id' => 'id', 'name' => 'name');

            // Search record by id in the database
            $queryStatusDelivery = StatusDelivery::find();
            $modelValidate = new StatusDelivery();
            foreach ($arrayStatusDeliveryAssoc as $nameStatusDeliveryAssoc => $valueStatusDeliveryAssoc) {
                if (array_key_exists($valueStatusDeliveryAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameStatusDeliveryAssoc)) {
                        $modelValidate->$nameStatusDeliveryAssoc = $bodyRaw[$arrayStatusDeliveryAssoc[$nameStatusDeliveryAssoc]];
                        if (!$modelValidate->validate($nameStatusDeliveryAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueStatusDeliveryAssoc));

                        $queryStatusDelivery->andWhere([$nameStatusDeliveryAssoc => $bodyRaw[$arrayStatusDeliveryAssoc[$nameStatusDeliveryAssoc]]]);
                    }
                }

            }
            $modelsStatusDelivery = $queryStatusDelivery->all();

            if (!empty($modelsStatusDelivery) && !empty($modelValidate)) {
                foreach ($modelsStatusDelivery as $modelStatusDelivery) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from StatusDelivery table.
                         $countStatusDeliveryDelete = $modelStatusDelivery->delete();

                        if ($countStatusDeliveryDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус поставки не может быть удален'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Статус поставки не может быть удален'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Статус поставки успешно удален'));
            }
        }
    }

}
