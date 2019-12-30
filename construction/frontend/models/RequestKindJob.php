<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "request_kind_job".
 *
 */
class RequestKindJob extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%request_kind_job}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['request_id', 'kind_job_id'], 'required', 'message' => 'Поле должно быть заполнено'],
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
            [['kind_job_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $kindJobId = KindJob::find()->select(['id'])->asArray()->all();
                    $kindJobIdStr = [];
                    foreach ($kindJobId as $item) {
                        array_push($kindJobIdStr, "{$item['id']}");
                    }
                    return $kindJobIdStr;
                },
                'message' => 'Вид работы не выбран из списка'],
        ];

    }

    /**
     *
     * Link to table contractor
     */
    public function getContractors()
    {
        return $this->hasMany(Contractor::className(), ['id' => 'contractor_id'])
            ->viaTable('contractor_kind_job', ['kind_job_id' => 'id']);
    }

    /**
     *
     * Link to table request
     */
    public function getRequests()
    {
        return $this->hasMany(Request::className(), ['id' => 'request_id'])
            ->viaTable('request_kind_job', ['kind_job_id' => 'id']);
    }
}
