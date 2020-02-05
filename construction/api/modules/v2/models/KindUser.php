<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "kind_user".
 *
 */
class KindUser extends \yii\db\ActiveRecord
{
    /**
     * Because the field names may match within a single query,
     * the parameter names may not match the table field names.
     * To solve this problem let's create an associative array
     */
    protected $assocKindUser = [
        'id' => 'id',
        'name' => 'name',
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
        return '{{%kind_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['name'],
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
                ['name'],
                'string',
                'max' => 255,
                'message' => 'Число знаков не должно превышать 255'
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
        return $this->hasOne(Profile::className(), ['kind_user_id' => 'id']);
    }

    /**
     * Get KindUser object properties by request params
     *
     * @params parameters for filtering
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataKindUser($params = [])
    {
        // Search data
        $query = KindUser::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataKindUser = $query->orderBy('name')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataKindUser)) {
            $this->modelResponseMessage->saveArrayMessage(ArrayHelper::toArray($dataKindUser));
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
        foreach ($this->assocKindUser as $name => $value) {
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

        foreach ($this->assocKindUser as $name => $value) {
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

    /**
     * Set KindUser properties and
     * save object into the Db
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function addDataKindUser($params = [])
    {
        // fill in the properties in the KindUser object
        foreach ($this->assocKindUser as $name => $value) {
            if (array_key_exists($value, $params) && $this->hasAttribute($name) && $name != 'id') {
                $this->$name = $params[$value];
                if (!$this->validate($name)) {
                    $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: параметр ' . $value);
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }
        }

        return $this->saveDataObject();
    }

    /**
     * Save KindUser object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function saveDataObject()
    {
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagKindUser = $this->save(false); // insert into KindUser table

                if ($flagKindUser == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть сохранен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть сохранен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Вид пользователя успешно сохранен');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Get KindUser object by id
     *
     * @params parameters with properties
     * @notEmpty check on empty
     *
     * @throws InvalidArgumentException if returned error
     */
    public function getDataKindUserById($params = [])
    {
        if (array_key_exists($this->assocKindUser['id'], $params)) {
            // check id parametr
            if (!preg_match("/^[0-9]*$/", $params[$this->assocKindUser['id']])) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            // Search record by id in the database
            $queryKindUser = KindUser::find()->where(['id' => $params[$this->assocKindUser['id']]]);
            $modelKindUser = $queryKindUser->one();
            if (empty($modelKindUser)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Вид пользователя по id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            return $modelKindUser;
        } else {
            $this->modelResponseMessage->saveErrorMessage('Отсутствет id параметр в запросе');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Set KindUser properties and
     * update object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function updateDataKindUser($params = [])
    {
        // fill in the properties in the KindUser object
        foreach ($this->assocKindUser as $name => $value) {
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
     * Update KindUser object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function updateDataObject()
    {
        // Update KindUser object
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagKindUser = $this->save(false); // update KindUser table

                if ($flagKindUser) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть обновлен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть обновлен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Вид пользователя успешно сохранен');
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
        if (array_key_exists($this->assocKindUser['id'], $params)
            && !empty($params[$this->assocKindUser['id']])) {
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
        if (array_key_exists('token',$params)
            && count($params) > 1) {
            $flag = false;
            foreach ($this->assocKindUser as $name => $value) {
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
     * Delete KindUser object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataKindUserById($params = [])
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // delete from KindUser table
            $countKindUserDelete = $this->delete($this->id);

            if ($countKindUserDelete > 0) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть удален');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть удален');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        $this->modelResponseMessage->saveSuccessMessage('Вид пользователя успешно удален');
        return Json::encode($this->modelResponseMessage->getDataMessage());
    }

    /**
     * Delete KindUser objects into the Db by params
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataKindUserByParams($params = [])
    {
        if (!$this->isOtherParams($params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Отсутствуют параметры для фильтра');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        // Search records by params in the database
        $query = KindUser::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataKindUser = $query->orderBy('id')->all();
        // delete records from database
        return $this->deleteDataKindUserArray($dataKindUser);
    }

    /**
     * Delete KindUser objects into the Db by params
     *
     * @dataKindUser array of objects
     *
     * @throws InvalidArgumentException if returned error
     */
    private function deleteDataKindUserArray($dataKindUser)
    {
        if (!empty($dataKindUser)) {
            foreach ($dataKindUser as $dataRec) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from KindUser table.
                    $countKindUserDelete = $dataRec->delete();

                    if ($countKindUserDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть удален');
                        throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Вид пользователя не может быть удален');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }

            $this->modelResponseMessage->saveSuccessMessage('Вид пользователя успешно удален');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Вид пользователя по параметрам');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}
