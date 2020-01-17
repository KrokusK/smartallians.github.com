<?php
namespace api\modules\v2\controllers;

use api\common\models\User;
use api\modules\v2\models\Specialization;
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
 * API Specialization controller
 */
class SpecializationController extends Controller
{
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
     * GET Method. Specialization table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        $getParams = Yii::$app->getRequest()->get();

        // check user is a guest
        $userByToken = \Yii::$app->user->loginByAccessToken($getParams['token']);
        if (empty($userByToken)) {
            //return $this->goHome();
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }
        $userRole = \Yii::$app->authManager->getRolesByUser($userByToken->id);

        unset($getParams['token']);

        if (count($getParams) > 0) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arraySpecializationAssoc = array ('id' => 'id', 'name' => 'name');

            $query = Specialization::find();
            $modelValidate = new Specialization();
            foreach ($arraySpecializationAssoc as $nameSpecializationAssoc => $valueSpecializationAssoc) {
                if (array_key_exists($valueSpecializationAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameSpecializationAssoc)) {
                        $modelValidate->$nameSpecializationAssoc = $getParams[$arraySpecializationAssoc[$nameSpecializationAssoc]];
                        if (!$modelValidate->validate($nameSpecializationAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameSpecializationAssoc => $getParams[$arraySpecializationAssoc[$nameSpecializationAssoc]]]);
                    }
                }
            }

            $modelSpecialization = $query->orderBy('id')
                ->asArray()
                ->with('requests','deliveries','materialType','statusMaterial')
                ->all();

            // get properties from Specialization object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelSpecialization));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            $query = Specialization::find();

            $modelSpecialization = $query->orderBy('id')
                ->asArray()
                ->with('requests','deliveries','materialType','statusMaterial')
                ->all();

            // get properties from Specialization object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelSpecialization));

            return Json::encode($RequestResponse);
        }
    }


    /**
     * POST Method. Specialization table.
     * Insert records
     *
     * @return json
     */
    public function actionCreate()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arraySpecializationAssoc = array ('id' => 'id', 'request_id' => 'request_id');
            $arraySubSpecializationAssoc = array ('delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost', 'measure' => 'measure');

            if (array_key_exists('Specialization', $bodyRaw)) {
                if (!is_array($bodyRaw['Specialization'])) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре Specialization ожидается массив'));

                $subBodyRaw = $bodyRaw['Specialization'];

                // fill in the properties in the Specialization object
                foreach ($subBodyRaw as $nameSubBodyRaw => $valueSubBodyRaw) {
                    $modelSpecialization = new Specialization();

                    // fill in the properties in the Specialization object from $subBodyRaw
                    foreach ($arraySubSpecializationAssoc as $nameSubSpecializationAssoc => $valueSubSpecializationAssoc) {
                        if (array_key_exists($valueSubSpecializationAssoc, $valueSubBodyRaw)) {

                            if ($modelSpecialization->hasAttribute($nameSubSpecializationAssoc)) {
                                $modelSpecialization->$nameSubSpecializationAssoc = $valueSubBodyRaw[$valueSubSpecializationAssoc];

                                if (!$modelSpecialization->validate($nameSubSpecializationAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueSubSpecializationAssoc));
                                else {

                                }
                            }
                        }
                    }

                    // fill in the properties in the Specialization object from $bodyRaw
                    foreach ($arraySpecializationAssoc as $nameSpecializationAssoc => $valueSpecializationAssoc) {
                        if (array_key_exists($valueSpecializationAssoc, $bodyRaw)) {
                            if ($modelSpecialization->hasAttribute($nameSpecializationAssoc)) {
                                if ($nameSpecializationAssoc != 'id') {
                                    $modelSpecialization->$nameSpecializationAssoc = $bodyRaw[$valueSpecializationAssoc];

                                    if (!$modelSpecialization->validate($nameSpecializationAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueSpecializationAssoc));

                                    $modelSpecialization->created_by = $userByToken->id;
                                }
                            }
                        }
                    }
                    //return Json::encode(ArrayHelper::toArray($modelSpecialization));

                    // Save Specialization object
                    if ($modelSpecialization->validate()) {
                        $transaction = \Yii::$app->db->beginTransaction();
                        try {
                            $flagSpecialization = $modelSpecialization->save(false); // insert into Specialization table

                            if ($flagSpecialization) {
                                $transaction->commit();
                            } else {
                                $transaction->rollBack();
                                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Материал не может быть сохранен'));
                            }
                        } catch (Exception $ex) {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Материал не может быть сохранен'));
                        }
                    } else {
                        return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                    }
                }

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Материал успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelSpecialization))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Материал успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отсутвтует параметр Specialization в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * PUT, PATCH Method. Specialization table.
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
            $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }
            $userRole = \Yii::$app->authManager->getRolesByUser($userByToken->id);

            if (array_key_exists('id', $bodyRaw)) {
                // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
                $arraySpecializationAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost', 'measure' => 'measure');

                // Search record by id in the database
                $querySpecialization = Specialization::find()->where(['id' => $bodyRaw[$arraySpecializationAssoc['id']]]);
                $modelSpecialization = $querySpecialization->one();

                if (empty($modelSpecialization)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Материал по id'));
                }

                foreach ($arraySpecializationAssoc as $nameSpecializationAssoc => $valueSpecializationAssoc) {
                    if (array_key_exists($valueSpecializationAssoc, $bodyRaw)) {
                        if ($modelSpecialization->hasAttribute($nameSpecializationAssoc)) {
                            $modelSpecialization->$nameSpecializationAssoc = $bodyRaw[$arraySpecializationAssoc[$nameSpecializationAssoc]];
                            if (!$modelSpecialization->validate($nameSpecializationAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelSpecialization->created_by = $userByToken->id;
                        }
                    }
                }

                // Save Specialization object
                if ($modelSpecialization->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagSpecialization = $modelSpecialization->save(false); // update Specialization table

                        if ($flagSpecialization) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Материал не может быть обновлен'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Материал не может быть обновлен'));
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
                }

                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 0, 'type' => 'success', 'message' => 'Материал успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id параметр в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
    }


    /**
     * DELETE Method. Specialization table.
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
            $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }
            $userRole = \Yii::$app->authManager->getRolesByUser($userByToken->id);

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arraySpecializationAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost', 'measure' => 'measure');

            if (array_key_exists($arraySpecializationAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arraySpecializationAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $querySpecialization = Specialization::find()->where(['id' => $bodyRaw[$arraySpecializationAssoc['id']]]);
                $modelSpecialization = $querySpecialization->one();

                if (empty($modelSpecialization)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Материал по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelSpecialization)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Specialization table
                    $countSpecializationDelete = $modelSpecialization->delete($modelSpecialization->id);

                    if ($countSpecializationDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Материал не может быть удален'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Материал не может быть удален'));
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Материал успешно удален'));
            } else {
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Материал не может быть удален'));
            }
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }

    /**
     * DELETE Method. Specialization table.
     * Delete records by another parameters
     *
     * @return json
     */
    public function actionDeleteByParam()
    {
        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        if (is_array($bodyRaw)) {
            // check user is a guest
            $userByToken = \Yii::$app->user->loginByAccessToken($bodyRaw['token']);
            if (empty($userByToken)) {
                //return $this->goHome();
                return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
            }
            $userRole = \Yii::$app->authManager->getRolesByUser($userByToken->id);

            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arraySpecializationAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost', 'measure' => 'measure');

            // Search record by id in the database
            $querySpecialization = Specialization::find();
            $modelValidate = new Specialization();
            foreach ($arraySpecializationAssoc as $nameSpecializationAssoc => $valueSpecializationAssoc) {
                if (array_key_exists($valueSpecializationAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameSpecializationAssoc)) {
                        $modelValidate->$nameSpecializationAssoc = $bodyRaw[$arraySpecializationAssoc[$nameSpecializationAssoc]];
                        if (!$modelValidate->validate($nameSpecializationAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueSpecializationAssoc));

                        $querySpecialization->andWhere([$nameSpecializationAssoc => $bodyRaw[$arraySpecializationAssoc[$nameSpecializationAssoc]]]);
                    }
                }

            }
            $modelsSpecialization = $querySpecialization->all();

            if (!empty($modelsSpecialization) && !empty($modelValidate)) {
                foreach ($modelsSpecialization as $modelSpecialization) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from Specialization table.
                         $countSpecializationDelete = $modelSpecialization->delete();

                        if ($countSpecializationDelete > 0) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявки не могут быть удалены'));
                        }
                    } catch (Exception $ex) {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявки не могут быть удалены'));
                    }
                }

                return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заявки успешно удалены'));
            }
        }
    }

}
