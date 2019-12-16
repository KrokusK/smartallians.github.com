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
            [['status_request_id', 'city_id', 'address', 'name', 'description'], 'required', 'message' => 'Поле должно быть заполнено'],
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
            [['budjet'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['period'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['date_begin'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['update_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            [['create_at'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer'],
            /*
            [['user_desc_id', 'status_id', 'header', 'content', 'city_id', 'amount', 'category_id'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['status_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesId = AdStatus::find()->select(['id'])->asArray()->all();
                    $statusesIdStr = [];
                    foreach ($statusesId as $item) {
                        array_push($statusesIdStr, "{$item['id']}");
                    }
                    return $statusesIdStr;
                },
                'message' => 'Статус не выбран из списка'],
            [['header'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['content'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['city_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $citiesId = UserCity::find()->select(['id'])->asArray()->all();
                    $citiesIdStr = [];
                    foreach ($citiesId as $item) {
                        array_push($citiesIdStr, "{$item['id']}");
                    }
                    return $citiesIdStr;
                },
                'message' => 'Город не выбран из списка'],
            [['amount'], 'double', 'message' => 'Значение должно быить числом'],
            [['category_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $categoriesId = AdCategory::find()->select(['id'])->asArray()->all();
                    $categoriesIdStr = [];
                    foreach ($categoriesId as $item) {
                        array_push($categoriesIdStr, "{$item['id']}");
                    }
                    return $categoriesIdStr;
                },
                'message' => 'Категория не выбрана из списка'],
            */
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
