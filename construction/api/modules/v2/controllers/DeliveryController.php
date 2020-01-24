<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Delivery;
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
 * API Delivery controller
 */
class DeliveryController extends Controller
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
     * GET Method. Delivery table.
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
        // If user have create right that his allowed to other actions to the Delivery table
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
            $arrayDeliveryAssoc = array ('departure_place_id' => 'departure_place_id', 'delivery_place_id' => 'delivery_place_id', 'status_delivery_id' => 'status_delivery_id', 'status_payment_id' => 'status_payment_id', 'cost' => 'cost');

            if (in_array('admin', $userRole)) {
                $query = Delivery::find();  // get all records
            } else {
                $query = Delivery::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new Delivery();
            foreach ($arrayDeliveryAssoc as $nameDeliveryAssoc => $valueDeliveryAssoc) {
                if (array_key_exists($valueDeliveryAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameDeliveryAssoc)) {
                        $modelValidate->$nameDeliveryAssoc = $getParams[$arrayDeliveryAssoc[$nameDeliveryAssoc]];
                        if (!$modelValidate->validate($nameDeliveryAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameDeliveryAssoc => $getParams[$arrayDeliveryAssoc[$nameDeliveryAssoc]]]);
                    }
                }
            }

            $modelDelivery = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Delivery object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelDelivery));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole)) {
                $query = Delivery::find();  // get all records
            } else {
                $query = Delivery::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelDelivery = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Delivery object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelDelivery));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. Delivery table.
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
            $arrayDeliveryAssoc = array ('departure_place_id' => 'departure_place_id', 'delivery_place_id' => 'delivery_place_id', 'status_delivery_id' => 'status_delivery_id', 'status_payment_id' => 'status_payment_id', 'cost' => 'cost');
            $modelDelivery = new Delivery();

            // fill in the properties in the Delivery object
            foreach ($arrayDeliveryAssoc as $nameDeliveryAssoc => $valueDeliveryAssoc) {
                if (array_key_exists($valueDeliveryAssoc, $bodyRaw)) {
                    if ($modelDelivery->hasAttribute($nameDeliveryAssoc)) {
                        if ($nameDeliveryAssoc != 'id') {
                            $modelDelivery->$nameDeliveryAssoc = $bodyRaw[$valueDeliveryAssoc];

                            if (!$modelDelivery->validate($nameDeliveryAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueDeliveryAssoc));

                            $modelDelivery->created_by = $userByToken->id;
                            $modelDelivery->created_at = time();
                            $modelDelivery->updated_at = time();
                        }
                    }
                }
            }

            if ($modelDelivery->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagDelivery = $modelDelivery->save(false); // insert into Delivery table

                    if ($flagDelivery == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть сохранена'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть сохранена'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Поставка успешно сохранена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDelivery))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Поставка успешно сохранена'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. Delivery table.
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
            $arrayDeliveryAssoc = array ('departure_place_id' => 'departure_place_id', 'delivery_place_id' => 'delivery_place_id', 'status_delivery_id' => 'status_delivery_id', 'status_payment_id' => 'status_payment_id', 'cost' => 'cost');
            
            if (array_key_exists($arrayDeliveryAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayDeliveryAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database                
                if (in_array('admin', $userRole)) {
                    $queryDelivery = Delivery::find()->where(['id' => $bodyRaw[$arrayDeliveryAssoc['id']]]);  // get all records
                } else {
                    $queryDelivery = Delivery::find()->where(['AND', ['id' => $bodyRaw[$arrayDeliveryAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelDelivery = $queryDelivery->one();

                if (empty($modelDelivery)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Поставка по id'));
                }

                foreach ($arrayDeliveryAssoc as $nameDeliveryAssoc => $valueDeliveryAssoc) {
                    if (array_key_exists($valueDeliveryAssoc, $bodyRaw)) {
                        if ($modelDelivery->hasAttribute($nameDeliveryAssoc)) {
                            $modelDelivery->$nameDeliveryAssoc = $bodyRaw[$arrayDeliveryAssoc[$nameDeliveryAssoc]];
                            if (!$modelDelivery->validate($nameDeliveryAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelDelivery->created_by = $userByToken->id;
                            $modelDelivery->updated_at = time();
                        }
                    }
                }

                // Save Delivery object
                if ($modelDelivery->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagDelivery = $modelDelivery->save(false); // update Delivery table

                        if ($flagDelivery) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть обновлена'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть обновлена'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Поставка успешно сохранена'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. Delivery table.
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
            $arrayDeliveryAssoc = array ('departure_place_id' => 'departure_place_id', 'delivery_place_id' => 'delivery_place_id', 'status_delivery_id' => 'status_delivery_id', 'status_payment_id' => 'status_payment_id', 'cost' => 'cost');
            
            if (array_key_exists($arrayDeliveryAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayDeliveryAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryDelivery = Delivery::find()->where(['id' => $bodyRaw[$arrayDeliveryAssoc['id']]]);  // get all records
                } else {
                    $queryDelivery = Delivery::find()->where(['AND', ['id' => $bodyRaw[$arrayDeliveryAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelDelivery = $queryDelivery->one();

                if (empty($modelDelivery)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Поставка по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelDelivery)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Delivery table
                    $countDeliveryDelete = $modelDelivery->delete($modelDelivery->id);

                    if ($countDeliveryDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Поставка успешно удалена'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. Delivery table.
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
            $arrayDeliveryAssoc = array ('departure_place_id' => 'departure_place_id', 'delivery_place_id' => 'delivery_place_id', 'status_delivery_id' => 'status_delivery_id', 'status_payment_id' => 'status_payment_id', 'cost' => 'cost');
            
            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryDelivery = Delivery::find();  // get all records
            } else {
                $queryDelivery = Delivery::find()->where(['created_by'=> $userByToken->id]);   // get records created by this user
            }
            $modelValidate = new Delivery();
            foreach ($arrayDeliveryAssoc as $nameDeliveryAssoc => $valueDeliveryAssoc) {
                if (array_key_exists($valueDeliveryAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameDeliveryAssoc)) {
                        $modelValidate->$nameDeliveryAssoc = $bodyRaw[$arrayDeliveryAssoc[$nameDeliveryAssoc]];
                        if (!$modelValidate->validate($nameDeliveryAssoc)) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueDeliveryAssoc));

                        $queryDelivery->andWhere([$nameDeliveryAssoc => $bodyRaw[$arrayDeliveryAssoc[$nameDeliveryAssoc]]]);
                    }
                }

            }
            $modelsDelivery = $queryDelivery->all();

            if (!empty($modelsDelivery) && !empty($modelValidate)) {
                foreach ($modelsDelivery as $modelDelivery) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from Delivery table.
                         $countDeliveryDelete = $modelDelivery->delete();

                        if ($countDeliveryDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Поставка успешно удалена'));
            }
        }
    }

}
