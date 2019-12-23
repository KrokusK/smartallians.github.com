<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "materials".
 *
 */
class Materials extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%materials}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['request_id','response_id'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['request_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $requestId = Request::find()->select(['id'])->asArray()->all();
                    $requestIdStr = [];
                    foreach ($requestId as $item) {
                        array_push($requestIdStr, "{$item['id']}");
                    }
                    return $requestIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
            [['delivery_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $deliveryId = Delivery::find()->select(['id'])->asArray()->all();
                    $deliveryIdStr = [];
                    foreach ($deliveryId as $item) {
                        array_push($deliveryIdStr, "{$item['id']}");
                    }
                    return $deliveryIdStr;
                },
                'message' => 'Поставка не выбрана из списка'],
            [['material_type_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $materialTypeId = MaterialType::find()->select(['id'])->asArray()->all();
                    $materialTypeIdStr = [];
                    foreach ($materialTypeId as $item) {
                        array_push($materialTypeIdStr, "{$item['id']}");
                    }
                    return $materialTypeIdStr;
                },
                'message' => 'Тип материала не выбран из списка'],
            [['status_material_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusMaterialId = StatusMaterial::find()->select(['id'])->asArray()->all();
                    $statusMaterialIdStr = [];
                    foreach ($statusMaterialId as $item) {
                        array_push($statusMaterialIdStr, "{$item['id']}");
                    }
                    return $statusMaterialIdStr;
                },
                'message' => 'Статус материала не выбран из списка'],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['count'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['cost'], 'double', 'message' => 'Значение должно быть числом'],
        ];

    }

    /**
     *
     * Link to table request
     */
    public function getRequests()
    {
        return $this->hasOne(Request::className(), ['id' => 'request_id']);
    }

    /**
     *
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasOne(Delivery::className(), ['id' => 'delivery_id']);
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
     * Link to table status_completion
     */
    public function getStatusCompletion()
    {
        return $this->hasOne(StatusCompletion::className(), ['id' => 'status_completion_id']);
    }

    /**
     *
     * Link to table project
     */
    public function getProjects()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     *
     * Link to table feedback
     */
    public function getFeedbacks()
    {
        return $this->hasOne(Feedback::className(), ['id' => 'feedback_id']);
    }

    /**
     *
     * Link to table profile
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['id' => 'profile_id'])
            ->viaTable('profile_rrod', ['materials_id' => 'id']);
    }
}
