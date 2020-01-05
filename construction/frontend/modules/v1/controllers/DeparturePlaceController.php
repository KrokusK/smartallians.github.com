<?php
namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\DeparturePlace;
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
 * API DeparturePlace controller
 */
class DeparturePlaceController extends Controller
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
     * GET Method. DeparturePlace table.
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
        $model = new DeparturePlace();
        if ($model->load(Yii::$app->request->get())) {

            // Search record by parametrs in the database
            $query = DeparturePlace::find();
            foreach (ArrayHelper::toArray($model) as $key => $value) {
                $query->andWhere([$key => $value]);
            }

            $modelDeparturePlace = $query->orderBy('name')->all();

            // get properties from DeparturePlace object
            $DeparturePlaceResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($DeparturePlaceResponse, ArrayHelper::toArray($modelDeparturePlace));

            return Json::encode($DeparturePlaceResponse);

        } else {
            // Search all records in the database
            $query = DeparturePlace::find();

            $modelDeparturePlace = $query->orderBy('name')->all();

            // get properties from DeparturePlace object
            $DeparturePlaceResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($DeparturePlaceResponse, ArrayHelper::toArray($modelDeparturePlace));

            return Json::encode($DeparturePlaceResponse);
        }
        //}
    }


    /**
     * POST Method. DeparturePlace table.
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
        //$array_put = $this->parsingDeparturePlaceFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelDeparturePlace->setAttributes($bodyRaw);

        // load attributes in DeparturePlace object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('DeparturePlace[id]', $bodyRaw)) {
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Недопустимый параметр: id'));
            } else {
                $modelDeparturePlace = new DeparturePlace();

                // fill in the properties in the DeparturePlace object
                foreach ($bodyRaw as $name => $value) {
                    $pos_begin = strpos($name, '[') + 1;
                    if (strtolower(substr($name, 0, $pos_begin - 1)) != 'departureplace') return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                    $pos_end = strpos($name, ']');
                    $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                    //if (isset($modelDeparturePlace->$name)) {
                    //    $modelDeparturePlace->$name = $value;
                    //}
                    //if (property_exists($modelDeparturePlace, $name)) {
                    if ($modelDeparturePlace->hasAttribute($name)) {
                        if ($name != 'id') $modelDeparturePlace->$name = $value;
                    }
                }
            }


        }

        if ($modelDeparturePlace->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelDeparturePlace->save(false); // insert

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть сохранено'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть сохранено'));
            }

            //return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Место отправления успешно сохранено', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDeparturePlace))));
            return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Место отправления успешно сохранено'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. DeparturePlace table.
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
            //$array_put = $this->parsingDeparturePlaceFormData($put_string);

            $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
            //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

            //$modelDeparturePlace->setAttributes($bodyRaw);

            // load attributes in DeparturePlace object
            // example: yiisoft/yii2/base/Model.php
            if (is_array($bodyRaw)) {
                if (array_key_exists('DeparturePlace[id]', $bodyRaw)) {
                    // check input parametrs
                    if (!preg_match("/^[0-9]*$/",$bodyRaw['DeparturePlace[id]'])) {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }

                    // Search record by id in the database
                    $query = DeparturePlace::find()
                        ->where(['id' => $bodyRaw['DeparturePlace[id]']]);
                    //->where(['AND', ['id' => $modelDeparturePlace->id], ['user_desc_id'=> $var2]]);

                    $modelDeparturePlace = $query->orderBy('name')->one();

                    if (!empty($modelDeparturePlace)) {
                        // update in the properties in the DeparturePlace object
                        foreach ($bodyRaw as $name => $value) {
                            $pos_begin = strpos($name, '[') + 1;
                            if (strtolower(substr($name, 0, $pos_begin - 1)) != 'departureplace') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                            $pos_end = strpos($name, ']');
                            $name = substr($name, $pos_begin, $pos_end - $pos_begin);

                            if ($name != 'id') $modelDeparturePlace->$name = $value;
                        }
                    } else {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }
                } else {
                    $modelDeparturePlace = new DeparturePlace();

                    // fill in the properties in the DeparturePlace object
                    foreach ($bodyRaw as $name => $value) {
                        $pos_begin = strpos($name, '[') + 1;
                        if (strtolower(substr($name, 0, $pos_begin - 1)) != 'departureplace') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                        $pos_end = strpos($name, ']');
                        $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                        //if (isset($modelDeparturePlace->$name)) {
                        //    $modelDeparturePlace->$name = $value;
                        //}
                        //if (property_exists($modelDeparturePlace, $name)) {
                        if ($modelDeparturePlace->hasAttribute($name)) {
                            if ($name != 'id') $modelDeparturePlace->$name = $value;
                        }
                    }
                }


            }

            if ($modelDeparturePlace->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flag = $modelDeparturePlace->save(false); // insert

                    if ($flag == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть сохранено (обновлено)'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть сохранено (обновлено)'));
                }

                //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Место отправления успешно сохранено (обновлено)', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDeparturePlace))));
                return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Место отправления успешно сохранено (обновлено)'));
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        //}
    }


    /**
     * DELETE Method. DeparturePlace table.
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
        //$array_put = $this->parsingDeparturePlaceFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelDeparturePlace->setAttributes($bodyRaw);

        // load attributes in DeparturePlace object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('DeparturePlace[id]', $bodyRaw)) {
                // check input parametrs
                if (!preg_match("/^[0-9]*$/",$bodyRaw['DeparturePlace[id]'])) {
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $query = DeparturePlace::find()
                    ->where(['id' => $bodyRaw['DeparturePlace[id]']]);
                //->where(['AND', ['id' => $modelDeparturePlace->id], ['user_desc_id'=> $var2]]);

                $modelDeparturePlace = $query->orderBy('name')->one();
            }
        }

        if (!empty($modelDeparturePlace)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelDeparturePlace->delete($bodyRaw['DeparturePlace[id]']); // delete

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
            }

            //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Место отправления успешно удалено', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelDeparturePlace))));
            return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Место отправления успешно удалено'));
        } else {
            return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Место отправления не может быть удалено'));
        }
        //}
    }


    /**
     * Parsing request Method.
     *
     * @return array
     */
    public function parsingDeparturePlaceFormData($put_string)
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
