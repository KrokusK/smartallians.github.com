<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "status_material".
 *
 */
class StatusMaterial extends \yii\db\ActiveRecord
{
    /**
     * Because the field names may match within a single query,
     * the parameter names may not match the table field names.
     * To solve this problem let's create an associative array
     */
    protected $assocStatusMaterial = [
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
        return '{{%status_material}}';
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
     * Link to table material
     */
    public function getMaterials()
    {
        return $this->hasOne(Material::className(), ['status_material_id' => 'id']);
    }

    /**
     * Get StatusMaterial object properties by request params
     *
     * @params parameters for filtering
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataStatusMaterial($params = [])
    {
        // Search data
        $query = StatusMaterial::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataStatusMaterial = $query->orderBy('name')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataStatusMaterial)) {
            $this->modelResponseMessage->saveArrayMessage(ArrayHelper::toArray($dataStatusMaterial));
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
        foreach ($this->assocStatusMaterial as $name => $value) {
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

        foreach ($this->assocStatusMaterial as $name => $value) {
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
     * Set StatusMaterial properties and
     * save object into the Db
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function addDataStatusMaterial($params = [])
    {
        // fill in the properties in the StatusMaterial object
        foreach ($this->assocStatusMaterial as $name => $value) {
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
     * Save StatusMaterial object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function saveDataObject()
    {
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagStatusMaterial = $this->save(false); // insert into StatusMaterial table

                if ($flagStatusMaterial == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть сохранен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть сохранен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Статус материала успешно сохранен');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Get StatusMaterial object by id
     *
     * @params parameters with properties
     * @notEmpty check on empty
     *
     * @throws InvalidArgumentException if returned error
     */
    public function getDataStatusMaterialById($params = [])
    {
        if (array_key_exists($this->assocStatusMaterial['id'], $params)) {
            // check id parametr
            if (!preg_match("/^[0-9]*$/", $params[$this->assocStatusMaterial['id']])) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            // Search record by id in the database
            $queryStatusMaterial = StatusMaterial::find()->where(['id' => $params[$this->assocStatusMaterial['id']]]);
            $modelStatusMaterial = $queryStatusMaterial->one();
            if (empty($modelStatusMaterial)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Статус материала по id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            return $modelStatusMaterial;
        } else {
            $this->modelResponseMessage->saveErrorMessage('Отсутствет id параметр в запросе');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Set StatusMaterial properties and
     * update object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function updateDataStatusMaterial($params = [])
    {
        // fill in the properties in the StatusMaterial object
        foreach ($this->assocStatusMaterial as $name => $value) {
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
     * Update StatusMaterial object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function updateDataObject()
    {
        // Update StatusMaterial object
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagStatusMaterial = $this->save(false); // update StatusMaterial table

                if ($flagStatusMaterial) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть обновлен');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть обновлен');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Статус материала успешно сохранен');
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
        if (array_key_exists($this->assocStatusMaterial['id'], $params)
            && !empty($params[$this->assocStatusMaterial['id']])) {
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
            foreach ($this->assocStatusMaterial as $name => $value) {
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
     * Delete StatusMaterial object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataStatusMaterialById($params = [])
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // delete from StatusMaterial table
            $countStatusMaterialDelete = $this->delete($this->id);

            if ($countStatusMaterialDelete > 0) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть удален');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть удален');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        $this->modelResponseMessage->saveSuccessMessage('Статус материала успешно удален');
        return Json::encode($this->modelResponseMessage->getDataMessage());
    }

    /**
     * Delete StatusMaterial objects into the Db by params
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataStatusMaterialByParams($params = [])
    {
        if (!$this->isOtherParams($params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Отсутствуют параметры для фильтра');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        // Search records by params in the database
        $query = StatusMaterial::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataStatusMaterial = $query->orderBy('id')->all();
        // delete records from database
        return $this->deleteDataStatusMaterialArray($dataStatusMaterial);
    }

    /**
     * Delete StatusMaterial objects into the Db by params
     *
     * @dataStatusMaterial array of objects
     *
     * @throws InvalidArgumentException if returned error
     */
    private function deleteDataStatusMaterialArray($dataStatusMaterial)
    {
        if (!empty($dataStatusMaterial)) {
            foreach ($dataStatusMaterial as $dataRec) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from StatusMaterial table.
                    $countStatusMaterialDelete = $dataRec->delete();

                    if ($countStatusMaterialDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть удален');
                        throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Статус материала не может быть удален');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }

            $this->modelResponseMessage->saveSuccessMessage('Статус материала успешно удален');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Статус материала по параметрам');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}
