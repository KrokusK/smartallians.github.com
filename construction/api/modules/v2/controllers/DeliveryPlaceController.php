<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\DeliveryPlace;
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
 * API DeliveryPlace controller
 */
class DeliveryPlaceController extends Controller
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
     * GET Method. DeliveryPlace table.
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
        // If user have create right that his allowed to other actions to the DeliveryPlace table
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
            $arrayDeliveryPlaceAssoc = array ('city_id' => 'city_id', 'name' => 'name');

            if (in_array('admin', $userRole)) {
                $query = DeliveryPlace::find();  // get all records
            } else {
                $query = DeliveryPlace::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }
            $modelValidate = new DeliveryPlace();
            foreach ($arrayDeliveryPlaceAssoc as $nameDeliveryPlaceAssoc => $valueDeliveryPlaceAssoc) {
                if (array_key_exists($valueDeliveryPlaceAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameDeliveryPlaceAssoc)) {
                        $modelValidate->$nameDeliveryPlaceAssoc = $getParams[$arrayDeliveryPlaceAssoc[$nameDeliveryPlaceAssoc]];
                        if (!$modelValidate->validate($nameDeliveryPlaceAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameDeliveryPlaceAssoc => $getParams[$arrayDeliveryPlaceAssoc[$nameDeliveryPlaceAssoc]]]);
                    }
                }
            }

            $modelDeliveryPlace = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from DeliveryPlace object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelDeliveryPlace));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            if (in_array('admin', $userRole)) {
                $query = DeliveryPlace::find();  // get all records
            } else {
                $query = DeliveryPlace::find()->Where(['created_by' => $userByToken->id]);  // get records created by this user
            }

            $modelDeliveryPlace = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from DeliveryPlace object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelDeliveryPlace));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. DeliveryPlace table.
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
            $arrayDeliveryPlaceAssoc = array ('city_id' => 'city_id', 'name' => 'name');

            $modelDeliveryPlace = new DeliveryPlace();

            // fill in the properties in the DeliveryPlace object
            foreach ($arrayDeliveryPlaceAssoc as $nameDeliveryPlaceAssoc => $valueDeliveryPlaceAssoc) {
                if (array_key_exists($valueDeliveryPlaceAssoc, $bodyRaw)) {
                    if ($modelDeliveryPlace->hasAttribute($nameDeliveryPlaceAssoc)) {
                        if ($nameDeliveryPlaceAssoc != 'id') {
                            $modelDeliveryPlace->$nameDeliveryPlaceAssoc = $bodyRaw[$valueDeliveryPlaceAssoc];

                            if (!$modelDeliveryPlace->validate($nameDeliveryPlaceAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueDeliveryPlaceAssoc));

                            $modelDeliveryPlace->created_by = $userByToken->id;
                        }
                    }
                }
            }

            if ($modelDeliveryPlace->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagDeliveryPlace = $modelDeliveryPlace->save(false); // insert into DeliveryPlace table

                    if ($flagDeliveryPlace == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть сохранено'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть сохранено'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Место поставки успешно сохранено', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDeliveryPlace))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Место поставки успешно сохранено'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. DeliveryPlace table.
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
            $arrayDeliveryPlaceAssoc = array ('city_id' => 'city_id', 'name' => 'name');

            if (array_key_exists($arrayDeliveryPlaceAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayDeliveryPlaceAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database                
                if (in_array('admin', $userRole)) {
                    $queryDeliveryPlace = DeliveryPlace::find()->where(['id' => $bodyRaw[$arrayDeliveryPlaceAssoc['id']]]);  // get all records
                } else {
                    $queryDeliveryPlace = DeliveryPlace::find()->where(['AND', ['id' => $bodyRaw[$arrayDeliveryPlaceAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelDeliveryPlace = $queryDeliveryPlace->one();

                if (empty($modelDeliveryPlace)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдено Место поставки по id'));
                }

                foreach ($arrayDeliveryPlaceAssoc as $nameDeliveryPlaceAssoc => $valueDeliveryPlaceAssoc) {
                    if (array_key_exists($valueDeliveryPlaceAssoc, $bodyRaw)) {
                        if ($modelDeliveryPlace->hasAttribute($nameDeliveryPlaceAssoc)) {
                            $modelDeliveryPlace->$nameDeliveryPlaceAssoc = $bodyRaw[$arrayDeliveryPlaceAssoc[$nameDeliveryPlaceAssoc]];
                            if (!$modelDeliveryPlace->validate($nameDeliveryPlaceAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelDeliveryPlace->created_by = $userByToken->id;
                        }
                    }
                }

                // Save DeliveryPlace object
                if ($modelDeliveryPlace->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagDeliveryPlace = $modelDeliveryPlace->save(false); // update DeliveryPlace table

                        if ($flagDeliveryPlace) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть обновлено'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть обновлено'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Место поставки успешно сохранено'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. DeliveryPlace table.
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
            $arrayDeliveryPlaceAssoc = array ('city_id' => 'city_id', 'name' => 'name');

            if (array_key_exists($arrayDeliveryPlaceAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayDeliveryPlaceAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                if (in_array('admin', $userRole)) {
                    $queryDeliveryPlace = DeliveryPlace::find()->where(['id' => $bodyRaw[$arrayDeliveryPlaceAssoc['id']]]);  // get all records
                } else {
                    $queryDeliveryPlace = DeliveryPlace::find()->where(['AND', ['id' => $bodyRaw[$arrayDeliveryPlaceAssoc['id']]], ['created_by'=> $userByToken->id]]);   // get records created by this user
                }
                $modelDeliveryPlace = $queryDeliveryPlace->one();

                if (empty($modelDeliveryPlace)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдено Место поставки по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelDeliveryPlace)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from DeliveryPlace table
                    $countDeliveryPlaceDelete = $modelDeliveryPlace->delete($modelDeliveryPlace->id);

                    if ($countDeliveryPlaceDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть удалено'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть удалено'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Место поставки успешно удалено'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть удалено'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. DeliveryPlace table.
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
            $arrayDeliveryPlaceAssoc = array ('city_id' => 'city_id', 'name' => 'name');

            // Search record by id in the database
            if (in_array('admin', $userRole)) {
                $queryDeliveryPlace = DeliveryPlace::find();  // get all records
            } else {
                $queryDeliveryPlace = DeliveryPlace::find()->where(['created_by'=> $userByToken->id]);   // get records created by this user
            }
            $modelValidate = new DeliveryPlace();
            foreach ($arrayDeliveryPlaceAssoc as $nameDeliveryPlaceAssoc => $valueDeliveryPlaceAssoc) {
                if (array_key_exists($valueDeliveryPlaceAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameDeliveryPlaceAssoc)) {
                        $modelValidate->$nameDeliveryPlaceAssoc = $bodyRaw[$arrayDeliveryPlaceAssoc[$nameDeliveryPlaceAssoc]];
                        if (!$modelValidate->validate($nameDeliveryPlaceAssoc)) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueDeliveryPlaceAssoc));

                        $queryDeliveryPlace->andWhere([$nameDeliveryPlaceAssoc => $bodyRaw[$arrayDeliveryPlaceAssoc[$nameDeliveryPlaceAssoc]]]);
                    }
                }

            }
            $modelsDeliveryPlace = $queryDeliveryPlace->all();

            if (!empty($modelsDeliveryPlace) && !empty($modelValidate)) {
                foreach ($modelsDeliveryPlace as $modelDeliveryPlace) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from DeliveryPlace table.
                         $countDeliveryPlaceDelete = $modelDeliveryPlace->delete();

                        if ($countDeliveryPlaceDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть удалено'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Место поставки не может быть удалено'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Место поставки успешно удалено'));
            }
        }
    }

}
