<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
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
    protected $assocStatusDelivery = [
        'id' => 'id',
        'name' => 'name',
        'limitRec' => 'limit_rec',
        'offsetRec' => 'offset_rec'
    ];

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
     * @params parameters for filtering
     * @limitRec limit records
     * @offsetRec offset records
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataStatusDelivery($params = [], $limitRec = 10, $offsetRec = 0)
    {
        // check $limitRec and $offsetRec
        if (!preg_match("/^[0-9]*$/",$limitRec)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр limitRec');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
        if (!preg_match("/^[0-9]*$/",$offsetRec)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр offsetRec');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

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
            ->limit($limitRec)
            ->offset($offsetRec)
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
