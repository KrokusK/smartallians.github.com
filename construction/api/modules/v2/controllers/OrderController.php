<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Order;
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
 * API Order controller
 */
class OrderController extends Controller
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
     * GET Method. Order table.
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
        // If user have create right that his allowed to other actions to the Order table
        if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createMediator')) {
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
            $arrayOrderAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'status_payment_id' => 'status_payment_id', 'status_completion_id' => 'status_completion_id', 'project_id' => 'project_id', 'feedback_id' => 'feedback_id');

            if (in_array('admin', $userRole)) {
                $query = Order::find();  // get all records
            } else {
                $query = Order::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Order();
            foreach ($arrayOrderAssoc as $nameOrderAssoc => $valueOrderAssoc) {
                if (array_key_exists($valueOrderAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameOrderAssoc)) {
                        $modelValidate->$nameOrderAssoc = $getParams[$arrayOrderAssoc[$nameOrderAssoc]];
                        if (!$modelValidate->validate($nameOrderAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameOrderAssoc => $getParams[$arrayOrderAssoc[$nameOrderAssoc]]]);
                    }
                }
            }

            $modelOrder = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Order object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelOrder));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole)) {
                $query = Order::find();  // get all records
            } else {
                $query = Order::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelOrder = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Order object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelOrder));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. Order table.
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createMediator')) {
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
            $arrayOrderAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'status_payment_id' => 'status_payment_id', 'status_completion_id' => 'status_completion_id', 'project_id' => 'project_id', 'feedback_id' => 'feedback_id');

            $modelOrder = new Order();

            // fill in the properties in the Order object
            foreach ($arrayOrderAssoc as $nameOrderAssoc => $valueOrderAssoc) {
                if (array_key_exists($valueOrderAssoc, $bodyRaw)) {
                    if ($modelOrder->hasAttribute($nameOrderAssoc)) {
                        if ($nameOrderAssoc != 'id') {
                            $modelOrder->$nameOrderAssoc = $bodyRaw[$valueOrderAssoc];

                            if (!$modelOrder->validate($nameOrderAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueOrderAssoc));

                            $modelOrder->created_by = $userByToken->id;
                            $modelOrder->created_at = time();
                            $modelOrder->updated_at = time();
                        }
                    }
                }
            }

            if ($modelOrder->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagOrder = $modelOrder->save(false); // insert into Order table

                    if ($flagOrder == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть сохранен'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть сохранен'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заказ успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelOrder))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заказ успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. Order table.
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
            if (static::CHECK_RIGHTS_RBAC !\Yii::$app->user->can('createMediator')) {
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
            $arrayOrderAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'status_payment_id' => 'status_payment_id', 'status_completion_id' => 'status_completion_id', 'project_id' => 'project_id', 'feedback_id' => 'feedback_id');

            if (array_key_exists($arrayOrderAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayOrderAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database                
                if (in_array('admin', $userRole)) {
                    $queryOrder = Order::find()->where(['id' => $bodyRaw[$arrayOrderAssoc['id']]]);  // get all records
                } else {
                    $queryOrder = Order::find()->where(['AND', ['id' => $bodyRaw[$arrayOrderAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelOrder = $queryOrder->one();

                if (empty($modelOrder)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Заказ по id'));
                }

                foreach ($arrayOrderAssoc as $nameOrderAssoc => $valueOrderAssoc) {
                    if (array_key_exists($valueOrderAssoc, $bodyRaw)) {
                        if ($modelOrder->hasAttribute($nameOrderAssoc)) {
                            $modelOrder->$nameOrderAssoc = $bodyRaw[$arrayOrderAssoc[$nameOrderAssoc]];
                            if (!$modelOrder->validate($nameOrderAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelOrder->created_by = $userByToken->id;
                            $modelOrder->updated_at = time();
                        }
                    }
                }

                // Save Order object
                if ($modelOrder->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagOrder = $modelOrder->save(false); // update Order table

                        if ($flagOrder) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть обновлен'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть обновлен'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Заказ успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. Order table.
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createMediator')) {
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
            $arrayOrderAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'status_payment_id' => 'status_payment_id', 'status_completion_id' => 'status_completion_id', 'project_id' => 'project_id', 'feedback_id' => 'feedback_id');

            if (array_key_exists($arrayOrderAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayOrderAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryOrder = Order::find()->where(['id' => $bodyRaw[$arrayOrderAssoc['id']]]);  // get all records
                } else {
                    $queryOrder = Order::find()->where(['AND', ['id' => $bodyRaw[$arrayOrderAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelOrder = $queryOrder->one();

                if (empty($modelOrder)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Заказ по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelOrder)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Order table
                    $countOrderDelete = $modelOrder->delete($modelOrder->id);

                    if ($countOrderDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заказ успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. Order table.
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createMediator')) {
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
            $arrayOrderAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'response_id' => 'response_id', 'status_payment_id' => 'status_payment_id', 'status_completion_id' => 'status_completion_id', 'project_id' => 'project_id', 'feedback_id' => 'feedback_id');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryOrder = Order::find();  // get all records
            } else {
                $queryOrder = Order::find()->where(['created_by'=> $userByToken->id]);   // get records created by this user
            }
            $modelValidate = new Order();
            foreach ($arrayOrderAssoc as $nameOrderAssoc => $valueOrderAssoc) {
                if (array_key_exists($valueOrderAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameOrderAssoc)) {
                        $modelValidate->$nameOrderAssoc = $bodyRaw[$arrayOrderAssoc[$nameOrderAssoc]];
                        if (!$modelValidate->validate($nameOrderAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueOrderAssoc));

                        $queryOrder->andWhere([$nameOrderAssoc => $bodyRaw[$arrayOrderAssoc[$nameOrderAssoc]]]);
                    }
                }

            }
            $modelsOrder = $queryOrder->all();

            if (!empty($modelsOrder) && !empty($modelValidate)) {
                foreach ($modelsOrder as $modelOrder) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from Order table.
                         $countOrderDelete = $modelOrder->delete();

                        if ($countOrderDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть удален'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заказ не может быть удален'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заказ успешно удален'));
            }
        }
    }

}
