<?php
namespace api\modules\v2\controllers;

use api\modules\v2\models\Request;
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
                    'customer-create' => ['post'],
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
     * POST Method. RBAC table.
     * Check user by createCustomer permission
     *
     * @return json
     */
    public function actionCustomerCreate()
    {
        $postParams = Yii::$app->getRequest()->post();

        // check user is a guest
        if (empty(\Yii::$app->user->loginByAccessToken($postParams['token']))) {
            //return $this->goHome();
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        if (\Yii::$app->user->can('createCustomer')) {
            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Успешно: Вы можете добавлять записи в таблицы Заказчика'));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Вы не можете добавлять записи в таблицы Заказчика'));
        }
    }


    /**
     * POST Method. RBAC table.
     * Check user by updateOwnCustomer permission
     *
     * @return json
     */
    public function actionCustomerOwnUpdate()
    {
        $postParams = Yii::$app->getRequest()->post();

        // check user is a guest
        if (empty(\Yii::$app->user->loginByAccessToken($postParams['token']))) {
            //return $this->goHome();
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Аутентификация не выполнена'));
        }

        // Search record by id in the database
        $queryRequest = Request::find()
            ->where(['id' => $postParams['id']]);
        $modelRequest = $queryRequest->one();

        if (\Yii::$app->user->can('updateOwnCustomer')) {
            return Json::encode(array('method' => 'POST', 'status' => 0, 'type' => 'success', 'message' => 'Успешно: Вы можете обновить запись в таблице Заказчика с id: '.$postParams['id']));
        } else {
            return Json::encode(array('method' => 'POST', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка: Вы не можете обновить запись в таблице Заказчика с id: '.$postParams['id']));
        }
    }
}
