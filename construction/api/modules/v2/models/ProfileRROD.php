<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "profile_rrod".
 *
 */
class ProfileRROD extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile_rrod}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['profile_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $ProfileId = Profile::find()->select(['id'])->asArray()->all();
                    $ProfileIdStr = [];
                    foreach ($ProfileId as $item) {
                        array_push($ProfileIdStr, "{$item['id']}");
                    }
                    return $ProfileIdStr;
                },
                'message' => 'Вид работы не выбран из списка'],
            [['request_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $RequestId = Request::find()->select(['id'])->asArray()->all();
                    $RequestIdStr = [];
                    foreach ($RequestId as $item) {
                        array_push($RequestIdStr, "{$item['id']}");
                    }
                    return $RequestIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
            [['response_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $ResponseId = Response::find()->select(['id'])->asArray()->all();
                    $ResponseIdStr = [];
                    foreach ($ResponseId as $item) {
                        array_push($ResponseIdStr, "{$item['id']}");
                    }
                    return $ResponseIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
            [['order_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $OrderId = Order::find()->select(['id'])->asArray()->all();
                    $OrderIdStr = [];
                    foreach ($OrderId as $item) {
                        array_push($OrderIdStr, "{$item['id']}");
                    }
                    return $OrderIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
            [['delivery_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $DeliveryId = Delivery::find()->select(['id'])->asArray()->all();
                    $DeliveryIdStr = [];
                    foreach ($DeliveryId as $item) {
                        array_push($DeliveryIdStr, "{$item['id']}");
                    }
                    return $DeliveryIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
        ];

    }

}
