<?php
namespace api\modules\v2\models;

use api\modules\v2\models\ResponseMessage;
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
     * Constants
     */

    const CHECK_RIGHTS_RBAC = false;  // Enable check rights by rbac model

    /**
     * properties
     */
    protected $method;
    protected $userByToken;
    protected $userRole;
    protected $params;
    protected $modelResponseMessage;

    //private $_user;


    /**
     * Creates a form model with given token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    /*
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }
        $this->_user = User::findByVerificationToken($token);
        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }
        parent::__construct($config);
    }
    */

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
                $this->params = Yii::$app->getRequest()->post();
                break;
            case 'put':
            case 'patch':
            case 'delete':
                $this->params = json_decode(Yii::$app->getRequest()->getRawBody(), true);
        }

        if (empty($this->params)) {
            $this->modelResponseMessage->saveErrorMessage('Ошибка: Запрос не содержит параметров');
            throw new InvalidArgumentException($this->modelResponseMessage->getErrorMessage());
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
     * Get user roles
     */
    public function getUserRoles()
    {
        if (!empty($this->userByToken)) {
            $this->userRole = [];
            $userAssigned = Yii::$app->authManager->getAssignments($this->userByToken->id);
            foreach($userAssigned as $userAssign) {
                array_push($this->userRole, $userAssign->roleName);
            }

            return $this->userRole;
        }

        return null;
    }

    /**
     * Check user rights
     */
    public function checkUserRightsByRole($rights = [])
    {
        $flagRights = false;
        foreach($rights as $value) {
            if (in_array($value, $this->userRole)) {
                $flagRights = true;
            }
        }
        if (static::CHECK_RIGHTS_RBAC) return $flagRights;
        else return true;
    }
}
