<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "profile_specialization".
 *
 */
class ProfileSpecialization extends \yii\db\ActiveRecord
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
        return '{{%profile_specialization}}';
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
                ['specialization_id'],
                'in',
                'range' =>
                function ( $attribute, $params ) {
                    $SpecializationId = Specialization::find()->select(['id'])->asArray()->all();
                    $SpecializationIdStr = [];
                    foreach ($SpecializationId as $item) {
                        array_push($SpecializationIdStr, "{$item['id']}");
                    }
                    return $SpecializationIdStr;
                },
                'message' => 'Специализация не выбрана из списка'
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
     * Set ProfileSpecialization properties and
     * save object into the Db
     *
     * @params parameters with properties
     *
     * @throws InvalidArgumentException if returned error
     */
    public function addDataProfileSpecialization($params = [], $modelProfile)
    {
        // specialization objects array
        $arrayModelSpecialization = [];

        // check parametr for the Specialization object
        if (!is_array($params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: В параметре specializations ожидается массив');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }

        // push specialization objects into array
        foreach ($params as $key => $value) {
            // check parametr for the Specialization object
            if (!preg_match("/^[0-9]*$/", $value)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка валидации: specializations');
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
            // search cities and add to array
            $querySpecialization = Specialization::find()->where(['id' => $value]);
            $modelSpecialization = $querySpecialization->one();
            if (empty($modelSpecialization)) {
                $this->modelResponseMessage->saveErrorMessage('Ошибка: В БД не найден Город по id = ' . $value);
                throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
            }
            array_push($arrayModelSpecialization, $modelSpecialization);
        }

        // Save objects by links
        foreach ($arrayModelSpecialization as $model) {
            $modelProfile->link('specializations', $model);
        }
    }
}
