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
                'only' => ['data'],
                'rules' => [
                    [
                        'actions' => ['data'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'data' => ['post'],
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

            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Успешно!', 'id_user' => Yii::$app->user->getId(), 'id_profile' => $userData['id'], 'id_kind_user' => $userData['kind_user_id'], 'last_name' => $userData['last_name'], 'first_name' => $userData['first_name'], 'middle_name' => $userData['middle_name'], 'username' => $userData['users']['username'], 'email' => $userData['users']['email'], 'phone' => $userData['users']['phone'], 'token' => $userData['users']['verification_token'], 'avatar' => $userData['avatar']));
        } else {
            return Json::encode(array('status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }
    }


}
