<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\MaterialType;
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
 * API MaterialType controller
 */
class MaterialTypeController extends Controller
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
     * GET Method. MaterialType table.
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
        // If user have create right that his allowed to other actions to the MaterialType table
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
            $arrayMaterialTypeAssoc = array ('id' => 'id', 'name' => 'name');

            $query = MaterialType::find();
            $modelValidate = new MaterialType();
            foreach ($arrayMaterialTypeAssoc as $nameMaterialTypeAssoc => $valueMaterialTypeAssoc) {
                if (array_key_exists($valueMaterialTypeAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameMaterialTypeAssoc)) {
                        $modelValidate->$nameMaterialTypeAssoc = $getParams[$arrayMaterialTypeAssoc[$nameMaterialTypeAssoc]];
                        if (!$modelValidate->validate($nameMaterialTypeAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameMaterialTypeAssoc => $getParams[$arrayMaterialTypeAssoc[$nameMaterialTypeAssoc]]]);
                    }
                }
            }

            $modelMaterialType = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from MaterialType object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelMaterialType));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            $query = MaterialType::find();

            $modelMaterialType = $query->orderBy('id')
                ->asArray()
                ->all();

            // get properties from MaterialType object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelMaterialType));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. MaterialType table.
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
            $arrayMaterialTypeAssoc = array ('id' => 'id', 'name' => 'name');

            $modelMaterialType = new MaterialType();

            // fill in the properties in the MaterialType object
            foreach ($arrayMaterialTypeAssoc as $nameMaterialTypeAssoc => $valueMaterialTypeAssoc) {
                if (array_key_exists($valueMaterialTypeAssoc, $bodyRaw)) {
                    if ($modelMaterialType->hasAttribute($nameMaterialTypeAssoc)) {
                        if ($nameMaterialTypeAssoc != 'id') {
                            $modelMaterialType->$nameMaterialTypeAssoc = $bodyRaw[$valueMaterialTypeAssoc];

                            if (!$modelMaterialType->validate($nameMaterialTypeAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueMaterialTypeAssoc));
                        }
                    }
                }
            }

            if ($modelMaterialType->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flagMaterialType = $modelMaterialType->save(false); // insert into MaterialType table

                    if ($flagMaterialType == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть сохранен'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть сохранен'));
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Тип материала успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelMaterialType))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Тип материала успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. MaterialType table.
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
            $arrayMaterialTypeAssoc = array ('id' => 'id', 'name' => 'name');

            if (array_key_exists($arrayMaterialTypeAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayMaterialTypeAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryMaterialType = MaterialType::find()->where(['id' => $bodyRaw[$arrayMaterialTypeAssoc['id']]]);
                $modelMaterialType = $queryMaterialType->one();

                if (empty($modelMaterialType)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Тип материала по id'));
                }

                foreach ($arrayMaterialTypeAssoc as $nameMaterialTypeAssoc => $valueMaterialTypeAssoc) {
                    if (array_key_exists($valueMaterialTypeAssoc, $bodyRaw)) {
                        if ($modelMaterialType->hasAttribute($nameMaterialTypeAssoc)) {
                            $modelMaterialType->$nameMaterialTypeAssoc = $bodyRaw[$arrayMaterialTypeAssoc[$nameMaterialTypeAssoc]];
                            if (!$modelMaterialType->validate($nameMaterialTypeAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));
                        }
                    }
                }

                // Save MaterialType object
                if ($modelMaterialType->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagMaterialType = $modelMaterialType->save(false); // update MaterialType table

                        if ($flagMaterialType) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть обновлен'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть обновлен'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Тип материала успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. MaterialType table.
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
            $arrayMaterialTypeAssoc = array ('id' => 'id', 'name' => 'name');

            if (array_key_exists($arrayMaterialTypeAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayMaterialTypeAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryMaterialType = MaterialType::find()->where(['id' => $bodyRaw[$arrayMaterialTypeAssoc['id']]]);
                $modelMaterialType = $queryMaterialType->one();

                if (empty($modelMaterialType)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Тип материала по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelMaterialType)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from MaterialType table
                    $countMaterialTypeDelete = $modelMaterialType->delete($modelMaterialType->id);

                    if ($countMaterialTypeDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Тип материала успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. MaterialType table.
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
            $arrayMaterialTypeAssoc = array ('id' => 'id', 'name' => 'name');

            // Search record by id in the database
            $queryMaterialType = MaterialType::find();
            $modelValidate = new MaterialType();
            foreach ($arrayMaterialTypeAssoc as $nameMaterialTypeAssoc => $valueMaterialTypeAssoc) {
                if (array_key_exists($valueMaterialTypeAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameMaterialTypeAssoc)) {
                        $modelValidate->$nameMaterialTypeAssoc = $bodyRaw[$arrayMaterialTypeAssoc[$nameMaterialTypeAssoc]];
                        if (!$modelValidate->validate($nameMaterialTypeAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueMaterialTypeAssoc));

                        $queryMaterialType->andWhere([$nameMaterialTypeAssoc => $bodyRaw[$arrayMaterialTypeAssoc[$nameMaterialTypeAssoc]]]);
                    }
                }

            }
            $modelsMaterialType = $queryMaterialType->all();

            if (!empty($modelsMaterialType) && !empty($modelValidate)) {
                foreach ($modelsMaterialType as $modelMaterialType) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from MaterialType table.
                         $countMaterialTypeDelete = $modelMaterialType->delete();

                        if ($countMaterialTypeDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть удален'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тип материала не может быть удален'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Тип материала успешно удален'));
            }
        }
    }

}
