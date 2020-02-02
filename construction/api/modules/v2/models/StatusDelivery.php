<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "status_delivery".
 *
 */
class StatusDelivery extends \yii\db\ActiveRecord
{
    /**
    * Because the field names may match within a single query,
    * the parameter names may not match the table field names.
    * To solve this problem let's create an associative array
    */
    protected $assocStatusDelivery = array ('id' => 'id', 'name' => 'name');

    /**
     * properties
     */
    protected $modelResponseMessage;

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
     * Create a model
     */
    public function __construct()
    {
        // Set property
        $this->modelResponseMessage = new ResponseMessage();
    }

    /**
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasOne(Delivery::className(), ['status_delivery_id' => 'id']);
    }

    /**
     * Get StatusDelivery object properties by request params
     *
     *
     */
    public function getDataStatusDelivery($params = [])
    {
        // Search data
        $query = StatusDelivery::find();
        foreach ($this->assocStatusDelivery as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name)) {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
                $query->andWhere([$name => $params[$value]]);
            }
        }
        $dataStatusDelivery = $query->orderBy('id')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataStatusDelivery)) {
            $this->modelResponseMessage->saveDataMessage(ArrayHelper::toArray($dataStatusDelivery));
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Записи не найдены');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}
