<?php
namespace api\modules\v2\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;

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
    protected $message;
    protected $userByToken;
    protected $params;

    /**
     * init
     */

    public function init()
    {
        parent::init();

        // Set properties: method, params
        $this->setProperties();
    }

    /**
     * Get params from request
     *
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
        $this->method = strtolower(Yii::$app->getRequest()->getMethod());
        $this->setParamsByMethod();
    }

    /**
     * Set params from request
     *
     */

    public function setParamsByMethod()
    {
        switch ($this->method) {
            case 'get':
                $this->params = Yii::$app->getRequest()->get();
                break;
            case 'post':
                $this->params = Yii::$app->getRequest()->post();
                break;
            case 'put':
            case 'patch':
            case 'delete':
                $this->params = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        }
    }

    /**
     * Authorization user by token in params
     */
    public function loginByParams()
    {
        if (array_key_exists('token', $this->params)) {
            $this->userByToken = \Yii::$app->user->loginByAccessToken($this->params['token']);
            if (!empty($this->userByToken)) {
                return $this->userByToken;
            }
        }

        return null;
    }

    /**
     * Set text message
     */
    public function setMessage($status = 1, $message = '') {
        switch ($status) {
            case 0:
                $this->message = [
                    'method' => $this->method,
                    'status' => $status,
                    'type' => 'success',
                    'message' => $message
                ];
                break;
            case 1:
                $this->message = [
                    'method' => $this->method,
                    'status' => $status,
                    'type' => 'error',
                    'message' => $message
                ];
        }
    }

    /**
     * Get text message
     *
     */

    public function getMessage()
    {
        return $this->message;
    }
}
