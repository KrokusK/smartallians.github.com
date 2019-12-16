<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "city".
 *
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%city}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['name'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['region_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesRegionId = Region::find()->select(['id'])->asArray()->all();
                    $statusesRegionIdStr = [];
                    foreach ($statusesRegionId as $item) {
                        array_push($statusesRegionIdStr, "{$item['id']}");
                    }
                    return $statusesRegionIdStr;
                },
                'message' => 'Регион не выбран из списка'],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

    }

    /**
     *
     * Link to table User_description
     */
    //public function getUserDescs()
    //{
    //    return $this->hasOne(UserDesc::className(), ['id' => 'user_desc_id']);
    //}

    /**
     *
     * Link to table Photo_ad
     */
    //public function getAdPhotos()
    //{
    //    return $this->hasMany(PhotoAd::className(), ['ad_id' => 'id']);
    //}
}
