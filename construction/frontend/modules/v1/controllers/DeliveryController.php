<?php
namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Delivery;
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
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['view', 'create', 'update', 'delete'],
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
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            //return $this->goHome();
        }

        //if (Yii::$app->request->isAjax) {
        //GET data from GET request
        $model = new Delivery();
        if ($model->load(Yii::$app->request->get())) {

            // Search record by parametrs in the database
            $query = Delivery::find();
            foreach (ArrayHelper::toArray($model) as $key => $value) {
                $query->andWhere([$key => $value]);
            }

            $modelDelivery = $query->OrderBy('id')->all();

            // get properties from Delivery object
            $DeliveryResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($DeliveryResponse, ArrayHelper::toArray($modelDelivery));

            return Json::encode($DeliveryResponse);

        } else {
            // Search all records in the database
            $query = Delivery::find();

            $modelDelivery = $query->OrderBy('id')->all();

            // get properties from Delivery object
            $DeliveryResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($DeliveryResponse, ArrayHelper::toArray($modelDelivery));

            return Json::encode($DeliveryResponse);
        }
        //}
    }


    /**
     * POST Method. Delivery table.
     * Insert records by parameters
     *
     * @return json
     */
    public function actionCreate()
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
        //$array_put = $this->parsingDeliveryFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelDelivery->setAttributes($bodyRaw);

        // load attributes in Delivery object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('Delivery[id]', $bodyRaw)) {
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Недопустимый параметр: id'));
            } else {
                $modelDelivery = new Delivery();

                // fill in the properties in the Delivery object
                foreach ($bodyRaw as $name => $value) {
                    $pos_begin = strpos($name, '[') + 1;
                    if (strtolower(substr($name, 0, $pos_begin - 1)) != 'delivery') return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                    $pos_end = strpos($name, ']');
                    $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                    //if (isset($modelDelivery->$name)) {
                    //    $modelDelivery->$name = $value;
                    //}
                    //if (property_exists($modelDelivery, $name)) {
                    if ($modelDelivery->hasAttribute($name)) {
                        if ($name != 'id' && $name != 'created_at' && $name != 'updated_at') $modelDelivery->$name = $value;

                        $modelDelivery->created_at = time();
                        $modelDelivery->updated_at = time();
                    }
                }
            }


        }

        if ($modelDelivery->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelDelivery->save(false); // insert

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть сохранена'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть сохранена'));
            }

            //return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Поставка успешно сохранена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDelivery))));
            return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Поставка успешно сохранена'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. Delivery table.
     * Update records by parameters
     *
     * @return json
     */
    public function actionUpdate()
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
            //$array_put = $this->parsingDeliveryFormData($put_string);

            $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
            //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

            //$modelDelivery->setAttributes($bodyRaw);

            // load attributes in Delivery object
            // example: yiisoft/yii2/base/Model.php
            if (is_array($bodyRaw)) {
                if (array_key_exists('Delivery[id]', $bodyRaw)) {
                    // check input parametrs
                    if (!preg_match("/^[0-9]*$/",$bodyRaw['Delivery[id]'])) {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }

                    // Search record by id in the database
                    $query = Delivery::find()
                        ->where(['id' => $bodyRaw['Delivery[id]']]);
                    //->where(['AND', ['id' => $modelDelivery->id], ['user_desc_id'=> $var2]]);

                    $modelDelivery = $query->OrderBy('id')->one();

                    if (!empty($modelDelivery)) {
                        // update in the properties in the Delivery object
                        foreach ($bodyRaw as $name => $value) {
                            $pos_begin = strpos($name, '[') + 1;
                            if (strtolower(substr($name, 0, $pos_begin - 1)) != 'delivery') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                            $pos_end = strpos($name, ']');
                            $name = substr($name, $pos_begin, $pos_end - $pos_begin);

                            if ($name != 'id' && $name != 'created_at' && $name != 'updated_at') $modelDelivery->$name = $value;

                            $modelDelivery->updated_at = time();
                        }
                    } else {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }
                } else {
                    $modelDelivery = new Delivery();

                    // fill in the properties in the Delivery object
                    foreach ($bodyRaw as $name => $value) {
                        $pos_begin = strpos($name, '[') + 1;
                        if (strtolower(substr($name, 0, $pos_begin - 1)) != 'delivery') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                        $pos_end = strpos($name, ']');
                        $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                        //if (isset($modelDelivery->$name)) {
                        //    $modelDelivery->$name = $value;
                        //}
                        //if (property_exists($modelDelivery, $name)) {
                        if ($modelDelivery->hasAttribute($name)) {
                            if ($name != 'id' && $name != 'created_at' && $name != 'updated_at') $modelDelivery->$name = $value;

                            $modelDelivery->created_at = time();
                            $modelDelivery->updated_at = time();
                        }
                    }
                }


            }

            if ($modelDelivery->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flag = $modelDelivery->save(false); // insert

                    if ($flag == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть сохранена (обновлена)'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть сохранена (обновлена)'));
                }

                //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Поставка успешно сохранена (обновлена)', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDelivery))));
                return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Поставка успешно сохранена (обновлена)'));
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        //}
    }


    /**
     * DELETE Method. Delivery table.
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
        //$array_put = $this->parsingDeliveryFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelDelivery->setAttributes($bodyRaw);

        // load attributes in Delivery object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('Delivery[id]', $bodyRaw)) {
                // check input parametrs
                if (!preg_match("/^[0-9]*$/",$bodyRaw['Delivery[id]'])) {
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $query = Delivery::find()
                    ->where(['id' => $bodyRaw['Delivery[id]']]);
                //->where(['AND', ['id' => $modelDelivery->id], ['user_desc_id'=> $var2]]);

                $modelDelivery = $query->OrderBy('id')->one();
            }
        }

        if (!empty($modelDelivery)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelDelivery->delete($bodyRaw['Delivery[id]']); // delete

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
            }

            //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Поставка успешно удалена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDelivery))));
            return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Поставка успешно удалена'));
        } else {
            return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Поставка не может быть удалена'));
        }
        //}
    }


    /**
     * Parsing request Method.
     *
     * @return array
     */
    public function parsingDeliveryFormData($put_string)
    {
        //            //$put_string = json_decode($put_string_json, TRUE);
        //            //$put_string=Yii::$app->request->getBodyParams();
        //
        //            //$temp = json_decode($put_string, TRUE);
        //
        //            //$put_param = explode("&", $put_string);
        //            //$array_put=array();
        //            //parse_str($put_string, $array_put);
        //
        //
        //
        //            //foreach($array_put as $put_val)
        //            //{
        //            //    $param = explode("=", $put_val);
        //            //    $array_put[$paam[0]]=urldecode($param[1]);
        //}

        //$request = Yii::$app->request;

        // returns all parameters
        //$params = $request->getBodyParams();

        //name=\"Request[name]\"\r\n\r\ntest\r\n-----------------------------4833311154639"

        // returns the parameter "id"
        //$param = $request->getBodyParam('nad');

        function strpos_recursive($haystack, $needle, $offset = 0, &$results = array()) {
            $offset = strpos($haystack, $needle, $offset);
            if($offset === false) {
                return $results;
            } else {
                $results[] = $offset;
                return strpos_recursive($haystack, $needle, ($offset + 1), $results);
            }
        }

        $string = $put_string;
        $search = 'name="';
        $found = strpos_recursive($string, $search);

        if($found) {
            foreach($found as $pos) {
                //$temp = 'Found "'.$search.'" in string "'.$string.'" at position '.$pos;

                $key = substr($string, ($pos + strlen($search)), (strpos($string, '"', ($pos + strlen($search))) - ($pos + strlen($search))));

                $pos_begin = strpos($string, '"', ($pos + strlen($search))) + 4;
                $pos_end = strpos($string, '-', $pos_begin) - 2;
                $value = substr($string, $pos_begin + 1, $pos_end - ($pos_begin + 1));

                $array_put[$key] = $value;
            }
        } else {
            //$temp = '"'.$search.'" not found in "'.$string.'"';
        }

        return $array_put;
    }
}
