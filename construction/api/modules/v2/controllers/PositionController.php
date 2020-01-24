<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Position;
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
 * API Position controller
 */
class PositionController extends Controller
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
     * GET Method. Position table.
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
        // If user have create right that his allowed to other actions to the Position table
        if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        }
        /*
        $flagRights = false;
        foreach(array('admin', 'customer', 'contractor', 'mediator') as $value) {
            if (in_array($value, $userRole)) {
                $flagRights = true;
            }
        }
        if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию просмотра'));
        */
        
        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPositionAssoc = array ('id' => 'id', 'portfolio_id' => 'portfolio_id', 'description' => 'description');

            $query = Position::find();
            $modelValidate = new Position();
            foreach ($arrayPositionAssoc as $namePositionAssoc => $valuePositionAssoc) {
                if (array_key_exists($valuePositionAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($namePositionAssoc)) {
                        $modelValidate->$namePositionAssoc = $getParams[$arrayPositionAssoc[$namePositionAssoc]];
                        if (!$modelValidate->validate($namePositionAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valuePositionAssoc));

                        $query->andWhere([$namePositionAssoc => $getParams[$arrayPositionAssoc[$namePositionAssoc]]]);
                    }
                }
            }

            $modelPosition = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Position object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelPosition));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            $query = Position::find();

            $modelPosition = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from Position object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelPosition));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. Position table.
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
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */
            
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPositionAssoc = array ('id' => 'id', 'portfolio_id' => 'portfolio_id', 'description' => 'description');

            $modelPosition = new Position();

            // fill in the properties in the Position object
            foreach ($arrayPositionAssoc as $namePositionAssoc => $valuePositionAssoc) {
                if (array_key_exists($valuePositionAssoc, $bodyRaw)) {
                    if ($modelPosition->hasAttribute($namePositionAssoc)) {
                        if ($namePositionAssoc != 'id') {
                            $modelPosition->$namePositionAssoc = $bodyRaw[$valuePositionAssoc];

                            if (!$modelPosition->validate($namePositionAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valuePositionAssoc));

                            $modelPosition->created_by = $userByToken->id;
                        }
                    }
                }
            }

            if ($modelPosition->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagPosition = $modelPosition->save(false); // insert into Position table

                    if ($flagPosition == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть сохранена'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть сохранена'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Позиция успешно сохранена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelPosition))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Позиция успешно сохранена'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. Position table.
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
            // If user have create right that his allowed to other actions to the Position table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'PUT', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию обновления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */
            
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPositionAssoc = array ('id' => 'id', 'portfolio_id' => 'portfolio_id', 'description' => 'description');

            if (array_key_exists($arrayPositionAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayPositionAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryPosition = Position::find()->where(['id' => $bodyRaw[$arrayPositionAssoc['id']]]);
                $modelPosition = $queryPosition->one();

                if (empty($modelPosition)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Позиция по id'));
                }

                foreach ($arrayPositionAssoc as $namePositionAssoc => $valuePositionAssoc) {
                    if (array_key_exists($valuePositionAssoc, $bodyRaw)) {
                        if ($modelPosition->hasAttribute($namePositionAssoc)) {
                            $modelPosition->$namePositionAssoc = $bodyRaw[$arrayPositionAssoc[$namePositionAssoc]];
                            if (!$modelPosition->validate($namePositionAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelPosition->created_by = $userByToken->id;
                        }
                    }
                }

                // Save Position object
                if ($modelPosition->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagPosition = $modelPosition->save(false); // update Position table

                        if ($flagPosition) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть обновлена'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть обновлена'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Позиция успешно сохранена'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. Position table.
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
            // If user have create right that his allowed to other actions to the Position table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию добавления'));
            */
            
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPositionAssoc = array ('id' => 'id', 'portfolio_id' => 'portfolio_id', 'description' => 'description');

            if (array_key_exists($arrayPositionAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayPositionAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryPosition = Position::find()->where(['id' => $bodyRaw[$arrayPositionAssoc['id']]]);
                $modelPosition = $queryPosition->one();

                if (empty($modelPosition)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Позиция по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelPosition)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Position table
                    $countPositionDelete = $modelPosition->delete($modelPosition->id);

                    if ($countPositionDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть удалена'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть удалена'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Позиция успешно удалена'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть удалена'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. Position table.
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
            // If user have create right that his allowed to other actions to the Position table
            if (static::CHECK_RIGHTS_RBAC && !\Yii::$app->user->can('createCustomer') && !\Yii::$app->user->can('createContractor')) {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            }
            /*
            $flagRights = false;
            foreach(array('admin', 'customer', 'contractor') as $value) {
                if (in_array($value, $userRole)) {
                    $flagRights = true;
                }
            }
            if (static::CHECK_RIGHTS_RBAC && !$flagRights) return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Не хватает прав на операцию удаления'));
            */
            
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayPositionAssoc = array ('id' => 'id', 'portfolio_id' => 'portfolio_id', 'description' => 'description');

            // Search record by id in the database
            $queryPosition = Position::find();
            $modelValidate = new Position();
            foreach ($arrayPositionAssoc as $namePositionAssoc => $valuePositionAssoc) {
                if (array_key_exists($valuePositionAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($namePositionAssoc)) {
                        $modelValidate->$namePositionAssoc = $bodyRaw[$arrayPositionAssoc[$namePositionAssoc]];
                        if (!$modelValidate->validate($namePositionAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valuePositionAssoc));

                        $queryPosition->andWhere([$namePositionAssoc => $bodyRaw[$arrayPositionAssoc[$namePositionAssoc]]]);
                    }
                }

            }
            $modelsPosition = $queryPosition->all();

            if (!empty($modelsPosition) && !empty($modelValidate)) {
                foreach ($modelsPosition as $modelPosition) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from Position table.
                         $countPositionDelete = $modelPosition->delete();

                        if ($countPositionDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть удалена'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Позиция не может быть удалена'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Позиция успешно удалена'));
            }
        }
    }

}
