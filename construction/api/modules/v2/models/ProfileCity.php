<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "profile_city".
 *
 */
class ProfileCity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile_city}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['profile_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $ProfileId = Profile::find()->select(['id'])->asArray()->all();
                    $ProfileIdStr = [];
                    foreach ($ProfileId as $item) {
                        array_push($ProfileIdStr, "{$item['id']}");
                    }
                    return $ProfileIdStr;
                },
                'message' => 'Вид работы не выбран из списка'],
            [['city_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $CityId = City::find()->select(['id'])->asArray()->all();
                    $CityIdStr = [];
                    foreach ($CityId as $item) {
                        array_push($CityIdStr, "{$item['id']}");
                    }
                    return $CityIdStr;
                },
                'message' => 'Город не выбран из списка'],
        ];

    }

}
