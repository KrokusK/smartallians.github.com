<?php
namespace api\modules\v2\controllers;

use api\modules\v2\rbac\CustomerRule;
//use yii\console\rbac;


use api\common\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class RbacActionsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'customer-create' => ['get'],
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
     * GET Method. RBAC table.
     * Get records by parameters
     *
     * @return json
     */
    public function actionCustomerCreate()
    {
        $getParams = Yii::$app->getRequest()->get();

        // check user is a guest
        if (empty(\Yii::$app->user->loginByAccessToken($getParams['token']))) {
            //return $this->goHome();
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }
        //$userByToken = User::findIdentityByAccessToken($getParams['token']);
        //if (empty($userByToken)) {
        //    //return $this->goHome();
        //    return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        //} else {
        //    \Yii::$app->user->loginByAccessToken($getParams['token']);
        //}

        if (\Yii::$app->user->can('createCustomer')) {
            return Json::encode(array('method' => 'GET', 'status' => 0, 'type' => 'success', 'message' => 'Успешно: Вы можете добавлять записи в таблицы Заказчика'));
        } else {
            return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Вы не можете добавлять записи в таблицы Заказчика'));
        }
    }
}
