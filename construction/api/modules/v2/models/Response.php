<?php
namespace api\modules\v2\models;

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
            [['request_id', 'description', 'cost', 'period', 'updated_at', 'created_at'], 'required', 'message' => 'Поле должно быть заполнено'],
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
            [['description'], 'string', 'max' => 512, 'message' => 'Число знаков не должно превышать 255'],
            [['cost'], 'integer', 'message' => 'Значение должно быть числом'],
            [['period'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['date_begin'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['date_end'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['updated_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['created_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
        ];

    }

    /**
     *
     * Link to table status_response
     */
    public function getStatusResponse()
    {
        return $this->hasOne(StatusResponse::className(), ['id' => 'status_response_id']);
    }

    /**
     *
     * Link to table request
     */
    public function getRequests()
    {
        return $this->hasOne(Request::className(), ['id' => 'request_id']);
    }

    /**
     *
     * Link to table photo
     */
    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['response_id' => 'id']);
    }

    /**
     *
     * Link to table profile
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['id' => 'profile_id'])
            ->viaTable('profile_rrod', ['response_id' => 'id']);
    }
}
