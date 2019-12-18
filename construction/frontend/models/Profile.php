<?php
namespace frontend\models;
namespace common\models\User;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "profile".
 *
 */
class Profile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['name', 'user_id', 'kind_user_id', 'type_job_id', 'inn', 'site', 'avatar', 'updated_at', 'created_at'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['user_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesUserId = User::find()->select(['id'])->asArray()->all();
                    $statusesUserIdStr = [];
                    foreach ($statusesUserId as $item) {
                        array_push($statusesUserIdStr, "{$item['id']}");
                    }
                    return $statusesUserIdStr;
                },
                'message' => 'Пользователь не выбран из списка'],
            [['kind_user_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesKindUserId = KindUser::find()->select(['id'])->asArray()->all();
                    $statusesKindUserIdStr = [];
                    foreach ($statusesKindUserId as $item) {
                        array_push($statusesKindUserIdStr, "{$item['id']}");
                    }
                    return $statusesKindUserIdStr;
                },
                'message' => 'Вид пользователя не выбран из списка'],
            [['type_job_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesTypeJobId = TypeJob::find()->select(['id'])->asArray()->all();
                    $statusesTypeJobIdStr = [];
                    foreach ($statusesTypeJobId as $item) {
                        array_push($statusesTypeJobIdStr, "{$item['id']}");
                    }
                    return $statusesTypeJobIdStr;
                },
                'message' => 'Форма работы не выбрана из списка'],
            [['fio'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['firm_name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['inn'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['site'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['avatar'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
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
