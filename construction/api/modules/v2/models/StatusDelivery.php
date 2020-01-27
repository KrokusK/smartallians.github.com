<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;

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
    public $params;

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
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

    }

    /**
     *
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasOne(Delivery::className(), ['status_delivery_id' => 'id']);
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

    public function setMethodAndParams()
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
        /*
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
        */

        $this->params = Yii::$app->getRequest()->get();
        /*
        if ($this->method == 'get') {
            $this->params = Yii::$app->getRequest()->get();
        } elseif ($this->method == 'post') {
            $this->params = Yii::$app->getRequest()->post();
        } else {
            $this->params = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        }
        */
    }
}
