<?php
namespace frontend\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "feedback".
 *
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 1;
    const STATUS_ACTIVE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%feedback}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['profile_id', 'status_feedback_id', 'content', 'updated_at', 'created_at'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer', 'skipOnEmpty' => true],
            [['profile_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $ProfileId = Profile::find()->select(['id'])->asArray()->all();
                    $ProfileIdStr = [];
                    foreach ($ProfileId as $item) {
                        array_push($ProfileIdStr, "{$item['id']}");
                    }
                    return $ProfileIdStr;
                },
                'message' => 'Статус отклика не выбран из списка'],
            [['status_feedback_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesFeedbackId = StatusFeedback::find()->select(['id'])->asArray()->all();
                    $statusesFeedbackIdStr = [];
                    foreach ($statusesFeedbackId as $item) {
                        array_push($statusesFeedbackIdStr, "{$item['id']}");
                    }
                    return $statusesFeedbackIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
            [['content'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['updated_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['created_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
        ];

    }

    /**
     *
     * Link to table status_feedback
     */
    public function getStatusFeedback()
    {
        return $this->hasOne(StatusFeedback::className(), ['id' => 'status_feedback_id']);
    }

    /**
     *
     * Link to table profile
     */
    public function getProfiles()
    {
        return $this->hasOne(Profile::className(), ['id' => 'profile_id']);
    }

    /**
     *
     * Link to table order
     */
    public function getOrders()
    {
        return $this->hasOne(Order::className(), ['feedback_id' => 'id']);
    }
}
