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
