<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contractor".
 *
 */
class Contractor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%contractor}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['profile_id', 'experience', 'cost'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['profile_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesProfileId = Profile::find()->select(['id'])->asArray()->all();
                    $statusesProfileIdStr = [];
                    foreach ($statusesProfileId as $item) {
                        array_push($statusesProfileIdStr, "{$item['id']}");
                    }
                    return $statusesProfileIdStr;
                },
                'message' => 'Профиль не выбран из списка'],
            [['experience'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['cost'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

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
     * Link to table porfolio
     */
    public function getPortfolios()
    {
        return $this->hasMany(Portfolio::className(), ['contractor_id' => 'id']);
    }

    /**
     *
     * Link to table attestation
     */
    public function getAttestations()
    {
        return $this->hasMany(Attestation::className(), ['id' => 'attestation_id'])
            ->viaTable('contractor_attestation', ['contractor_id' => 'id']);
    }

    /**
     *
     * Link to table kind_job
     */
    public function getKindJob()
    {
        return $this->hasMany(KindJob::className(), ['id' => 'kind_job_id'])
            ->viaTable('contractor_kind_job', ['contractor_id' => 'id']);
    }
}
