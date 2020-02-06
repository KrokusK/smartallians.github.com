<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "contractor".
 *
 */
class Contractor extends \yii\db\ActiveRecord
{
    /**
     * Because the field names may match within a single query,
     * the parameter names may not match the table field names.
     * To solve this problem let's create an associative array
     */
    protected $assocContractor = [
        'id' => 'id',
        'profile_id' => 'profile_id',
        'passport' => 'passport',
        'experience' => 'experience',
        'cost' => 'cost',
        'limitRec' => 'limit',
        'offsetRec' => 'offset'
    ];

    /**
     * properties
     */
    protected $modelResponseMessage;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%contractor}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [
                ['profile_id'],
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
                ['profile_id'], 'in', 'range' =>
                function ($attribute, $params) {
                    $statusesProfileId = Profile::find()->select(['id'])->asArray()->all();
                    $statusesProfileIdStr = [];
                    foreach ($statusesProfileId as $item) {
                        array_push($statusesProfileIdStr, "{$item['id']}");
                    }
                    return $statusesProfileIdStr;
                },
                'message' => 'Профиль не выбран из списка'
            ],
            [
                ['experience'],
                'string', 'max' => 255,
                'message' => 'Число знаков не должно превышать 255',
                'skipOnEmpty' => true
            ],
            [
                ['cost'],
                'double',
                'message' => 'Значение должно быть числом',
                'skipOnEmpty' => true
            ],
            [
                ['passport'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 255',
                'skipOnEmpty' => true
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
         * Link to table profile
         */
        public function getProfiles()
    {
        return $this->hasOne(Profile::className(), ['id' => 'profile_id']);
    }

        /**
         *
         * Link to table porfolio
         */
        public function getPortfolios()
    {
        return $this->hasMany(Portfolio::className(), ['contractor_id' => 'id']);
    }

        /**
         *
         * Link to table attestation
         */
        public function getAttestations()
    {
        return $this->hasMany(Attestation::className(), ['id' => 'attestation_id'])
            ->viaTable('contractor_attestation', ['contractor_id' => 'id']);
    }

        /**
         *
         * Link to table kind_job
         */
        public function getKindJob()
    {
        return $this->hasMany(KindJob::className(), ['id' => 'kind_job_id'])
            ->viaTable('contractor_kind_job', ['contractor_id' => 'id']);
    }

    /**
     * Get Contractor object properties by request params
     *
     * @params parameters for filtering
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataContractor($params = [], $userRoles = [])
    {
        // Search data
        $query = Contractor::find();
        // Get only owner records if user role isn't admin
        if (!in_array('admin', $userRoles)) $query->Where(['profile.created_by' => Yii::$app->user->getId()]);
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataContractor = $query->orderBy('id')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataContractor)) {
            $this->modelResponseMessage->saveArrayMessage(ArrayHelper::toArray($dataContractor));
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Записи не найдены');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Set data filter
     *
     * @params parameters for filtering
     * @query object with data filter
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    private function setDataFilter($query, $params = [])
    {
        foreach ($this->assocContractor as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name)) {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
                $query->andWhere([$name => $params[$value]]);
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

        foreach ($this->assocContractor as $name => $value) {
            switch ($name) {
                case 'limitRec':
                    if (array_key_exists($value, $params) && preg_match("/^[0-9]*$/", $params[$value])) {
                        $query->limit($params[$value]);
                    } else {
                        // default value
                        $query->limit($defauftParams[$name]);
                    }
                    break;
                case 'offsetRec':
                    if (array_key_exists($value, $params) && preg_match("/^[0-9]*$/", $params[$value])) {
                        $query->offset($params[$value]);
                    } else {
                        // default value
                        $query->offset($defauftParams[$name]);
                    }
            }
        }
    }

    /**
     * Set Contractor properties and
     * save object into the Db
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function addDataContractor($params = [])
    {
        // fill in the properties in the Contractor object
        foreach ($this->assocContractor as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name) && $name != 'id') {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }
        }
        $this->created_by = Yii::$app->user->getId();

        return $this->saveDataObject();
    }

    /**
     * Save Contractor object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function saveDataObject()
    {
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagContractor = $this->save(false); // insert into Contractor table

                if ($flagContractor == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть сохранен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть сохранен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Исполнитель успешно сохранен');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Get Contractor object by id
     *
     * @params parameters with properties
     * @notEmpty check on empty
     *
     * @throws InvalidArgumentException if returned error
     */
    public function getDataContractorById($params = [])
    {
        if (array_key_exists($this->assocContractor['id'], $params)) {
            // check id parametr
            if (!preg_match("/^[0-9]*$/", $params[$this->assocContractor['id']])) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            // Search record by id in the database
            $queryContractor = Contractor::find()->where(['id' => $params[$this->assocContractor['id']]]);
            $modelContractor = $queryContractor->one();
            if (empty($modelContractor)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Исполнитель по id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            return $modelContractor;
        } else {
            $this->modelResponseMessage->saveErrorMessage('Отсутствет id параметр в запросе');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Set Contractor properties and
     * update object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function updateDataContractor($params = [])
    {
        // fill in the properties in the Contractor object
        foreach ($this->assocContractor as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name) && $name != 'id') {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }
        }

        return $this->updateDataObject();
    }

    /**
     * Update Contractor object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function updateDataObject()
    {
        // Update Contractor object
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagContractor = $this->save(false); // update Contractor table

                if ($flagContractor) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть обновлен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть обновлен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Исполнитель успешно сохранен');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Check Id in params. Is this null?
     *
     * @params parameters with properties
     *
     * @bool return true if id is null
     */
    public function isNullIdInParams($params = [])
    {
        if (array_key_exists($this->assocContractor['id'], $params)
            && !empty($params[$this->assocContractor['id']])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check other parameters in addition to the token
     *
     * @params parameters with properties
     *
     * @bool return true if id is null
     */
    public function isOtherParams($params = [])
    {
        if (array_key_exists('token', $params)
            && count($params) > 1) {
            $flag = false;
            foreach ($this->assocContractor as $name => $value) {
                if (array_key_exists($value, $params) && $this->hasAttribute($name)) {
                    $flag = true;
                }
            }

            return $flag;
        } else {
            return false;
        }
    }

    /**
     * Delete Contractor object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataContractorById($params = [])
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // delete from Contractor table
            $countContractorDelete = $this->delete($this->id);

            if ($countContractorDelete > 0) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть удален');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть удален');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        $this->modelResponseMessage->saveSuccessMessage('Исполнитель успешно удален');
        return Json::encode($this->modelResponseMessage->getDataMessage());
    }

    /**
     * Delete Contractor objects into the Db by params
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataContractorByParams($params = [])
    {
        if (!$this->isOtherParams($params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Отсутствуют параметры для фильтра');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        // Search records by params in the database
        $query = Contractor::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataContractor = $query->orderBy('id')->all();
        // delete records from database
        return $this->deleteDataContractorArray($dataContractor);
    }

    /**
     * Delete Contractor objects into the Db by params
     *
     * @dataContractor array of objects
     *
     * @throws InvalidArgumentException if returned error
     */
    private function deleteDataContractorArray($dataContractor)
    {
        if (!empty($dataContractor)) {
            foreach ($dataContractor as $dataRec) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Contractor table.
                    $countContractorDelete = $dataRec->delete();

                    if ($countContractorDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть удален');
                        throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Исполнитель не может быть удален');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }

            $this->modelResponseMessage->saveSuccessMessage('Исполнитель успешно удален');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Исполнитель по параметрам');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}
