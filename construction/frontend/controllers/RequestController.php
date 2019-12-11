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
        return $this->render('entry', [
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

            return Json::encode(array('status' => '1', 'type' => 'success', 'message' => 'Успешно'));
        } else {
            return Json::encode(array('status' => '0', 'type' => 'error', 'message' => 'Ошибка'));
        }







        // check user is a guest
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // if user profile is empty go to Homepage
        $modelUserDesc = UserDesc::find()->where(['user_id' => Yii::$app->user->getId()])->one();
        if (empty($modelUserDesc)) {
            return $this->goHome();
        }

        // Get id from user_description table
        $UserDesc = UserDesc::find()
            ->where(['user_id' => Yii::$app->user->getId()])
            ->asArray()
            ->one();

        // check input parametrs for GET method
        $cit = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cit'))) ? Yii::$app->request->get('cit') : null;
        $cat = (preg_match("/^[0-9]*$/",Yii::$app->request->get('cat'))) ? Yii::$app->request->get('cat') : null;
        $ser = (preg_match("/^[a-zA-Z0-9]*$/",Yii::$app->request->get('ser'))) ? Yii::$app->request->get('ser') : null;

        // select user ads by cities/categories/search text parametrs
        if(!empty($cit) && empty($cat)) {
            $query = UserAd::find()
                ->where(['AND', ['city_id' => $cit], ['user_desc_id'=> $UserDesc['id']]]);
            //->andWhere('in','user_desc_id', $arrayMyAdsId);
        }
        else if(empty($cit) && !empty($cat)) {
            $query = UserAd::find()
                ->where(['AND', ['category_id' => $cat], ['user_desc_id'=> $UserDesc['id']]]);
        }
        else if(!empty($cit) && !empty($cat)) {
            $query = UserAd::find()
                ->where(['AND', ['city_id' => $cit], ['category_id' => $cat], ['user_desc_id'=> $UserDesc['id']]]);
            //->where('city_id=:cit',[':cit' => $cit])
            //->andWhere('category_id=:cat',[':cat' => $cat]);
        } else {
            if(!empty($ser)) {
                $query = UserAd::find()
                    ->where(['AND', ['OR', ['like', 'LOWER(header)', strtolower($ser)], ['like', 'LOWER(content)', strtolower($ser)], ['amount' => (int)$ser]], ['user_desc_id'=> $UserDesc['id']]]);
                //->where(['like', 'LOWER(header)', strtolower($ser)])
                //->orWhere(['like', 'LOWER(content)', strtolower($ser)])
                //->orWhere(['amount' => (int)$ser]);
            } else {
                $query = UserAd::find()
                    ->where('user_desc_id=:UserDescId', [':UserDescId' => $UserDesc['id']]);
                //->where('in','id', $arrayMyAdsId);
            }
        }

        // pagination
        $pagination = new Pagination([
            'defaultPageSize' => 6,
            'totalCount' => $query->count(),
        ]);

        // select ads
        $userAds = $query->orderBy('header')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            //->leftJoin('photo_ad', '"user_ad"."id" = "photo_ad"."ad_id"')
            ->with('adPhotos', 'adStatus', 'userCities', 'adCategories')
            ->all();

        // display list my ads
        return $this->render('ListMyAds', [
            'userAds' => $userAds,
            'cit' =>  $cit,
            'cat' => $cat,
            'pagination' => $pagination,
        ]);



        //return $this->render('index');
    }














    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            if (Yii::$app->user->login($user)) {
                Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
}
