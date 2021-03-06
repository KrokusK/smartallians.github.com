<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "status_delivery".
 *
 */
class StatusDelivery extends \yii\db\ActiveRecord
{
    /**
    * Because the field names may match within a single query,
    * the parameter names may not match the table field names.
    * To solve this problem let's create an associative array
    */
    protected $assocStatusDelivery = [
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
        return '{{%status_delivery}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required', 'message' => 'Поле должно быть заполнено'],
            [
                ['id'],
                'match',
                'pattern' => '/^[0-9]*$/',
                'message' => 'поле id  должно быть типа integer',
                'skipOnEmpty' => true
            ],
            [['name'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
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
     * Link to table delivery
     */
    public function getDeliveries()
    {
        return $this->hasOne(Delivery::className(), ['status_delivery_id' => 'id']);
    }

    /**
     * Get StatusDelivery object properties by request params
     *
     * @params parameters for filtering
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataStatusDelivery($params = [])
    {
        // Search data
        $query = StatusDelivery::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataStatusDelivery = $query->orderBy('id')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataStatusDelivery)) {
            $this->modelResponseMessage->saveDataMessage(ArrayHelper::toArray($dataStatusDelivery));
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
        foreach ($this->assocStatusDelivery as $name => $value) {
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

        foreach ($this->assocStatusDelivery as $name => $value) {
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
     * Set StatusDelivery properties and
     * save object into the Db
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function addDataStatusDelivery($params = [])
    {
        // fill in the properties in the StatusDelivery object
        foreach ($this->assocStatusDelivery as $name => $value) {
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
     * Save StatusDelivery object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function saveDataObject()
    {
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagStatusDelivery = $this->save(false); // insert into StatusDelivery table

                if ($flagStatusDelivery == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть сохранен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть сохранен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSucessMessage('Статус поставки успешно сохранен');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Get StatusDelivery object by id
     *
     * @params parameters with properties
     * @notEmpty check on empty
     *
     * @throws InvalidArgumentException if returned error
     */
    public function getDataStatusDeliveryById($params = [])
    {
        if (array_key_exists($this->assocStatusDelivery['id'], $params)) {
            // check id parametr
            if (!preg_match("/^[0-9]*$/", $params[$this->assocStatusDelivery['id']])) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            // Search record by id in the database
            $queryStatusDelivery = StatusDelivery::find()->where(['id' => $params[$this->assocStatusDelivery['id']]]);
            $modelStatusDelivery = $queryStatusDelivery->one();
            if (empty($modelStatusDelivery)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Статус поставки по id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            return $modelStatusDelivery;
        } else {
            $this->modelResponseMessage->saveErrorMessage('Отсутствет id параметр в запросе');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Set StatusDelivery properties and
     * update object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function updateDataStatusDelivery($params = [])
    {
        // fill in the properties in the StatusDelivery object
        foreach ($this->assocStatusDelivery as $name => $value) {
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
     * Update StatusDelivery object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function updateDataObject()
    {
        // Update StatusDelivery object
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagStatusDelivery = $this->save(false); // update StatusDelivery table

                if ($flagStatusDelivery) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть обновлен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть обновлен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSucessMessage('Статус поставки успешно сохранен');
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
        if (array_key_exists($this->assocStatusDelivery['id'], $params)
            && !empty($params[$this->assocStatusDelivery['id']])) {
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
            foreach ($this->assocStatusDelivery as $name => $value) {
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
     * Delete StatusDelivery object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataStatusDeliveryById($params = [])
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // delete from StatusDelivery table
            $countStatusDeliveryDelete = $this->delete($this->id);

            if ($countStatusDeliveryDelete > 0) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть удален');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть удален');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        $this->modelResponseMessage->saveSucessMessage('Статус поставки успешно удален');
        return Json::encode($this->modelResponseMessage->getDataMessage());
    }

    /**
     * Delete StatusDelivery objects into the Db by params
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataStatusDeliveryByParams($params = [])
    {
        if (!$this->isOtherParams($params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Отсутствуют параметры для фильтра');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        // Search records by params in the database
        $query = StatusDelivery::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataStatusDelivery = $query->orderBy('id')->all();
        // delete records from database
        return $this->deleteDataStatusDeliveryArray($dataStatusDelivery);
    }

    /**
     * Delete StatusDelivery objects into the Db by params
     *
     * @dataStatusDelivery array of objects
     *
     * @throws InvalidArgumentException if returned error
     */
    private function deleteDataStatusDeliveryArray($dataStatusDelivery)
    {
        if (!empty($dataStatusDelivery)) {
            foreach ($dataStatusDelivery as $dataRec) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from StatusDelivery table.
                    $countStatusDeliveryDelete = $dataRec->delete();

                    if ($countStatusDeliveryDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть удален');
                        throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус поставки не может быть удален');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }

            $this->modelResponseMessage->saveSucessMessage('Статус поставки успешно удален');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Статус поставки по параметрам');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}
