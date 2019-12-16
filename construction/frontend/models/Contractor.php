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
