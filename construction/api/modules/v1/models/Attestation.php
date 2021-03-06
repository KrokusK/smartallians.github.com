<?php
namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "attestation".
 *
 */
class Attestation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%attestation}}';
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
     * Link to table contractor
     */
    public function getContractors()
    {
        return $this->hasMany(Contractor::className(), ['id' => 'contractor_id'])
            ->viaTable('contractor_attestation', ['attestation_id' => 'id']);
    }
}
