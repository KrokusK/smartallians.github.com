<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "portfolio".
 *
 */
class Portfolio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%portfolio}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['contractor_id', 'name'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['contractor_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesContractorId = Contractor::find()->select(['id'])->asArray()->all();
                    $statusesContractorIdStr = [];
                    foreach ($statusesContractorId as $item) {
                        array_push($statusesContractorIdStr, "{$item['id']}");
                    }
                    return $statusesContractorIdStr;
                },
                'message' => 'Исполнитель не выбран из списка'],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

    }

    /**
     *
     * Link to table contractor
     */
    public function getContractors()
    {
        return $this->hasOne(Contractor::className(), ['id' => 'contractor_id']);
    }

    /**
     *
     * Link to table position
     */
    public function getPositions()
    {
        return $this->hasMany(Position::className(), ['portfolio_id' => 'id']);
    }
}
