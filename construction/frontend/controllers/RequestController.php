<?php
namespace frontend\controllers;

//use frontend\models\AdCategory;
//use frontend\models\PhotoAd;
use frontend\models\Request;
use frontend\models\StatusRequest;
//use frontend\models\ResendVerificationEmailForm;
//use frontend\models\UserAd;
//use frontend\models\UserDesc;
//use frontend\models\VerifyEmailForm;
//use frontend\models\UserAd;
//use frontend\models\UserCity;
//use frontend\models\UserDesc;
//use frontend\models\UserAd;
//use frontend\models\UserCity;
//use frontend\models\UserDesc;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
//use common\models\LoginForm;
//use frontend\models\PasswordResetRequestForm;
//use frontend\models\ResetPasswordForm;
//use frontend\models\SignupForm;
//use frontend\models\ContactForm;

/**
 * Site controller
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
            //return $this->goHome();
        }

        $modelRequest = new Request();
        if (Yii::$app->request->isAjax && $modelRequest->load(Yii::$app->request->get())) {

            // check input parametrs
            //$cit = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cit'))) ? Yii::$app->request->get('cit') : null;
            //$cat = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cat'))) ? Yii::$app->request->get('cat') : null;
            //$ser = (preg_match("/^[a-zA-Z0-9]*$/",Yii::$app->request->get('ser'))) ? Yii::$app->request->get('ser') : null;

            // select user ads by */*/* parametrs
            if (false) {
                // something
            } else {
                $query = Request::find();
                //$query = Request::find()
                //    ->where(['AND', ['city_id' => $var1], ['user_desc_id'=> $var2]]);

                $requestList = $query->orderBy('created_at')
                    //->offset($pagination->offset)
                    //->limit($pagination->limit)
                    //->leftJoin('photo_ad', '"user_ad"."id" = "photo_ad"."ad_id"')
                    //->with('adPhotos')
                    ->all();
            }

            return Json::encode(array('method' => 'GET', 'status' => '1', 'type' => 'success', 'message' => 'Успешно'));
        } else {
            return Json::encode(array('method' => 'GET', 'status' => '0', 'type' => 'error', 'message' => 'Ошибка'));
        }

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
            //return $this->goHome();
        }

        $modelRequest = new Request();
        if (Yii::$app->request->isAjax && $modelRequest->load(Yii::$app->request->post())) {

            // check input parametrs
            //$cit = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cit'))) ? Yii::$app->request->get('cit') : null;
            //$cat = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cat'))) ? Yii::$app->request->get('cat') : null;
            //$ser = (preg_match("/^[a-zA-Z0-9]*$/",Yii::$app->request->get('ser'))) ? Yii::$app->request->get('ser') : null;

            // select user ads by */*/* parametrs
            if (false) {
                // something
            } else {
                $query = Request::find();
                //$query = Request::find()
                //    ->where(['AND', ['city_id' => $var1], ['user_desc_id'=> $var2]]);

                $requestList = $query->orderBy('created_at')
                    //->offset($pagination->offset)
                    //->limit($pagination->limit)
                    //->leftJoin('photo_ad', '"user_ad"."id" = "photo_ad"."ad_id"')
                    //->with('adPhotos')
                    ->all();
            }



            return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'success', 'message' => 'Успешно'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'error', 'message' => 'Ошибка'));
        }

    }

/*    public function actionCreateAd()
    {
        // Is user a guest?
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // if user profile is empty go to Homepage
        $modelUserDesc = UserDesc::find()->where(['user_id' => Yii::$app->user->getId()])->one();
        if (empty($modelUserDesc)) {
            return $this->goHome();
        }

        // create new models for ad and photos
        $modelUserAd = new UserAd();
        $modelPhotoAd = new PhotoAd();

        // get attbutes for Ad and Photos objects from Post request
        if (Yii::$app->request->isAjax && $modelUserAd->load(Yii::$app->request->post()) && $modelPhotoAd->load(Yii::$app->request->post())) {
            $modelPhotoAd->imageFiles = UploadedFile::getInstances($modelPhotoAd, 'imageFiles');
            if ($modelPhotoAd->upload()) { // save ad photos
                $modelUserAd->user_desc_id = $modelUserDesc->id;
                $modelUserAd->status_id = UserAd::STATUS_ACTIVE; // default for new ad
                $modelUserAd->created_at = time(); // updating Create time
                $modelUserAd->updated_at = time(); // updating Update time

                if ($modelUserAd->validate()) { // check new ad
                    $transactionUserAd = \Yii::$app->db->beginTransaction();
                    try {
                        $flagUserAd = $modelUserAd->save(false); // insert new ad
                        if ($flagUserAd == true) {
                            $transactionUserAd->commit();

                            //$modelPhotoAd->ad_id = $modelUserAd->id;
                        } else {
                            $transactionUserAd->rollBack();
                            return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var1'));
                        }
                    } catch (Exception $ex) {
                        $transactionUserAd->rollBack();
                        return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var2'));
                    }

                    // Insert each new Photo in database
                    foreach ($modelPhotoAd->arrayWebFilename as $file) {
                        $transactionAdPhoto = \Yii::$app->db->beginTransaction();
                        try {
                            $modelPhotoAdFile = new PhotoAd();
                            $modelPhotoAdFile->ad_id = $modelUserAd->id;
                            $modelPhotoAdFile->created_at = time();
                            $modelPhotoAdFile->updated_at = time();
                            $modelPhotoAdFile->photo_path = '/uploads/PhotoAd/'.$file;
                            //$modelPhotoAd->id = null;
                            //$modelPhotoAd->isNewRecord = true;
                            $flagPhotoAd = $modelPhotoAdFile->save(false); // insert

                            if ($flagPhotoAd == true) {
                                $transactionAdPhoto->commit();
                            } else {
                                $transactionAdPhoto->rollBack();
                                return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше фото не может быть сохранено. var3'));
                            }
                        } catch (Exception $ex) {
                            $transactionAdPhoto->rollBack();
                            return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше фото не может быть сохранено. var4'));
                        }
                    }

                    return Json::encode(array('status' => '1', 'type' => 'success', 'message' => 'Ваше объявление успешно сохранено.'));

                } else {
                    return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var5'.var_dump($modelUserAd->user_desc_id, $modelUserAd->status_id, $modelUserAd->created_at, $modelUserAd->updated_at, $modelUserAd->header, $modelUserAd->content, $modelUserAd->city_id, $modelUserAd->amount, $modelUserAd->category_id)));
                }
            } else {
                return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var6'.$modelPhotoAd->msg));
            }
        } else {
            // get cities and cxategories arrays for Select tags in Form
            $cities = UserCity::find()
                ->orderBy('city_name')
                //->asArray()
                ->all();
            $categories = AdCategory::find()
                ->orderBy('name')
                //->asArray()
                ->all();

            // go to create ad form
            return $this->render('EditeAd', [
                'selectCity' => $cities,
                'selectCategory' => $categories,
                'modelUserAd' => $modelUserAd,
                'modelPhotoAd' => $modelPhotoAd,
            ]);
        }
    }
*/
/*    public function actionUpdateAd()
    {
        // check user is a guest
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // if user profile is empty go to Homepage
        $modelUserDesc = UserDesc::find()->where(['user_id' => Yii::$app->user->getId()])->one();
        if (empty($modelUserDesc)) {
            return $this->goHome();
        }

        // check input parametrs (id for ad) for PUT method
        $nad = (preg_match("/^[0-9]*$/",Yii::$app->request->post('nad'))) ? Yii::$app->request->post('nad') : null;
        if (is_null($nad)) return $this->goHome();

        // check access to update your ads
        $modelUserAdId = UserAd::find()->where(['AND', ['id' => $nad], ['user_desc_id' => $modelUserDesc->id], ['status_id' => UserAd::STATUS_ACTIVE]])->one();
        if (empty($modelUserAdId)) {
            return $this->goHome();
        }

        //$modelUserAd = new UserAd();
        $modelPhotoAd = new PhotoAd();

        // get attbutes for Ad and Photos objects from Post request
        if (Yii::$app->request->isAjax && $modelUserAdId->load(Yii::$app->request->post()) && $modelPhotoAd->load(Yii::$app->request->post())) {
            $modelPhotoAd->imageFiles = UploadedFile::getInstances($modelPhotoAd, 'imageFiles');
            if ($modelPhotoAd->upload()) { //upload ad photos to the server
                $modelUserAdId->updated_at = time(); // updating Update time fo ad

                if ($modelUserAdId->validate()) {
                    $transactionUserAd = \Yii::$app->db->beginTransaction();
                    try {
                        $flagUserAdDelete = PhotoAd::deleteAll(['ad_id' => $modelUserAdId->id]); // delete old record of photos in database
                        $flagUserAdUpdate = $modelUserAdId->save(false); // update ad
                        if ($flagUserAdUpdate && $flagUserAdDelete) {
                            $transactionUserAd->commit();
                        } else {
                            $transactionUserAd->rollBack();
                            return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var1'));
                        }
                    } catch (Exception $ex) {
                        $transactionUserAd->rollBack();
                        return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var2'));
                    }

                    // Insert each new Photo in database
                    foreach ($modelPhotoAd->arrayWebFilename as $file) {
                        $transactionAdPhoto = \Yii::$app->db->beginTransaction();
                        try {
                            $modelPhotoAdFile = new PhotoAd();
                            $modelPhotoAdFile->ad_id = $modelUserAdId->id;
                            $modelPhotoAdFile->created_at = time();
                            $modelPhotoAdFile->updated_at = time();
                            $modelPhotoAdFile->photo_path = '/uploads/PhotoAd/'.$file;
                            //$modelPhotoAd->id = null;
                            //$modelPhotoAd->isNewRecord = true;
                            $flagPhotoAd = $modelPhotoAdFile->save(false);

                            if ($flagPhotoAd == true) {
                                $transactionAdPhoto->commit();
                            } else {
                                $transactionAdPhoto->rollBack();
                                return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше фото не может быть сохранено. var3'));
                            }
                        } catch (Exception $ex) {
                            $transactionAdPhoto->rollBack();
                            return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше фото не может быть сохранено. var4'));
                        }
                    }

                    return Json::encode(array('status' => '1', 'type' => 'success', 'message' => 'Ваше объявление успешно сохранено.'));

                } else {
                    return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var5'.var_dump($modelUserAd->user_desc_id, $modelUserAd->status_id, $modelUserAd->created_at, $modelUserAd->updated_at, $modelUserAd->header, $modelUserAd->content, $modelUserAd->city_id, $modelUserAd->amount, $modelUserAd->category_id)));
                }
            } else {
                return Json::encode(array('status' => '0', 'type' => 'warning', 'message' => 'Ваше объявление не может быть сохранено. var6'.$modelPhotoAd->msg));
            }
        } else {
            // get cities and categories arrays for Select tags in Form
            $cities = UserCity::find()
                ->orderBy('city_name')
                //->asArray()
                ->all();
            $categories = AdCategory::find()
                ->orderBy('name')
                //->asArray()
                ->all();

            // go to the Update form
            return $this->render('EditeAd', [
                'selectCity' => $cities,
                'selectCategory' => $categories,
                'modelUserAd' => $modelUserAdId,
                'modelPhotoAd' => $modelPhotoAd,
            ]);
        }
    }
*/
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
                        return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: id'));
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

                    // update in the properties in the Request object
                    foreach ($bodyRaw as $name => $value) {
                        $pos_begin = strpos($name, '[') + 1;
                        $pos_end = strpos($name, ']');
                        $name = substr($name, $pos_begin, $pos_end-$pos_begin);

                        if ($name != 'id') $modelRequest->$name = $value;
                    }
                } else {
                    $modelRequest = new Request();

                    // fill in the properties in the Request object
                    foreach ($bodyRaw as $name => $value) {
                        $pos_begin = strpos($name, '[') + 1;
                        $pos_end = strpos($name, ']');
                        $name = substr($name, $pos_begin, $pos_end-$pos_begin);
                        //if (isset($modelRequest->$name)) {
                        //    $modelRequest->$name = $value;
                        //}
                        //if (property_exists($modelRequest, $name)) {
                        if ($modelRequest->hasAttribute($name)) {
                            if ($name != 'id') $modelRequest->$name = $value;
                        }
                    }
                }


            }

            if ($modelRequest->validate()) {
                return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'success', 'message' => 'Успешно', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelRequest))));
            } else {
                return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации'));
            }
        //}
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

        $modelRequest = new Request();
        if (Yii::$app->request->isAjax) {

            $modelRequest->load(Yii::$app->request->post());

            // check input parametrs
            //$cit = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cit'))) ? Yii::$app->request->get('cit') : null;
            //$cat = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cat'))) ? Yii::$app->request->get('cat') : null;
            //$ser = (preg_match("/^[a-zA-Z0-9]*$/",Yii::$app->request->get('ser'))) ? Yii::$app->request->get('ser') : null;

            // select user ads by */*/* parametrs
            if (false) {
                // something
            } else {
                $query = Request::find();
                //$query = Request::find()
                //    ->where(['AND', ['city_id' => $var1], ['user_desc_id'=> $var2]]);

                $requestList = $query->orderBy('created_at')
                    //->offset($pagination->offset)
                    //->limit($pagination->limit)
                    //->leftJoin('photo_ad', '"user_ad"."id" = "photo_ad"."ad_id"')
                    //->with('adPhotos')
                    ->all();
            }

            //GET data from body request
            //Yii::$app->request->getBodyParams()
            $fh = fopen("php://input", 'r');
            $put_string = stream_get_contents($fh);
            $put_string = urldecode($put_string);
            $array_put = $this->parsingRequest($put_string);

            return Json::encode(array('method' => 'DELETE', 'status' => '1', 'type' => 'success', 'message' => 'Успешно', var_dump($array_put)));
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => '0', 'type' => 'error', 'message' => 'Ошибка'));
        }

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
