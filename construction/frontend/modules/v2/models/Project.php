<?php
namespace frontend\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "project".
 *
 */
class Project extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%project}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['job_stages_id','period','cost'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['job_stages_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $jobstagesId = JobStages::find()->select(['id'])->asArray()->all();
                    $jobstagesIdStr = [];
                    foreach ($jobstagesId as $item) {
                        array_push($jobstagesIdStr, "{$item['id']}");
                    }
                    return $jobstagesIdStr;
                },
                'message' => 'Этап работы не выбран из списка'],
            [['period'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['cost'], 'double', 'message' => 'Значение должно быть числом'],
        ];

    }

    /**
     *
     * Link to table order
     */
    public function getOrders()
    {
        return $this->hasOne(Order::className(), ['project_id' => 'id']);
    }

    /**
     *
     * Link to table project_documents
     */
    public function getProjectDocuments()
    {
        return $this->hasMany(ProjectDocuments::className(), ['project_id' => 'id']);
    }

    /**
     *
     * Link to table job_stages
     */
    public function getJobStages()
    {
        return $this->hasOne(JobStages::className(), ['id' => 'job_stages_id']);
    }
}
