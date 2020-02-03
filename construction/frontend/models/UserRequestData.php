<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for processing request
 * User login by token in request params. Model
 * return messages of request processing status.
 */
class UserRequestData extends Model
{
    /**
     * properties
     */
    protected $method;
    protected $params;
    protected $modelResponseMessage;


    /**
     * Creates a model with user request params.
     *
     */
    public function __construct()
    {
        // Set properties: method, params
        $this->setProperties();
    }

    /**
     * Get params from request
     */
    public function getRequestParams()
    {
        return $this->params;
    }

    /**
     * Defining request method and
     * set params by values from request
     */
    public function setProperties()
    {
        $this->modelResponseMessage = new ResponseMessage();
        $this->method = strtolower(Yii::$app->getRequest()->getMethod());
        $this->setParamsByMethod();
    }

    /**
     * Set params from request
     *
     * @throws InvalidArgumentException if request params is empty
     */
    public function setParamsByMethod()
    {
        switch ($this->method) {
            case 'get':
                $this->params = Yii::$app->getRequest()->get();
                break;
            case 'post':
            case 'put':
            case 'patch':
            case 'delete':
                $this->params = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        }

        if (empty($this->params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Запрос не содержит параметров');
            throw new InvalidArgumentException(Json::encode($this->modelResponseMessage->getErrorMessage()));
        }
    }
}
