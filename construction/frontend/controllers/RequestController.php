<?php
namespace frontend\controllers;

use frontend\models\Request;
use frontend\models\RequestKindJob;
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
 * API Request controller
 */
class RequestController extends Controller
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

    public function actionTest()
    {
        $modelRequest = new Request();
        return $this->render('request', [
            'modelRequest' => $modelRequest,
        ]);
    }


    /**
     * GET Method. Request table.
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
        //GET data from GET request
        $model = new Request();
        if ($model->load(Yii::$app->request->get())) {

            // Search record by parametrs in the database
            //$sqlParametrs = array(['AND']);
            //foreach (ArrayHelper::toArray($model) as $key => $value) {
            //    array_push($sqlParametrs, [$key => $value]);
            //}
            $query = Request::find()->Where(['created_by' => Yii::$app->user->getId()]);
            foreach (ArrayHelper::toArray($model) as $key => $value) {
                $query->andWhere([$key => $value]);
            }

            $modelRequest = $query->orderBy('created_at')
                //->offset($pagination->offset)
                //->limit($pagination->limit)
                ->with('kindJob')
                ->asArray()
                ->all();

            // get properties from Request object and from links
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelRequest));
            //array_push($RequestResponse, var_dump($modelRequest));

            return Json::encode($RequestResponse);

        } else {
            // Search all records in the database
            $query = Request::find()->Where(['created_by' => Yii::$app->user->getId()]);

            $modelRequest = $query->orderBy('created_at')
                ->with('kindJob')
                ->asArray()
                ->all();

            // get properties from Request object
            $RequestResponse = array('method' => 'GET', 'status' => 0, 'type' => 'success');
            array_push($RequestResponse, ArrayHelper::toArray($modelRequest));

            return Json::encode($RequestResponse);
        }
        //}
    }


    /**
     * POST Method. Request table.
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

        // load attributes in Request object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            $arrayKindJobAssoc = array ('kind_job_id' => 'work_type');

            $modelRequest = new Request();

            // fill in the properties in the Request object
            foreach ($arrayRequestAssoc as $nameRequestAssoc => $valueRequestAssoc) {
                if (array_key_exists($valueRequestAssoc, $bodyRaw)) {
                    if ($modelRequest->hasAttribute($nameRequestAssoc)) {
                        if ($nameRequestAssoc != 'id' && $nameRequestAssoc != 'created_at' && $nameRequestAssoc != 'updated_at') {
                            $modelRequest->$nameRequestAssoc = $bodyRaw[$valueRequestAssoc];

                            $modelRequest->created_by = Yii::$app->user->getId();
                            $modelRequest->created_at = time();
                            $modelRequest->updated_at = time();
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


        if ($modelRequest->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagRequest = $modelRequest->save(false); // insert into request table

                $flagRequestKindJob = true;
                if ($flagRequest) {

                    // Save records into request_kind_job table
                    if (array_key_exists($arrayKindJobAssoc['kind_job_id'], $bodyRaw)) {
                        foreach ($bodyRaw[$arrayKindJobAssoc['kind_job_id']] as $name => $value) {
                            $modelRequestKindJob = new RequestKindJob();

                            // fill in the properties in the KindJob object
                            if ($modelRequestKindJob->hasAttribute('kind_job_id')) {
                                $modelRequestKindJob->kind_job_id = $value;
                            }

                            if ($modelRequestKindJob->validate('kind_job_id')) {
                                $modelRequestKindJob->request_id = $modelRequest->id;

                                if (!$modelRequestKindJob->save(false)) $flagRequestKindJob = false; // insert into request_kind_job table
                            }
                        }
                    }
                }

                if ($flagRequest == true && $flagRequestKindJob == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть сохранена'));
            }

            //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelRequest))));
            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно сохранена'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации'));
        }
        //}
    }


    /**
     * PUT, PATCH Method. Request table.
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

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Request object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            // Because the field names may match within a single query, the parameter names may not match the table field names. To solve this problem let's create an associative arrays
            $arrayRequestAssoc = array ('id' => 'id', 'status_request_id' => 'status_request_id', 'city_id' => 'city_id', 'address' => 'address', 'name' => 'name', 'description' => 'description', 'task' => 'task', 'budjet' => 'budjet', 'period' => 'period', 'date_begin' => 'date_begin', 'date_end' => 'date_end');
            $arrayKindJobAssoc = array ('kind_job_id' => 'work_type');

            if (array_key_exists($arrayRequestAssoc['id'], $bodyRaw)) {
                // check id parametr
                if (!preg_match("/^[0-9]*$/",$bodyRaw[$arrayRequestAssoc['id']])) {
                    return Json::encode(array('method' => 'PUT, PATCH', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $queryRequest = Request::find()
                    ->where(['AND', ['id' => $bodyRaw[$arrayRequestAssoc['id']]], ['created_by'=> Yii::$app->user->getId()]]);
                $modelRequest = $queryRequest->orderBy('created_at')->one();

                // Search record by id in the database
                //$queryRequestKindJob = RequestKindJob::find()
                //    ->where(['request_id' =>  $modelRequest->id]);
                //$modelRequestKindJob = $queryRequestKindJob->asArray()->all();

                if (!empty($modelRequest)) {
                    // fill in the properties in the Request object
                    foreach ($arrayRequestAssoc as $nameRequestAssoc => $valueRequestAssoc) {
                        if (array_key_exists($valueRequestAssoc, $bodyRaw)) {
                            if ($modelRequest->hasAttribute($nameRequestAssoc)) {
                                if ($nameRequestAssoc != 'id' && $nameRequestAssoc != 'created_at' && $nameRequestAssoc != 'updated_at') {
                                    $modelRequest->$nameRequestAssoc = $bodyRaw[$valueRequestAssoc];

                                    $modelRequest->created_by = Yii::$app->user->getId();
                                    $modelRequest->updated_at = time();
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


        if ($modelRequest->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagRequest = $modelRequest->save(false); // insert into request table

                $flagRequestKindJob = true;
                if ($flagRequest) {

                    // Save records into request_kind_job table
                    if (array_key_exists($arrayKindJobAssoc['kind_job_id'], $bodyRaw)) {
                        // delete old records from request_kind_job table
                        $modelRequestKindJob = new RequestKindJob();
                        $modelRequestKindJob->delete(['request_id' => $modelRequest->id]);

                        foreach ($bodyRaw[$arrayKindJobAssoc['kind_job_id']] as $name => $value) {
                            $modelRequestKindJob = new RequestKindJob();

                            // fill in the properties in the KindJob object
                            if ($modelRequestKindJob->hasAttribute('kind_job_id')) {
                                $modelRequestKindJob->kind_job_id = $value;
                            }

                            if ($modelRequestKindJob->validate('kind_job_id')) {
                                $modelRequestKindJob->request_id = $modelRequest->id;

                                if (!$modelRequestKindJob->save(false)) $flagRequestKindJob = false; // insert into request_kind_job table
                            }
                        }
                    }
                }

                if ($flagRequest == true && $flagRequestKindJob == true) {
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
     * DELETE Method. Request table.
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

        //$modelRequest->setAttributes($bodyRaw);

        // load attributes in Request object
        // example: yiisoft/yii2/base/Model.php
        if (is_array($bodyRaw)) {
            if (array_key_exists('Request[id]', $bodyRaw)) {
                // check input parametrs
                if (!preg_match("/^[0-9]*$/",$bodyRaw['Request[id]'])) {
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: id'));
                }

                // Search record by id in the database
                $query = Request::find()
                    ->where(['id' => $bodyRaw['Request[id]']]);
                //->where(['AND', ['id' => $modelRequest->id], ['user_desc_id'=> $var2]]);

                $modelRequest = $query->orderBy('created_at')
                    //->offset($pagination->offset)
                    //->limit($pagination->limit)
                    //->leftJoin('photo_ad', '"user_ad"."id" = "photo_ad"."ad_id"')
                    //->with('adPhotos')
                    ->one();
            }
        }

        if (!empty($modelRequest)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flag = $modelRequest->delete($bodyRaw['Request[id]']); // delete

                if ($flag == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
            }

            //return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно удалена', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelRequest))));
            return Json::encode(array('method' => 'DELETE', 'status' => 0, 'type' => 'success', 'message' => 'Заявка успешно удалена'));
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Заявка не может быть удалена'));
        }
        //}
    }


    /**
     * Parsing request Method.
     *
     * @return array
     */
    public function parsingRequestFormData($put_string)
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
