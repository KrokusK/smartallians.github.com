<?php
namespace console\rbac;

use yii\rbac\Rule;

class MediatorRule extends Rule
{
    public $name = 'isMediator';

    /**
     * @param string|int $user the user ID.
     * @param Item $item the role or permission that this rule is associated width.
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['recordMediator']) ? $params['recordMediator']->created_by == $user : false;
    }
}
