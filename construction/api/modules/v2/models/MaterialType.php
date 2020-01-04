<?php
namespace frontend\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "material_type".
 *
 */
class MaterialType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%material_type}}';
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
     * Link to table material
     */
    public function getMaterials()
    {
        return $this->hasOne(Material::className(), ['material_type_id' => 'id']);
    }

}
