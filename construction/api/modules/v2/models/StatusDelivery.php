<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "status_delivery".
 *
 */
class StatusDelivery extends \yii\db\ActiveRecord
{
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
     *
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasOne(Delivery::className(), ['status_delivery_id' => 'id']);
    }

    /**
     *
     * Get records from status_delivery table
     */
    public function getStatusDeliveryData($params = [], $assoc = [])
    {
        $query = StatusDelivery::find();
        foreach ($assoc as $name => $value) {
            if (array_key_exists($value, $params)) {
                if ($this->hasAttribute($name)) {
                    $this->$name = $params[$assoc[$name]];
                    if (!$this->validate($name)) return Json::encode(array('method' => 'GET', 'status' => 1, 'type' => 'error', 'message' => 'Ошибка валидации: параметр '.$valueRequestAssoc));

                    $query->andWhere([$name => $params[$assoc[$name]]]);
                }
            }
        }

        $modelStatusDelivery = $query->orderBy('id')
            ->asArray()
            ->all();

        return ArrayHelper::toArray($modelStatusDelivery);
    }
}
