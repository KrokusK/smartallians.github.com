<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "request".
 *
 */
class Request extends \yii\db\ActiveRecord
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
        return '{{%request}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['city_id', 'address', 'description', 'task', 'budjet', 'date_begin', 'date_end', 'updated_at', 'created_at', 'kind_job_id'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer', 'skipOnEmpty' => true],
            [['status_request_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesRequestId = StatusRequest::find()->select(['id'])->asArray()->all();
                    $statusesRequestIdStr = [];
                    foreach ($statusesRequestId as $item) {
                        array_push($statusesRequestIdStr, "{$item['id']}");
                    }
                    return $statusesRequestIdStr;
                },
                'message' => 'Статус заявки не выбран из списка'],
            [['city_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesCitesId = City::find()->select(['id'])->asArray()->all();
                    $statusesCitesIdStr = [];
                    foreach ($statusesCitesId as $item) {
                        array_push($statusesCitesIdStr, "{$item['id']}");
                    }
                    return $statusesCitesIdStr;
                },
                'message' => 'Город не выбран из списка'],
            [['address'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['description'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['task'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['budjet'], 'double', 'message' => 'Значение должно быть числом'],
            [['period'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['date_begin'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['updated_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['created_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            
        ];

    }

    /**
     *
     * Link to table status_request
     */
    public function getStatusRequest()
    {
        return $this->hasOne(StatusRequest::className(), ['id' => 'status_request_id']);
    }

    /**
     *
     * Link to table city
     */
    public function getCites()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     *
     * Link to table Response
     */
    public function getResponses()
    {
        return $this->hasMany(Response::className(), ['request_id' => 'id']);
    }

    /**
     *
     * Link to table photo
     */
    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['request_id' => 'id']);
    }

    /**
     *
     * Link to table profile
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['id' => 'profile_id'])
            ->viaTable('profile_rrod', ['request_id' => 'id']);
    }

    /**
     *
     * Link to table kind_job
     */
    public function getKindJob()
    {
        return $this->hasMany(KindJob::className(), ['id' => 'kind_job_id'])
            ->viaTable('request_kind_job', ['request_id' => 'id']);
    }
}
