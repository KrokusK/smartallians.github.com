<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "region".
 *
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * Because the field names may match within a single query,
     * the parameter names may not match the table field names.
     * To solve this problem let's create an associative array
     */
    protected $assocRegion = [
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
        return '{{%region}}';
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
     * Link to table city
     */
    public function getCities()
    {
        return $this->hasMany(City::className(), ['region_id' => 'id']);
    }

    /**
     * Get Region object properties by request params
     *
     * @params parameters for filtering
     *
     * @throws InvalidArgumentException if data not found or parameters is not validated
     */
    public function getDataRegion($params = [])
    {
        // Search data
        $query = Region::find();
        // Add data filter
        $this->setDataFilter($query, $params);
        // Add pagination params
        $this->setPaginationParams($query, $params);
        // get data
        $dataRegion = $query->orderBy('name')
            ->asArray()
            ->all();

        // return data
        if (!empty($dataRegion)) {
            $this->modelResponseMessage->saveDataMessage(ArrayHelper::toArray($dataRegion));
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
        foreach ($this->assocRegion as $name => $value) {
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

        foreach ($this->assocRegion as $name => $value) {
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
