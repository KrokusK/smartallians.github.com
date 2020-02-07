<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "profile_city".
 *
 */
class ProfileCity extends \yii\db\ActiveRecord
{
    /**
     * properties
     */
    protected $modelResponseMessage;

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
            [
                ['profile_id'],
                'in',
                'range' =>
                function ( $attribute, $params ) {
                    $ProfileId = Profile::find()->select(['id'])->asArray()->all();
                    $ProfileIdStr = [];
                    foreach ($ProfileId as $item) {
                        array_push($ProfileIdStr, "{$item['id']}");
                    }
                    return $ProfileIdStr;
                },
                'message' => 'Вид работы не выбран из списка'
            ],
            [
                ['city_id'],
                'in',
                'range' =>
                function ( $attribute, $params ) {
                    $CityId = City::find()->select(['id'])->asArray()->all();
                    $CityIdStr = [];
                    foreach ($CityId as $item) {
                        array_push($CityIdStr, "{$item['id']}");
                    }
                    return $CityIdStr;
                },
                'message' => 'Город не выбран из списка'
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
     * Set ProfileCity properties and
     * save object into the Db
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function addDataProfileCity($params = [], $modelProfile)
    {
        // city objects array
        $arrayModelCity = [];

        // check parametr for the City object
        if (!is_array($params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: В параметре cities ожидается массив');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        // push city objects into array
        foreach ($params as $key => $value) {
            // check parametr for the City object
            if (!preg_match("/^[0-9]*$/", $value)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: cities');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
            // search cities and add to array
            $queryCity = City::find()->where(['id' => $value]);
            $modelCity = $queryCity->one();
            if (empty($modelCity)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Город по id = ' . $value);
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
            array_push($arrayModelCity, $modelCity);
        }

        // Save objects by links
        foreach ($arrayModelCity as $model) {
            $modelProfile->link('cities', $model);
        }
    }
}
