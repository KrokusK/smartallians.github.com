<?php
namespace frontend\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "status_feedback".
 *
 */
class StatusFeedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%status_feedback}}';
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
     * Link to table feedback
     */
    public function getStatusFeedback()
    {
        return $this->hasOne(Feedback::className(), ['status_feedback_id' => 'id']);
    }
}
