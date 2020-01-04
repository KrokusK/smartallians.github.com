<?php
namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "job_stages".
 *
 */
class JobStages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%job_stages}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['name','period','cost'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['period'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['cost'], 'double', 'message' => 'Значение должно быть числом'],
        ];

    }

    /**
     *
     * Link to table project
     */
    public function getProjects()
    {
        return $this->hasOne(Project::className(), ['job_stages_id' => 'id']);
    }
}
