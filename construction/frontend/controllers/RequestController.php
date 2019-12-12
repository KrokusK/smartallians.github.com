<?php
namespace frontend\controllers;

use frontend\models\Request;
//use frontend\models\ResendVerificationEmailForm;
//use frontend\models\UserAd;
//use frontend\models\UserDesc;
//use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
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
        if (Yii::$app->request->isAjax) {

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

            return Json::encode(array('method' => 'PUT', 'status' => '1', 'type' => 'success', 'message' => 'Успешно', 'nad' => Yii::$app->getRequest()->getPut('nad'), 'modelRequest->address' => $modelRequest->address));
        } else {
            return Json::encode(array('method' => 'PUT', 'status' => '0', 'type' => 'error', 'message' => 'Ошибка'));
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

            return Json::encode(array('method' => 'DELETE', 'status' => '1', 'type' => 'success', 'message' => 'Успешно'));
        } else {
            return Json::encode(array('method' => 'DELETE', 'status' => '0', 'type' => 'error', 'message' => 'Ошибка'));
        }

    }
}
