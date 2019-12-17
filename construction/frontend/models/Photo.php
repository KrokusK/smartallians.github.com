<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "photo".
 *
 */
class Photo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 1;
    const STATUS_ACTIVE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%photo}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['description', 'caption', 'path'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer', 'skipOnEmpty' => true],
            [['response_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $ResponseId = Response::find()->select(['id'])->asArray()->all();
                    $ResponseIdStr = [];
                    foreach ($ResponseId as $item) {
                        array_push($ResponseIdStr, "{$item['id']}");
                    }
                    return $ResponseIdStr;
                },
                'message' => 'Отклик не выбран из списка', 'skipOnEmpty' => true],
            [['request_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesRequestId = Request::find()->select(['id'])->asArray()->all();
                    $statusesRequestIdStr = [];
                    foreach ($statusesRequestId as $item) {
                        array_push($statusesRequestIdStr, "{$item['id']}");
                    }
                    return $statusesRequestIdStr;
                },
                'message' => 'Заявка не выбрана из списка', 'skipOnEmpty' => true],
            [['position_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesPositionId = Request::find()->select(['id'])->asArray()->all();
                    $statusesPositionIdStr = [];
                    foreach ($statusesPositionId as $item) {
                        array_push($statusesPositionIdStr, "{$item['id']}");
                    }
                    return $statusesPositionIdStr;
                },
                'message' => 'Позиция не выбрана из списка', 'skipOnEmpty' => true],
            [['description'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['caption'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['path'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
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
