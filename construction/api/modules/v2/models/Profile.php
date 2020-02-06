<?php
namespace api\modules\v2\models;

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
     * Because the field names may match within a single query,
     * the parameter names may not match the table field names.
     * To solve this problem let's create an associative array
     */
    protected $assocProfile = [
        'id' => 'id',
        'user_id' => 'user_id',
        'kind_user_id' => 'kind_user_id',
        'type_job_id' => 'type_job_id',
        'fio' => 'fio',
        'firm_name' => 'firm_name',
        'inn' => 'inn',
        'site' => 'site',
        'avatar' => 'avatar',
        'about' => 'about',
        'last_name' => 'last_name',
        'first_name' => 'first_name',
        'middle_name' => 'middle_name'
    ];
    protected $assocContractor = [
        'experience' => 'experience',
        'cost' => 'cost',
        'passport' => 'passport'
    ];
    protected $assocProfileCity = ['city_id' => 'city'];
    protected $assocProfileSpecialization = ['specialization_id' => 'specialization'];

    /**
     * properties
     */
    protected $modelResponseMessage;

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
            [
                [
                    'user_id',
                    'kind_user_id',
                    'type_job_id',
                    'last_name',
                    'first_name',
                    'middle_name',
                    'avatar',
                    'updated_at',
                    'created_at'
                ],
                'required',
                'message' => 'Поле должно быть заполнено'
            ],
            [
                ['id'],
                'match',
                'pattern' => '/^[0-9]*$/',
                'message' => 'поле id  должно быть типа integer',
                'skipOnEmpty' => true
            ],
            [
                ['user_id'],
                'in',
                'range' =>
                function ( $attribute, $params ) {
                    $statusesUserId = User::find()->select(['id'])->asArray()->all();
                    $statusesUserIdStr = [];
                    foreach ($statusesUserId as $item) {
                        array_push($statusesUserIdStr, "{$item['id']}");
                    }
                    return $statusesUserIdStr;
                },
                'message' => 'Пользователь не выбран из списка'
            ],
            [
                ['kind_user_id'],
                'in',
                'range' =>
                function ( $attribute, $params ) {
                    $statusesKindUserId = KindUser::find()->select(['id'])->asArray()->all();
                    $statusesKindUserIdStr = [];
                    foreach ($statusesKindUserId as $item) {
                        array_push($statusesKindUserIdStr, "{$item['id']}");
                    }
                    return $statusesKindUserIdStr;
                },
                'message' => 'Вид пользователя не выбран из списка'
            ],
            [
                ['type_job_id'],
                'in',
                'range' =>
                function ( $attribute, $params ) {
                    $statusesTypeJobId = TypeJob::find()->select(['id'])->asArray()->all();
                    $statusesTypeJobIdStr = [];
                    foreach ($statusesTypeJobId as $item) {
                        array_push($statusesTypeJobIdStr, "{$item['id']}");
                    }
                    return $statusesTypeJobIdStr;
                },
                'message' => 'Форма работы не выбрана из списка'
            ],
            [
                ['fio'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 255',
                'skipOnEmpty' => true
            ],
            [
                ['firm_name'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 255',
                'skipOnEmpty' => true
            ],
            [
                ['inn'],
                'match',
                'pattern' => '/^[0-9]{12}$/',
                'message' => 'Число знаков не должно превышать 12, все знаки должны быть типа integer',
                'skipOnEmpty' => true
            ],
            [
                ['site'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 255',
                'skipOnEmpty' => true
            ],
            [
                ['avatar'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 255'
            ],
            [
                ['about'],
                'string',
                'max' => 512,
                'message' => 'Число знаков не должно превышать 512'
            ],
            [
                ['updated_at'],
                'match',
                'pattern' => '/^[0-9]*$/',
                'message' => 'поле должно быть типа integer'
            ],
            [
                ['created_at'],
                'match',
                'pattern' => '/^[0-9]*$/',
                'message' => 'поле должно быть типа integer'
            ],
            [
                ['last_name'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 15'
            ],
            [
                ['first_name'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 15'
            ],
            [
                ['middle_name'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 15'
            ],
        ];
    }

    /**
     * Create a model
     */
    public function __construct()
    {
        // Set property
        $this->modelResponseMessage = new ResponseMessage();
    }

    /**
     *
     * Link to table user
     */
    public function getUsers()
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
     * Link to table feedback
     */
    public function getFeedbacks()
    {
        return $this->hasOne(Feedback::className(), ['profile_id' => 'id']);
    }

    /**
     *
     * Link to table Contractor
     */
    public function getContractors()
    {
        return $this->hasOne(Contractor::className(), ['profile_id' => 'id']);
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

    /**
     *
     * Link to table order
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['id' => 'order_id'])
            ->viaTable('profile_rrod', ['profile_id' => 'id']);
    }

    /**
     *
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasMany(Order::className(), ['id' => 'delivery_id'])
            ->viaTable('profile_rrod', ['profile_id' => 'id']);
    }

    /**
     * Get Profile object properties by request params
     *
     * @params parameters for filtering
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataProfile($params = [], $userRole = [])
    {
        // Search data
        $query = Profile::find()
            ->leftJoin('contractor','contractor.profile_id = profile.id')
            ->leftJoin('profile_city','profile_city.profile_id = profile.id')
            ->leftJoin('profile_specialization','profile_specialization.profile_id = profile.id');
        // Get only owner records if user role isn't admin
        if (!in_array('admin', $userRole)) $query->Where(['profile.created_by' => Yii::$app->user->getId()]);
        // Add data filter for profile table
        $this->setProfileFilter($query, $params);
        // Add data filter for contractor table
        $this->setContractorFilter($query, $params);
        // Add data filter for city table
        $this->setProfileCityFilter($query, $params);
        // Add data filter for specialization table
        $this->setsetProfileSpecializationFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataProfile = $query->orderBy('created_at')
            ->with('contractors','cities','specializations')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataProfile)) {
            $this->modelResponseMessage->saveArrayMessage(ArrayHelper::toArray($dataProfile));
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Записи не найдены');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Set data filter for profile table
     *
     * @params parameters for filtering
     * @query object with data filter
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    private function setProfileFilter($query, $params = [])
    {
        // ilike parameters
        $ilikeParams = ['last_name', 'first_name', 'middle_name', 'firm_name', 'inn'];

        foreach ($this->assocProfile as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name)) {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
                if (in_array($name, $ilikeParams)) {
                    $query->andWhere(['ilike', $this->assocProfile, $name => $params[$value]]);
                } else {
                    $query->andWhere([$name => $params[$value]]);
                }
            }
        }
    }

    /**
     * Set data filter for contractor table
     *
     * @params parameters for filtering
     * @query object with data filter
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    private function setContractorFilter($query, $params = [])
    {
        foreach ($this->assocContractor as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name)) {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
                $query->andWhere(['contractor.'.$name => $params[$value]]);
            }
        }
    }

    /**
     * Set data filter for city table
     *
     * @params parameters for filtering
     * @query object with data filter
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    private function setProfileCityFilter($query, $params = [])
    {
        foreach ($this->assocProfileCity as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name)) {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
                $query->andWhere(['profile_city.'.$name => $params[$value]]);
            }
        }
    }

    /**
     * Set data filter for specialization table
     *
     * @params parameters for filtering
     * @query object with data filter
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    private function setProfileSpecializationFilter($query, $params = [])
    {
        foreach ($this->assocProfileSpecialization as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name)) {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
                $query->andWhere(['profile_specialization.'.$name => $params[$value]]);
            }
        }
    }

    /**
     * Set pagination params
     *
     * @params parameters for pagination
     * @query object with data filter
     */
    private function setPaginationParams($query, $params = [])
    {
        // default values
        $defauftParams = [
            'limitRec' => 10,
            'offsetRec' => 0
        ];

        foreach ($this->assocProfile as $name => $value) {
            switch ($name) {
                case 'limitRec':
                    if (array_key_exists($value, $params) && preg_match("/^[0-9]*$/",$params[$value])) {
                        $query->limit($params[$value]);
                    } else {
                        // default value
                        $query->limit($defauftParams[$name]);
                    }
                    break;
                case 'offsetRec':
                    if (array_key_exists($value, $params) && preg_match("/^[0-9]*$/",$params[$value])) {
                        $query->offset($params[$value]);
                    } else {
                        // default value
                        $query->offset($defauftParams[$name]);
                    }
            }
        }
    }
}
