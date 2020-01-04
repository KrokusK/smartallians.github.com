<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\City;
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
 * API City controller
 */
class CityController extends Controller
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
     * GET Method. City table.
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
        $model = new City();
        if ($model->load(Yii::$app->request->get())) {

            // Search record by parametrs in the database
            $query = City::find();
            foreach (ArrayHelper::toArray($model) as $key => $value) {
                $query->andWhere([$key => $value]);
            }

            $modelCity = $query->orderBy('name')->all();

            // get properties from City object
            $CityResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($CityResponse, ArrayHelper::toArray($modelCity));

            return Json::encode($CityResponse);

        } else {
            // Search all records in the database
            $query = City::find();

            $modelCity = $query->orderBy('name')->all();

            // get properties from City object
            $CityResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($CityResponse, ArrayHelper::toArray($modelCity));

            return Json::encode($CityResponse);
        }
        //}
    }


    /**
     * POST Method. City table.
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
        //$array_put = $this->parsingCityFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelCity->setAttributes($bodyRaw);

        // load attributes in City object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('City[id]', $bodyRaw)) {
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Недопустимый параметр: id'));
            } else {
                $modelCity = new City();

                // fill in the properties in the City object
                foreach ($bodyRaw as $name => $value) {
                    $pos_begin = strpos($name, '[') + 1;
                    if (strtolower(substr($name, 0, $pos_begin - 1)) != 'city') return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                    $pos_end = strpos($name, ']');
                    $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                    //if (isset($modelCity->$name)) {
                    //    $modelCity->$name = $value;
                    //}
                    //if (property_exists($modelCity, $name)) {
                    if ($modelCity->hasAttribute($name)) {
                        if ($name != 'id') $modelCity->$name = $value;
                    }
                }
            }


        }

        if ($modelCity->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelCity->save(false); // insert

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Город не может быть сохранен'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Город не может быть сохранен'));
            }

            //return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Город успешно сохранен', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelCity))));
            return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Город успешно сохранен'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. City table.
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
            //$array_put = $this->parsingCityFormData($put_string);

            $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
            //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

            //$modelCity->setAttributes($bodyRaw);

            // load attributes in City object
            // example: yiisoft/yii2/base/Model.php
            if (is_array($bodyRaw)) {
                if (array_key_exists('City[id]', $bodyRaw)) {
                    // check input parametrs
                    if (!preg_match("/^[0-9]*$/",$bodyRaw['City[id]'])) {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }

                    // Search record by id in the database
                    $query = City::find()
                        ->where(['id' => $bodyRaw['City[id]']]);
                    //->where(['AND', ['id' => $modelCity->id], ['user_desc_id'=> $var2]]);

                    $modelCity = $query->orderBy('name')->one();

                    if (!empty($modelCity)) {
                        // update in the properties in the City object
                        foreach ($bodyRaw as $name => $value) {
                            $pos_begin = strpos($name, '[') + 1;
                            if (strtolower(substr($name, 0, $pos_begin - 1)) != 'city') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                            $pos_end = strpos($name, ']');
                            $name = substr($name, $pos_begin, $pos_end - $pos_begin);

                            if ($name != 'id') $modelCity->$name = $value;
                        }
                    } else {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }
                } else {
                    $modelCity = new City();

                    // fill in the properties in the City object
                    foreach ($bodyRaw as $name => $value) {
                        $pos_begin = strpos($name, '[') + 1;
                        if (strtolower(substr($name, 0, $pos_begin - 1)) != 'city') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                        $pos_end = strpos($name, ']');
                        $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                        //if (isset($modelCity->$name)) {
                        //    $modelCity->$name = $value;
                        //}
                        //if (property_exists($modelCity, $name)) {
                        if ($modelCity->hasAttribute($name)) {
                            if ($name != 'id') $modelCity->$name = $value;
                        }
                    }
                }


            }

            if ($modelCity->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flag = $modelCity->save(false); // insert

                    if ($flag == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Город не может быть сохранен (обновлен)'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Город не может быть сохранен (обновлен)'));
                }

                //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Город успешно сохранен (обновлен)', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelCity))));
                return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Город успешно сохранен (обновлен)'));
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        //}
    }


    /**
     * DELETE Method. City table.
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
        //$array_put = $this->parsingCityFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelCity->setAttributes($bodyRaw);

        // load attributes in City object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('City[id]', $bodyRaw)) {
                // check input parametrs
                if (!preg_match("/^[0-9]*$/",$bodyRaw['City[id]'])) {
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $query = City::find()
                    ->where(['id' => $bodyRaw['City[id]']]);
                //->where(['AND', ['id' => $modelCity->id], ['user_desc_id'=> $var2]]);

                $modelCity = $query->orderBy('name')->one();
            }
        }

        if (!empty($modelCity)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelCity->delete($bodyRaw['City[id]']); // delete

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Город не может быть удален'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Город не может быть удален'));
            }

            //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Город успешно удален', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelCity))));
            return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Город успешно удален'));
        } else {
            return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Город не может быть удален'));
        }
        //}
    }


    /**
     * Parsing request Method.
     *
     * @return array
     */
    public function parsingCityFormData($put_string)
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
