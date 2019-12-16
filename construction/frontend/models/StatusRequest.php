<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "status_request".
 *
 */
class StatusRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%status_request}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['status_request_id', 'city_id', 'address', 'name', 'description'], 'required', 'message' => 'Поле должно быть заполнено'],
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
