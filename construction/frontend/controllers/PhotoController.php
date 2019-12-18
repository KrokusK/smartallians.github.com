<?php
namespace frontend\controllers;

use frontend\models\Photo;
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
 * API Photo controller
 */
class PhotoController extends Controller
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
     * GET Method. Photo table.
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
        $model = new Photo();
        if ($model->load(Yii::$app->request->get())) {

            // Search record by parametrs in the database
            $query = Photo::find();
            foreach (ArrayHelper::toArray($model) as $key => $value) {
                $query->andWhere([$key => $value]);
            }

            $modelPhoto = $query->orderBy('id')->all();

            // get properties from Photo object
            $PhotoResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($PhotoResponse, ArrayHelper::toArray($modelPhoto));

            return Json::encode($PhotoResponse);

        } else {
            // Search all records in the database
            $query = Photo::find();

            $modelPhoto = $query->orderBy('id')->all();

            // get properties from Photo object
            $PhotoResponse = array('method' => 'GET', 'status' => '0', 'type' => 'success');
            array_push($PhotoResponse, ArrayHelper::toArray($modelPhoto));

            return Json::encode($PhotoResponse);
        }
        //}
    }


    /**
     * POST Method. Photo table.
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
        //$array_put = $this->parsingPhotoFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelPhoto->setAttributes($bodyRaw);

        // load attributes in Photo object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('Photo[id]', $bodyRaw)) {
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Недопустимый параметр: id'));
            } else {
                $modelPhoto = new Photo();

                // fill in the properties in the Photo object
                foreach ($bodyRaw as $name => $value) {
                    $pos_begin = strpos($name, '[') + 1;
                    if (strtolower(substr($name, 0, $pos_begin - 1)) != 'photo') return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                    $pos_end = strpos($name, ']');
                    $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                    //if (isset($modelPhoto->$name)) {
                    //    $modelPhoto->$name = $value;
                    //}
                    //if (property_exists($modelPhoto, $name)) {
                    if ($modelPhoto->hasAttribute($name)) {
                        if ($name != 'id') $modelPhoto->$name = $value;
                    }
                }
            }


        }

        if ($modelPhoto->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelPhoto->save(false); // insert

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Фото не может быть сохраненоо'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Фото не может быть сохраненоо'));
            }

            //return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Фото успешно сохраненоо', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelPhoto))));
            return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Фото успешно сохраненоо'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. Photo table.
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
            //$array_put = $this->parsingPhotoFormData($put_string);

            $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
            //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

            //$modelPhoto->setAttributes($bodyRaw);

            // load attributes in Photo object
            // example: yiisoft/yii2/base/Model.php
            if (is_array($bodyRaw)) {
                if (array_key_exists('Photo[id]', $bodyRaw)) {
                    // check input parametrs
                    if (!preg_match("/^[0-9]*$/",$bodyRaw['Photo[id]'])) {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }

                    // Search record by id in the database
                    $query = Photo::find()
                        ->where(['id' => $bodyRaw['Photo[id]']]);
                    //->where(['AND', ['id' => $modelPhoto->id], ['user_desc_id'=> $var2]]);

                    $modelPhoto = $query->orderBy('id')->one();

                    if (!empty($modelPhoto)) {
                        // update in the properties in the Photo object
                        foreach ($bodyRaw as $name => $value) {
                            $pos_begin = strpos($name, '[') + 1;
                            if (strtolower(substr($name, 0, $pos_begin - 1)) != 'photo') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                            $pos_end = strpos($name, ']');
                            $name = substr($name, $pos_begin, $pos_end - $pos_begin);

                            if ($name != 'id') $modelPhoto->$name = $value;
                        }
                    } else {
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                    }
                } else {
                    $modelPhoto = new Photo();

                    // fill in the properties in the Photo object
                    foreach ($bodyRaw as $name => $value) {
                        $pos_begin = strpos($name, '[') + 1;
                        if (strtolower(substr($name, 0, $pos_begin - 1)) != 'photo') return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                        $pos_end = strpos($name, ']');
                        $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                        //if (isset($modelPhoto->$name)) {
                        //    $modelPhoto->$name = $value;
                        //}
                        //if (property_exists($modelPhoto, $name)) {
                        if ($modelPhoto->hasAttribute($name)) {
                            if ($name != 'id') $modelPhoto->$name = $value;
                        }
                    }
                }


            }

            if ($modelPhoto->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flag = $modelPhoto->save(false); // insert

                    if ($flag == true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Фото не может быть сохранено (обновлено)'));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Фото не может быть сохранено (обновлено)'));
                }

                //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Фото успешно сохранено (обновлено)', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelPhoto))));
                return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Фото успешно сохранено (обновлено)'));
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        //}
    }


    /**
     * DELETE Method. Photo table.
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
        //$array_put = $this->parsingPhotoFormData($put_string);

        $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        //$body = json_decode(Yii::$app->getRequest()->getBodyParams(), true);

        //$modelPhoto->setAttributes($bodyRaw);

        // load attributes in Photo object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('Photo[id]', $bodyRaw)) {
                // check input parametrs
                if (!preg_match("/^[0-9]*$/",$bodyRaw['Photo[id]'])) {
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $query = Photo::find()
                    ->where(['id' => $bodyRaw['Photo[id]']]);
                //->where(['AND', ['id' => $modelPhoto->id], ['user_desc_id'=> $var2]]);

                $modelPhoto = $query->orderBy('id')->one();
            }
        }

        if (!empty($modelPhoto)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelPhoto->delete($bodyRaw['Photo[id]']); // delete

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удален'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удален'));
            }

            //return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Фото успешно удален', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelPhoto))));
            return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Фото успешно удален'));
        } else {
            return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка: Фото не может быть удален'));
        }
        //}
    }


    /**
     * Parsing request Method.
     *
     * @return array
     */
    public function parsingPhotoFormData($put_string)
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
