<?php
namespace api\modules\v2\rbac;

use yii\rbac\Rule;

class ContractorRule extends Rule
{
    public $name = 'isContractor';

    /**
     * @param string|int $user the user ID.
     * @param Item $item the role or permission that this rule is associated width.
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['recordContractor']) ? $params['recordContractor']->created_by == $user : false;
    }
}
