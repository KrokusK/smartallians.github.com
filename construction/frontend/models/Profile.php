<?php
namespace frontend\models;

use common\models\User;
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
            [['user_id', 'kind_user_id', 'type_job_id', 'fio', 'firm_name', 'inn', 'site', 'avatar', 'updated_at', 'created_at'], 'required', 'message' => 'Поле должно быть заполнено'],
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
     * Link to table user
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     *
     * Link to table kind_user
     */
    public function getKindUser()
    {
        return $this->hasOne(KindUser::className(), ['id' => 'kind_user_id']);
    }

    /**
     *
     * Link to table type_job
     */
    public function getTypeJob()
    {
        return $this->hasOne(TypeJob::className(), ['id' => 'type_job_id']);
    }

    /**
     *
     * Link to table city
     */
    public function getCities()
    {
        return $this->hasMany(City::className(), ['id' => 'city_id'])
            ->viaTable('profile_city', ['profile_id' => 'id']);
    }

    /**
     *
     * Link to table specialization
     */
    public function getSpecializations()
    {
        return $this->hasMany(Specialization::className(), ['id' => 'specialization_id'])
            ->viaTable('profile_specialization', ['profile_id' => 'id']);
    }

    /**
     *
     * Link to table response
     */
    public function getResponses()
    {
        return $this->hasMany(Response::className(), ['id' => 'response_id'])
            ->viaTable('profile_rrod', ['profile_id' => 'id']);
    }

    /**
     *
     * Link to table request
     */
    public function getRequests()
    {
        return $this->hasMany(Request::className(), ['id' => 'request_id'])
            ->viaTable('profile_rrod', ['profile_id' => 'id']);
    }
}
