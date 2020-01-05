<?php
namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Profile;
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
 * User controller
 */
class UserController extends Controller
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
     * Get a user data.
     *
     * @return json
     */
    public function actionData()
    {
        // check user is a guest
        if (!Yii::$app->user->isGuest && Yii::$app->request->isPost) {
            //return $this->goHome();

            // Get user data from tables
            $query = Profile::find()
                ->where(['user_id' => Yii::$app->user->getId()]);
            $userData = $query->orderBy('id')
                ->with('users')
                ->asArray()
                ->one();

            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Успешно!', 'id_user' => Yii::$app->user->getId(), 'id_profile' => $userData['id'], 'id_kind_user' => $userData['kind_user_id'], 'fio' => $userData['fio'], 'username' => $userData['users']['username'], 'email' => $userData['users']['email'], 'avatar' => $userData['avatar']));
        } else {
            return Json::encode(array('status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }
    }


}
