<?php
namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "delivery_place".
 *
 */
class DeliveryPlace extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%delivery_place}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['name', 'city_id'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['city_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $CityId = Region::find()->select(['id'])->asArray()->all();
                    $CityIdStr = [];
                    foreach ($CityId as $item) {
                        array_push($CityIdStr, "{$item['id']}");
                    }
                    return $CityIdStr;
                },
                'message' => 'Город не выбран из списка'],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

    }


    /**
     *
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasOne(Delivery::className(), ['delivery_place_id' => 'id']);
    }

    /**
     *
     * Link to table city
     */
    public function getCities()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }
}
