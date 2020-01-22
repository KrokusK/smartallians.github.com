<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "profile_specialization".
 *
 */
class ProfileSpecialization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile_specialization}}';
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
            [['specialization_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $SpecializationId = Specialization::find()->select(['id'])->asArray()->all();
                    $SpecializationIdStr = [];
                    foreach ($SpecializationId as $item) {
                        array_push($SpecializationIdStr, "{$item['id']}");
                    }
                    return $SpecializationIdStr;
                },
                'message' => 'Специализация не выбрана из списка'],
        ];

    }

}
