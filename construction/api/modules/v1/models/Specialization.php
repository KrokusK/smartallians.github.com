<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "specialization".
 *
 */
class Specialization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%specialization}}';
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
     * Link to table profile
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['id' => 'profile_id'])
            ->viaTable('profile_specialization', ['specialization_id' => 'id']);
    }
}
