<?php
namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "delivery".
 *
 */
class Delivery extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%delivery}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['departure_place_id', 'delivery_place_id', 'status_delivery_id', 'status_payment_id', 'cost'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['departure_place_id'], 'in', 'range' =>
                function ($attribute, $params) {
                    $departurePlaceId = DeparturePlace::find()->select(['id'])->asArray()->all();
                    $departurePlaceIdStr = [];
                    foreach ($departurePlaceId as $item) {
                        array_push($departurePlaceIdStr, "{$item['id']}");
                    }
                    return $departurePlaceIdStr;
                },
                'message' => 'Место отправления не выбрано из списка'],
            [['delivery_place_id'], 'in', 'range' =>
                function ($attribute, $params) {
                    $deliveryPlaceId = DeliveryPlace::find()->select(['id'])->asArray()->all();
                    $deliveryPlaceIdStr = [];
                    foreach ($deliveryPlaceId as $item) {
                        array_push($deliveryPlaceIdStr, "{$item['id']}");
                    }
                    return $deliveryPlaceIdStr;
                },
                'message' => 'Место отправки не выбрано из списка'],
            [['status_delivery_id'], 'in', 'range' =>
                function ($attribute, $params) {
                    $statusDeliveryId = StatusDelivery::find()->select(['id'])->asArray()->all();
                    $statusDeliveryIdStr = [];
                    foreach ($statusDeliveryId as $item) {
                        array_push($statusDeliveryIdStr, "{$item['id']}");
                    }
                    return $statusDeliveryIdStr;
                },
                'message' => 'Статус поставки не выбран из списка'],
            [['status_payment_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusPaymentId = StatusPayment::find()->select(['id'])->asArray()->all();
                    $statusPaymentIdStr = [];
                    foreach ($statusPaymentId as $item) {
                        array_push($statusPaymentIdStr, "{$item['id']}");
                    }
                    return $statusPaymentIdStr;
                },
                'message' => 'Статус оплаты не выбран из списка'],
            [['cost'], 'double', 'message' => 'Значение должно быть числом'],
            [['updated_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['created_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
        ];

    }

    /**
     *
     * Link to table material
     */
    public function getMaterials()
    {
        return $this->hasOne(Material::className(), ['delivery_id' => 'id']);
    }

    /**
     *
     * Link to table departure_place
     */
    public function getDeparturePlace()
    {
        return $this->hasOne(DeparturePlace::className(), ['id' => 'departure_place_id']);
    }

    /**
     *
     * Link to table delivery_place
     */
    public function getDeliveryPlace()
    {
        return $this->hasOne(DeliveryPlace::className(), ['id' => 'delivery_place_id']);
    }

    /**
     *
     * Link to table status_delivery
     */
    public function getStatusDelivery()
    {
        return $this->hasOne(StatusDelivery::className(), ['id' => 'status_delivery_id']);
    }

    /**
     *
     * Link to table status_payment
     */
    public function getStatusPayment()
    {
        return $this->hasOne(StatusPayment::className(), ['id' => 'status_payment_id']);
    }

    /**
     *
     * Link to table profile
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['id' => 'profile_id'])
            ->viaTable('profile_rrod', ['delivery_id' => 'id']);
    }

}
