<?php
namespace frontend\controllers;

use frontend\models\Materials;
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
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
            return $this->goHome();
        }

        //if (Yii::$app->request->isAjax) {

        $bodyRaw = Yii::$app->getRequest()->get();

        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayMaterialsAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost');

            // Search record by id in the database
            $query = Materials::find()->Where(['created_by' => Yii::$app->user->getId()]);
            //foreach (ArrayHelper::toArray($model) as $key => $value) {
            //    $query->andWhere([$key => $value]);
            //}
            foreach ($arrayMaterialsAssoc as $nameMaterialsAssoc => $valueMaterialsAssoc) {
                if (array_key_exists($valueMaterialsAssoc, $bodyRaw)) {
                    $query->andWhere([$nameMaterialsAssoc => $bodyRaw[$arrayMaterialsAssoc[$nameMaterialsAssoc]]]);
                }
            }

            $modelMaterials = $query->orderBy('created_at')
                //->offset($pagination->offset)
                //->limit($pagination->limit)
                ->with('kindJob')
                ->asArray()
                ->all();

            // get properties from Materials object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelMaterials));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            $query = Materials::find()->Where(['created_by' => Yii::$app->user->getId()]);

            $modelMaterials = $query->orderBy('created_at')
                ->with('kindJob')
                ->asArray()
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
     * Insert records by parameters
     *
     * @return json
     */
    public function actionCreate()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
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

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Materials object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayMaterialsAssoc = array ('id' => 'id', 'request_id' => 'request_id', 'delivery_id' => 'delivery_id', 'material_type_id' => 'material_type_id', 'status_material_id' => 'status_material_id', 'name' => 'name', 'count' => 'count', 'cost' => 'cost');

            $modelMaterials = new Materials();

            // fill in the properties in the Materials object
            foreach ($arrayMaterialsAssoc as $nameMaterialsAssoc => $valueMaterialsAssoc) {
                if (array_key_exists($valueMaterialsAssoc, $bodyRaw)) {
                    if ($modelMaterials->hasAttribute($nameMaterialsAssoc)) {
                        if ($nameMaterialsAssoc != 'id' && $nameMaterialsAssoc != 'created_at' && $nameMaterialsAssoc != 'updated_at') {
                            $modelMaterials->$nameMaterialsAssoc = $bodyRaw[$valueMaterialsAssoc];

                            if (!$modelMaterials->validate($nameMaterialsAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueMaterialsAssoc));

                            $modelMaterials->created_by = Yii::$app->user->getId();
                            $modelMaterials->created_at = time();
                            $modelMaterials->updated_at = time();
                        }
                    }
                }
            }

            // check parametr for the KindJob object
            foreach ($arrayKindJobAssoc as $nameKindJobAssoc => $valueKindJobAssoc) {
                if (array_key_exists($valueKindJobAssoc, $bodyRaw)) {
                    if ($nameKindJobAssoc == 'kind_job_id' && !is_array($bodyRaw[$valueKindJobAssoc])) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре work_type ожидается массив'));
                }
            }

        }


        if ($modelMaterials->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagMaterials = $modelMaterials->save(false); // insert into materials table

                if ($flagMaterials) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
            }

            //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelMaterials))));
            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. Materials table.
     * Update records by parameters
     *
     * @return json
     */
    public function actionUpdate()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
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
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryMaterials = Materials::find()
                    ->where(['AND', ['id' => $bodyRaw[$arrayMaterialsAssoc['id']]], ['created_by'=> Yii::$app->user->getId()]]);
                $modelMaterials = $queryMaterials->orderBy('created_at')->one();

                if (!empty($modelMaterials)) {
                    // fill in the properties in the Materials object
                    foreach ($arrayMaterialsAssoc as $nameMaterialsAssoc => $valueMaterialsAssoc) {
                        if (array_key_exists($valueMaterialsAssoc, $bodyRaw)) {
                            if ($modelMaterials->hasAttribute($nameMaterialsAssoc)) {
                                if ($nameMaterialsAssoc != 'id' && $nameMaterialsAssoc != 'created_at' && $nameMaterialsAssoc != 'updated_at') {
                                    $modelMaterials->$nameMaterialsAssoc = $bodyRaw[$valueMaterialsAssoc];

                                    if (!$modelMaterials->validate($nameMaterialsAssoc)) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueMaterialsAssoc));

                                    $modelMaterials->created_by = Yii::$app->user->getId();
                                    $modelMaterials->updated_at = time();
                                }
                            }
                        }
                    }

                    // check parametr for the KindJob object
                    foreach ($arrayKindJobAssoc as $nameKindJobAssoc => $valueKindJobAssoc) {
                        if (array_key_exists($valueKindJobAssoc, $bodyRaw)) {
                            if ($nameKindJobAssoc == 'kind_job_id' && !is_array($bodyRaw[$valueKindJobAssoc])) return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В параметре work_type ожидается массив'));
                        }
                    }
                } else {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Завка по id'));
                }
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id заявки'));
            }

        }


        if ($modelMaterials->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagMaterials = $modelMaterials->save(false); // insert into materials table

                if ($flagMaterials) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена (обновлена)'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена (обновлена)'));
            }

            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена (обновлена)'));
        }

    }


    /**
     * DELETE Method. Materials table.
     * Delete records by parameters
     *
     * @return json
     */
    public function actionDelete()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            //return $this->goHome();
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
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryMaterials = Materials::find()
                    ->where(['AND', ['id' => $bodyRaw[$arrayMaterialsAssoc['id']]], ['created_by'=> Yii::$app->user->getId()]]);
                $modelMaterials = $queryMaterials->orderBy('created_at')->one();

                if (empty($modelMaterials)) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: В БД не найдена Завка по id'));
                }
            } else {
                return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Отсутствет id заявки'));
            }
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
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
            }

            return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно удалена'));
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
        }
        //}
    }

}
