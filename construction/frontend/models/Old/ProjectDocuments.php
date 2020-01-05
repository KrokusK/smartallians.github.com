<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "project_documents".
 *
 */
class ProjectDocuments extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%project_documents}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['project_id','name','description','path'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['project_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $projectId = Project::find()->select(['id'])->asArray()->all();
                    $projectIdStr = [];
                    foreach ($projectId as $item) {
                        array_push($projectIdStr, "{$item['id']}");
                    }
                    return $projectIdStr;
                },
                'message' => 'Проект не выбран из списка'],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['description'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['path'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

    }

    /**
     *
     * Link to table project
     */
    public function getProjects()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }
}
