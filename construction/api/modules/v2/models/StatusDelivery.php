<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "status_delivery".
 *
 */
class StatusDelivery extends \yii\db\ActiveRecord
{
    /**
     * properties
     */
    public $method;
    public $message;
    protected $userByToken;
    protected $params;

     /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%status_delivery}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['name'], 'required', 'message' => 'Поле должно быть заполнено'],
            [
                ['id'],
                'match',
                'pattern' => '/^[0-9]*$/',
                'message' => 'поле id  должно быть типа integer',
                'skipOnEmpty' => true
            ],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

    }

    /**
     * init
     */

    public function init()
    {
        parent::init();

        // Set properties: method, params
        $this->setProperties();
    }

    /**
     * Get params from request
     *
     */

    public function getRequestParams()
    {
        return $this->params;
    }

    /**
     * Defining request method and
     * set params by values from request
     */

    public function setProperties()
    {
        $this->method = strtolower(Yii::$app->getRequest()->getMethod());
        $this->setParamsByMethod();
    }

    /**
     * Set params from request
     *
     */

    public function setParamsByMethod()
    {
        switch ($this->method) {
            case 'get':
                $this->params = Yii::$app->getRequest()->get();
                break;
            case 'post':
                $this->params = Yii::$app->getRequest()->post();
                break;
            case 'put':
            case 'patch':
            case 'delete':
                $this->params = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        }
    }

    /**
     * Authorization user by token in params
     */
    public function loginByParams()
    {
        if (array_key_exists('token', $this->params)) {
            $this->userByToken = \Yii::$app->user->loginByAccessToken($this->params['token']);
            if (!empty($this->userByToken)) {
                return $this->userByToken;
            }
        }

        $this->setMessage(1, 'Ошибка: Аутентификация не выполнена');
        return null;
    }

    /**
     * Set text message
     */
    public function setMessage($status = 1, $message = '') {
        switch ($status) {
            case 0:
                $this->message = [
                    'method' => $this->method,
                    'status' => $status,
                    'type' => 'success',
                    'message' => $message
                ];
                break;
            case 1:
                $this->message = [
                    'method' => $this->method,
                    'status' => $status,
                    'type' => 'error',
                    'message' => $message
                ];
        }
    }

    /**
     *
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasOne(Delivery::className(), ['status_delivery_id' => 'id']);
    }
}
