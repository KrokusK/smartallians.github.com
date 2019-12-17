<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "response".
 *
 */
class Response extends \yii\db\ActiveRecord
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
        return '{{%response}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['status_response_id', 'request_id', 'description', 'cost', 'period', 'updated_at', 'created_at'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer', 'skipOnEmpty' => true],
            [['status_response_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesResponseId = StatusResponse::find()->select(['id'])->asArray()->all();
                    $statusesResponseIdStr = [];
                    foreach ($statusesResponseId as $item) {
                        array_push($statusesResponseIdStr, "{$item['id']}");
                    }
                    return $statusesResponseIdStr;
                },
                'message' => 'Статус отклика не выбран из списка'],
            [['request_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesRequestId = Request::find()->select(['id'])->asArray()->all();
                    $statusesRequestIdStr = [];
                    foreach ($statusesRequestId as $item) {
                        array_push($statusesRequestIdStr, "{$item['id']}");
                    }
                    return $statusesRequestIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
            [['description'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['cost'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['period'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['updated_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['created_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
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
