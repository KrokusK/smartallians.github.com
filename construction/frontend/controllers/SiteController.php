<?php
namespace frontend\controllers;

use frontend\modules\v2\models\Profile;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\SignupAccount;
use frontend\models\ModuleSMS;
use frontend\models\UserRequestData;
use frontend\models\ContactForm;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;

/**
 * Site controller
 */
class SiteController extends Controller
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
                    //'login' => ['post'],
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
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Don't response on the OPTIONS request.
     *
     * @return nothing
     */
    public function actionOptions()
    {
        // do nothing

        // test
        //return Json::encode(array('method' => 'OPTIONS', 'status' => 0, 'type' => 'success'));
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        setcookie('_identity-frontend', 'data', time()+60*60*24*365, '/', $domain, false);
        setcookie('advanced-frontend', 'data', time()+60*60*24*365, '/', $domain, false);

        // получение коллекции (yii\web\CookieCollection) из компонента "response"
        $cookies = Yii::$app->response->cookies;

        // добавление новой куки в HTTP-ответ
        $cookies->add(new \yii\web\Cookie([
            'name' => 'test',
            'value' => 'zh-CN',
        ]));

        // check user is a guest
        if (!Yii::$app->user->isGuest) {
            //return $this->goHome();

            // Get user data from tables
            $query = Profile::find()
                ->where(['user_id' => Yii::$app->user->getId()]);
            $userData = $query->orderBy('id')
                ->with('users')
                ->asArray()
                ->one();

            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Вы уже авторизованы!', 'id_user' => Yii::$app->user->getId(), 'id_profile' => $userData['id'], 'id_kind_user' => $userData['kind_user_id'], 'fio' => $userData['fio'], 'username' => $userData['users']['username'], 'email' => $userData['users']['email'], 'avatar' => $userData['avatar']));
        }

        //if (Yii::$app->request->isAjax) {
        if (Yii::$app->request->isPost) {
            //GET data from body request
            //Yii::$app->request->getBodyParams()
            $fh = fopen("php://input", 'r');
            $put_string = stream_get_contents($fh);
            $put_string = urldecode($put_string);

            $bodyRaw = json_decode(Yii::$app->getRequest()->getRawBody(), true);

            $modelLoginForm = new LoginForm();

            // load attributes in LoginForm object
            // example: yiisoft/yii2/base/Model.php
            if (is_array($bodyRaw)) {
                // fill in the properties in the LoginForm object
                foreach ($bodyRaw as $name => $value) {
                    //$pos_begin = strpos($name, '[') + 1;
                    //if (strtolower(substr($name, 0, $pos_begin - 1)) != 'loginform') return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ошибка валидации: '.$name));
                    //$pos_end = strpos($name, ']');
                    //$name = substr($name, $pos_begin, $pos_end-$pos_begin);

                    $modelLoginForm->$name = $value;
                }
            }

            if ($modelLoginForm->validate()) {

                // Проверка логина пароля
                /*
                $user = $modelLoginForm->getUser();
                if (!$user) {
                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Введен неверный логин'));
                } elseif (!$user->validatePassword($modelLoginForm->password)) {
                    return Json::encode(array('method' => 'POST', 'status' => 2, 'type' => 'error', 'message' => 'Введен неверный пароль'));
                }
                */

                if ($modelLoginForm->login()) {
                    //return $this->goBack();

                    // Get user data from tables
                    $query = Profile::find()
                        ->where(['user_id' => Yii::$app->user->getId()]);
                    $userData = $query->orderBy('id')
                        ->with('users')
                        ->asArray()
                        ->one();

                    //return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Авторизация прошла успешно!', var_dump($bodyRaw), var_dump(ArrayHelper::toArray($modelLoginForm))));
                    return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Авторизация прошла успешно!', 'id_user' => Yii::$app->user->getId(), 'id_profile' => $userData['id'], 'id_kind_user' => $userData['kind_user_id'], 'fio' => $userData['fio'], 'username' => $userData['users']['username'], 'email' => $userData['users']['email'], 'avatar' => $userData['avatar']));
                } else {
                    $modelLoginForm->password = '';

                    return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Введен неверный логин или пароль'));

                    //return $this->render('login');
                }
            } else {
                return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Введен неверный логин или пароль'));

                //return $this->render('index');
            }
        } else {
            //return Json::encode(array('method' => 'POST', 'status' => '1', 'type' => 'error', 'message' => 'Ожидается POST запрос'));
        }
        //}
    }
/*
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
*/
    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return Json::encode(array('method' => 'POST', 'status' => '0', 'type' => 'success', 'message' => 'Разлогирование прошло успешно!'));
        //return $this->goHome();
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
    public function actionSignupOther()
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
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        try {
            $modelUserRequestData = new UserRequestData();
            $modelSignupAccount = new SignupAccount();

            $params = $modelUserRequestData->getRequestParams();
            $msg = $modelSignupAccount->signup($params);
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $msg;
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Verify email
     *
     * @return mixed
     */
    public function actionEmailVerify()
    {
        try {
            $modelUserRequestData = new UserRequestData();
            $modelSignupAccount = new SignupAccount();

            $params = $modelUserRequestData->getRequestParams();
            return $modelSignupAccount->sendEmailVerifyCode($params);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Verify phone.
     *
     * @return mixed
     */
    public function actionPhoneVerifyClient()
    {
        try {
            $modelUserRequestData = new UserRequestData();
            $modelSignupForm = new SignupForm();

            $params = $modelUserRequestData->getRequestParams();
            return $modelSignupForm->sendPhoneVerifyCode($params);
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Verify phone
     *
     * @return mixed
     */
    public function actionPhoneVerify()
    {
        try {
            $modelUserRequestData = new UserRequestData();
            $paramsRequest = $modelUserRequestData->getRequestParams();

            $modelSMS = new ModuleSMS('20A77165-3182-6775-558D-623A2BC81EDB');
            $paramsSMS = [
                'to' => $paramsRequest['to'],
                'msg' => $paramsRequest['msg']
            ];
            $postSMS = (object) $paramsSMS;
            $responseSMS = $modelSMS->send_one($postSMS);
            return Json::encode(ArrayHelper::toArray($responseSMS));
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
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
