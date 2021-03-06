<?php
namespace frontend\modules\v2\controllers;

use frontend\modules\v2\models\Materials;
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
 * API Materials controller
 */
class MaterialsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['view', 'create', 'update', 'delete', 'delete-by-param'],
                'rules' => [
                    [
                        'actions' => ['view', 'create', 'update', 'delete', 'delete-by-param'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
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
     * GET Method. Materials table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionView()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            //return $this->goHome();
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        //if (Yii::$app->request->isAjax) {

        $getParams = Yii::$app->getRequest()->get();

        if (is_array($getParams)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayMaterialsAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost');

            // Search record by id in the database
            //$query = Materials::find()->Where(['created_by' => Yii::$app->user->getId()]);
            $query = Materials::find();
            //foreach (ArrayHelper::toArray($model) as $key => $value) {
            //    $query->andWhere([$key => $value]);
            //}
            $modelValidate = new Materials();
            foreach ($arrayMaterialsAssoc as $nameMaterialsAssoc => $valueMaterialsAssoc) {
                if (array_key_exists($valueMaterialsAssoc, $getParams)) {
                    if ($modelValidate->hasAttribute($nameMaterialsAssoc)) {
                        $modelValidate->$nameMaterialsAssoc = $getParams[$arrayMaterialsAssoc[$nameMaterialsAssoc]];
                        if (!$modelValidate->validate($nameMaterialsAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                        $query->andWhere([$nameMaterialsAssoc => $getParams[$arrayMaterialsAssoc[$nameMaterialsAssoc]]]);
                    }
                }
            }

            $modelMaterials = $query->orderBy('id')
                //->offset($pagination->offset)
                //->limit($pagination->limit)
                ->asArray()
                ->with('requests','deliveries','materialType','statusMaterial')
                ->all();

            // get properties from Materials object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelMaterials));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            //$query = Materials::find()->Where(['created_by' => Yii::$app->user->getId()]);
            $query = Materials::find();

            $modelMaterials = $query->orderBy('id')
                ->asArray()
                ->with('requests','deliveries','materialType','statusMaterial')
                ->all();

            // get properties from Materials object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelMaterials));

            return Json::encode($RequestResponse);
        }
        //}
    }


    /**
     * POST Method. Materials table.
     * Insert records
     *
     * @return json
     */
    public function actionCreate()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            //return $this->goHome();
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'test', var_dump($bodyRaw) ));

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Materials object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayMaterialsAssoc = array ('id' => 'id', 'request_id' => 'request_id');
            $arraySubMaterialsAssoc = array ('delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost');

            if (array_key_exists('materials', $bodyRaw)) {
                if (!is_array($bodyRaw['materials'])) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре materials ожидается массив'));

                $subBodyRaw = $bodyRaw['materials'];

                // fill in the properties in the Materials object
                foreach ($subBodyRaw as $nameSubBodyRaw => $valueSubBodyRaw) {
                    $modelMaterials = new Materials();

                    // fill in the properties in the Materials object from $subBodyRaw
                    foreach ($arraySubMaterialsAssoc as $nameSubMaterialsAssoc => $valueSubMaterialsAssoc) {
                        if (array_key_exists($valueSubMaterialsAssoc, $valueSubBodyRaw)) {

                            if ($modelMaterials->hasAttribute($nameSubMaterialsAssoc)) {
                                $modelMaterials->$nameSubMaterialsAssoc = $valueSubBodyRaw[$valueSubMaterialsAssoc];

                                if (!$modelMaterials->validate($nameSubMaterialsAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueSubMaterialsAssoc));
                                else {

                                }
                            }
                        }
                    }

                    // fill in the properties in the Materials object from $bodyRaw
                    foreach ($arrayMaterialsAssoc as $nameMaterialsAssoc => $valueMaterialsAssoc) {
                        if (array_key_exists($valueMaterialsAssoc, $bodyRaw)) {
                            if ($modelMaterials->hasAttribute($nameMaterialsAssoc)) {
                                if ($nameMaterialsAssoc != 'id') {
                                    $modelMaterials->$nameMaterialsAssoc = $bodyRaw[$valueMaterialsAssoc];

                                    if (!$modelMaterials->validate($nameMaterialsAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueMaterialsAssoc));

                                    $modelMaterials->created_by = Yii::$app->user->getId();
                                }
                            }
                        }
                    }
                    //return Json::encode(ArrayHelper::toArray($modelMaterials));

                    // Save Materials object
                    if ($modelMaterials->validate()) {
                        $transaction = \Yii::$app->db->beginTransaction();
                        try {
                            $flagMaterials = $modelMaterials->save(false); // insert into materials table

                            if ($flagMaterials) {
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

                //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Материал успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelMaterials))));
                return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Материал успешно сохранен'));
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Отсутвтует параметр materials в запросе'));
            }
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Тело запроса не обработано'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. Materials table.
     * Update record by id parameter
     *
     * @return json
     */
    public function actionUpdate()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            //return $this->goHome();
            return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelMaterials->setAttributes($bodyRaw);

        // load attributes in Materials object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('id', $bodyRaw)) {
                // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
                $arrayMaterialsAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost');

                // Search record by id in the database
                $queryMaterials = Materials::find()
                    //->where(['AND', ['id' => $bodyRaw[$arrayMaterialsAssoc['id']]], ['created_by'=> Yii::$app->user->getId()]]);
                    ->where(['id' => $bodyRaw[$arrayMaterialsAssoc['id']]]);
                $modelMaterials = $queryMaterials->one();
                foreach ($arrayMaterialsAssoc as $nameMaterialsAssoc => $valueMaterialsAssoc) {
                    if (array_key_exists($valueMaterialsAssoc, $bodyRaw)) {
                        if ($modelMaterials->hasAttribute($nameMaterialsAssoc)) {
                            $modelMaterials->$nameMaterialsAssoc = $bodyRaw[$arrayMaterialsAssoc[$nameMaterialsAssoc]];
                            if (!$modelMaterials->validate($nameMaterialsAssoc)) return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                            $modelMaterials->created_by = Yii::$app->user->getId();
                        }
                    }
                }

                // Save Materials object
                if ($modelMaterials->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $flagMaterials = $modelMaterials->save(false); // update materials table

                        if ($flagMaterials) {
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
     * DELETE Method. Materials table.
     * Delete records by id parameter
     * or by another parameters
     *
     * @return json
     */
    public function actionDelete()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            //return $this->goHome();
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelMaterials->setAttributes($bodyRaw);

        // load attributes in Materials object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayMaterialsAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost');

            if (array_key_exists($arrayMaterialsAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayMaterialsAssoc['id']])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryMaterials = Materials::find()
                    //->where(['AND', ['id' => $bodyRaw[$arrayMaterialsAssoc['id']]], ['created_by'=> Yii::$app->user->getId()]]);
                    ->where(['id' => $bodyRaw[$arrayMaterialsAssoc['id']]]);
                $modelMaterials = $queryMaterials->one();

                if (empty($modelMaterials)) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найден Материал по id'));
                }
            } else {
                //return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id материала'));
                return $this->actionDeleteByParam();
            }

            if (!empty($modelMaterials)) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from materials table
                    $countMaterialsDelete = $modelMaterials->delete($modelMaterials->id);

                    if ($countMaterialsDelete > 0) {
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
     * DELETE Method. Materials table.
     * Delete records by another parameters
     *
     * @return json
     */
    public function actionDeleteByParam()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            //return $this->goHome();
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        //if (Yii::$app->request->isAjax) {
        //GET data from body request
        //Yii::$app->request->getBodyParams()
        $fh = fopen("php://input", 'r');
        $put_string = stream_get_contents($fh);
        $put_string = urldecode($put_string);
        //$array_put = $this->parsingRequestFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelMaterials->setAttributes($bodyRaw);

        // load attributes in Materials object
        // example: yiisoft/yii2/base/Model.php

        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayMaterialsAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost');

            // Search record by id in the database
            //$queryMaterials = Materials::find()->Where(['created_by' => Yii::$app->user->getId()]);
            $queryMaterials = Materials::find();
            //foreach (ArrayHelper::toArray($model) as $key => $value) {
            //    $query->andWhere([$key => $value]);
            //}
            $modelValidate = new Materials();
            foreach ($arrayMaterialsAssoc as $nameMaterialsAssoc => $valueMaterialsAssoc) {
                if (array_key_exists($valueMaterialsAssoc, $bodyRaw)) {
                    if ($modelValidate->hasAttribute($nameMaterialsAssoc)) {
                        $modelValidate->$nameMaterialsAssoc = $bodyRaw[$arrayMaterialsAssoc[$nameMaterialsAssoc]];
                        if (!$modelValidate->validate($nameMaterialsAssoc)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр ' . $valueMaterialsAssoc));

                        $queryMaterials->andWhere([$nameMaterialsAssoc => $bodyRaw[$arrayMaterialsAssoc[$nameMaterialsAssoc]]]);
                    }
                }

            }
            $modelsMaterials = $queryMaterials->all();

            if (!empty($modelsMaterials) && !empty($modelValidate)) {
                foreach ($modelsMaterials as $modelMaterials) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        // delete from Materials table.
                         $countMaterialsDelete = $modelMaterials->delete();

                        if ($countMaterialsDelete > 0) {
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
