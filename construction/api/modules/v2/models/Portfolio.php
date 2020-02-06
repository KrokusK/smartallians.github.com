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
class Portfolio extends \yii\db\ActiveRecord
{
    /**
     * Because the field names may match within a single query,
     * the parameter names may not match the table field names.
     * To solve this problem let's create an associative array
     */
    protected $assocPortfolio = [
        'id' => 'id',
        'contractor_id' => 'contractor_id',
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
        return '{{%contractor}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [
                ['contractor_id', 'name'], 
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
                ['contractor_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesContractorId = Contractor::find()->select(['id'])->asArray()->all();
                    $statusesContractorIdStr = [];
                    foreach ($statusesContractorId as $item) {
                        array_push($statusesContractorIdStr, "{$item['id']}");
                    }
                    return $statusesContractorIdStr;
                },
                'message' => 'Портфолио не выбран из списка'
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
     * Link to table contractor
     */
    public function getContractors()
    {
        return $this->hasOne(Contractor::className(), ['id' => 'contractor_id']);
    }

    /**
     *
     * Link to table position
     */
    public function getPositions()
    {
        return $this->hasMany(Position::className(), ['portfolio_id' => 'id']);
    }

    /**
     * Get Portfolio object properties by request params
     *
     * @params parameters for filtering
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataPortfolio($params = [])
    {
        // Search data
        $query = Portfolio::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataPortfolio = $query->orderBy('id')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataPortfolio)) {
            $this->modelResponseMessage->saveArrayMessage(ArrayHelper::toArray($dataPortfolio));
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
        foreach ($this->assocPortfolio as $name => $value) {
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

        foreach ($this->assocPortfolio as $name => $value) {
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
     * Set Portfolio properties and
     * save object into the Db
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function addDataPortfolio($params = [])
    {
        // fill in the properties in the Portfolio object
        foreach ($this->assocPortfolio as $name => $value) {
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
     * Save Portfolio object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function saveDataObject()
    {
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagPortfolio = $this->save(false); // insert into Portfolio table

                if ($flagPortfolio == true) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть сохранено');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть сохранено');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Портфолио успешно сохранено');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка валидации');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Get Portfolio object by id
     *
     * @params parameters with properties
     * @notEmpty check on empty
     *
     * @throws InvalidArgumentException if returned error
     */
    public function getDataPortfolioById($params = [])
    {
        if (array_key_exists($this->assocPortfolio['id'], $params)) {
            // check id parametr
            if (!preg_match("/^[0-9]*$/", $params[$this->assocPortfolio['id']])) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            // Search record by id in the database
            $queryPortfolio = Portfolio::find()->where(['id' => $params[$this->assocPortfolio['id']]]);
            $modelPortfolio = $queryPortfolio->one();
            if (empty($modelPortfolio)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найдено Портфолио по id');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            return $modelPortfolio;
        } else {
            $this->modelResponseMessage->saveErrorMessage('Отсутствет id параметр в запросе');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }

    /**
     * Set Portfolio properties and
     * update object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function updateDataPortfolio($params = [])
    {
        // fill in the properties in the Portfolio object
        foreach ($this->assocPortfolio as $name => $value) {
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
     * Update Portfolio object
     *
     * @throws InvalidArgumentException if returned error
     */
    private function updateDataObject()
    {
        // Update Portfolio object
        if ($this->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $flagPortfolio = $this->save(false); // update Portfolio table

                if ($flagPortfolio) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть обновлено');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            } catch (Exception $ex) {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть обновлено');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }

            $this->modelResponseMessage->saveSuccessMessage('Портфолио успешно сохранено');
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
        if (array_key_exists($this->assocPortfolio['id'], $params)
            && !empty($params[$this->assocPortfolio['id']])) {
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
            foreach ($this->assocPortfolio as $name => $value) {
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
     * Delete Portfolio object into the Db by id
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataPortfolioById($params = [])
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // delete from Portfolio table
            $countPortfolioDelete = $this->delete($this->id);

            if ($countPortfolioDelete > 0) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
                $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть удалено');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть удалено');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        $this->modelResponseMessage->saveSuccessMessage('Портфолио успешно удалено');
        return Json::encode($this->modelResponseMessage->getDataMessage());
    }

    /**
     * Delete Portfolio objects into the Db by params
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function deleteDataPortfolioByParams($params = [])
    {
        if (!$this->isOtherParams($params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Отсутствуют параметры для фильтра');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        // Search records by params in the database
        $query = Portfolio::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataPortfolio = $query->orderBy('id')->all();
        // delete records from database
        return $this->deleteDataPortfolioArray($dataPortfolio);
    }

    /**
     * Delete Portfolio objects into the Db by params
     *
     * @dataPortfolio array of objects
     *
     * @throws InvalidArgumentException if returned error
     */
    private function deleteDataPortfolioArray($dataPortfolio)
    {
        if (!empty($dataPortfolio)) {
            foreach ($dataPortfolio as $dataRec) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // delete from Portfolio table.
                    $countPortfolioDelete = $dataRec->delete();

                    if ($countPortfolioDelete > 0) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть удалено');
                        throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    $this->modelResponseMessage->saveErrorMessage('Ошибка: Портфолио не может быть удалено');
                    throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
                }
            }

            $this->modelResponseMessage->saveSuccessMessage('Портфолио успешно удалено');
            return Json::encode($this->modelResponseMessage->getDataMessage());
        } else {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найдено Портфолио по параметрам');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}
