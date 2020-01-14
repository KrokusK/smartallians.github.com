<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Customer
        // add the rule
        $ruleCustomer = new \console\rbac\CustomerRule;
        $auth->add($ruleCustomer);

        // add permission "viewCustomer"
        $viewCustomer = $auth->createPermission('viewCustomer');
        $viewCustomer->description = 'Get records from the Customer tables';
        $auth->add($viewCustomer);

        // add permission "createCustomer"
        $createCustomer = $auth->createPermission('createCustomer');
        $createCustomer->description = 'Insert records into the Customer tables';
        $auth->add($createCustomer);

        // add permission "updateCustomer"
        $updateCustomer = $auth->createPermission('updateCustomer');
        $updateCustomer->description = 'Update records into the Customer tables';
        $auth->add($updateCustomer);

        // add permission "deleteCustomer"
        $deleteCustomer = $auth->createPermission('deleteCustomer');
        $deleteCustomer->description = 'Delete records into the Customer tables';
        $auth->add($deleteCustomer);

        // add permission "viewOwnCustomer" and link him with a rule.
        $viewOwnCustomer = $auth->createPermission('viewOwnCustomer');
        $viewOwnCustomer->description = 'Get own records from the Customer tables';
        $viewOwnCustomer->ruleName = $ruleCustomer->name;
        $auth->add($viewOwnCustomer);

        // "viewOwnCustomer" will be used from "viewCustomer"
        $auth->addChild($viewOwnCustomer, $viewCustomer);


        // Contractor
        // add the rule
        $ruleContractor = new \console\rbac\ContractorRule;
        $auth->add($ruleContractor);

        // add permission "viewContractor"
        $viewContractor = $auth->createPermission('viewContractor');
        $viewContractor->description = 'Get records from the Contractor tables';
        $auth->add($viewContractor);

        // add permission "createContractor"
        $createContractor = $auth->createPermission('createContractor');
        $createContractor->description = 'Insert records into the Contractor tables';
        $auth->add($createContractor);

        // add permission "updateContractor"
        $updateContractor = $auth->createPermission('updateContractor');
        $updateContractor->description = 'Update records into the Contractor tables';
        $auth->add($updateContractor);

        // add permission "deleteContractor"
        $deleteContractor = $auth->createPermission('deleteContractor');
        $deleteContractor->description = 'Delete records into the Contractor tables';
        $auth->add($deleteContractor);

        // add permission "viewOwnContractor" and link him with a rule.
        $viewOwnContractor = $auth->createPermission('viewOwnContractor');
        $viewOwnContractor->description = 'Get own records from the Contractor tables';
        $viewOwnContractor->ruleName = $ruleContractor->name;
        $auth->add($viewOwnContractor);

        // "viewOwnContractor" will be used from "viewContractor"
        $auth->addChild($viewOwnContractor, $viewContractor);


        // Mediator
        // add the rule
        $ruleMediator = new \console\rbac\MediatorRule;
        $auth->add($ruleMediator);

        // add permission "viewMediator"
        $viewMediator = $auth->createPermission('viewMediator');
        $viewMediator->description = 'Get records from the Mediator tables';
        $auth->add($viewMediator);

        // add permission "createMediator"
        $createMediator = $auth->createPermission('createMediator');
        $createMediator->description = 'Insert records into the Mediator tables';
        $auth->add($createMediator);

        // add permission "updateMediator"
        $updateMediator = $auth->createPermission('updateMediator');
        $updateMediator->description = 'Update records into the Mediator tables';
        $auth->add($updateMediator);

        // add permission "deleteMediator"
        $deleteMediator = $auth->createPermission('deleteMediator');
        $deleteMediator->description = 'Delete records into the Mediator tables';
        $auth->add($deleteMediator);

        // add permission "viewOwnMediator" and link him with a rule.
        $viewOwnMediator = $auth->createPermission('viewOwnMediator');
        $viewOwnMediator->description = 'Get own records from the Mediator tables';
        $viewOwnMediator->ruleName = $ruleMediator->name;
        $auth->add($viewOwnMediator);

        // "viewOwnMediator" will be used from "viewMediator"
        $auth->addChild($viewOwnMediator, $viewMediator);


        // Provider
        // add the rule
        $ruleProvider = new \console\rbac\ProviderRule;
        $auth->add($ruleProvider);

        // add permission "viewProvider"
        $viewProvider = $auth->createPermission('viewProvider');
        $viewProvider->description = 'Get records from the Provider tables';
        $auth->add($viewProvider);

        // add permission "createProvider"
        $createProvider = $auth->createPermission('createProvider');
        $createProvider->description = 'Insert records into the Provider tables';
        $auth->add($createProvider);

        // add permission "updateProvider"
        $updateProvider = $auth->createPermission('updateProvider');
        $updateProvider->description = 'Update records into the Provider tables';
        $auth->add($updateProvider);

        // add permission "deleteProvider"
        $deleteProvider = $auth->createPermission('deleteProvider');
        $deleteProvider->description = 'Delete records into the Provider tables';
        $auth->add($deleteProvider);

        // add permission "viewOwnProvider" and link him with a rule.
        $viewOwnProvider = $auth->createPermission('viewOwnProvider');
        $viewOwnProvider->description = 'Get own records from the Provider tables';
        $viewOwnProvider->ruleName = $ruleProvider->name;
        $auth->add($viewOwnProvider);

        // "viewOwnProvider" will be used from "viewProvider"
        $auth->addChild($viewOwnProvider, $viewProvider);


        // Roles
        // add role "Customer" and add permission to the role
        $customer = $auth->createRole('customer');
        $auth->add($customer);
        $auth->addChild($customer, $createCustomer);
        $auth->addChild($customer, $viewOwnCustomer);
        $auth->addChild($customer, $createOwnCustomer);
        $auth->addChild($customer, $updateOwnCustomer);
        $auth->addChild($customer, $deleteOwnCustomer);

        // add role "Contractor" and add permission to the role
        $contractor = $auth->createRole('contractor');
        $auth->add($contractor);
        $auth->addChild($contractor, $createContractor);
        $auth->addChild($contractor, $viewOwnContractor);
        $auth->addChild($contractor, $createOwnContractor);
        $auth->addChild($contractor, $updateOwnContractor);
        $auth->addChild($contractor, $deleteOwnContractor);

        // add role "Mediator" and add permission to the role
        $mediator = $auth->createRole('mediator');
        $auth->add($mediator);
        $auth->addChild($mediator, $createMediator);
        $auth->addChild($mediator, $viewOwnMediator);
        $auth->addChild($mediator, $createOwnMediator);
        $auth->addChild($mediator, $updateOwnMediator);
        $auth->addChild($mediator, $deleteOwnMediator);

        // add role "Provider" and add permission to the role
        $provider = $auth->createRole('provider');
        $auth->add($provider);
        $auth->addChild($provider, $createProvider);
        $auth->addChild($provider, $viewOwnProvider);
        $auth->addChild($provider, $createOwnProvider);
        $auth->addChild($provider, $updateOwnProvider);
        $auth->addChild($provider, $deleteOwnProvider);

        // add role "Admin" and add permission to the role
        // and also add all permissions from another roles
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $viewCustomer);
        $auth->addChild($admin, $createCustomer);
        $auth->addChild($admin, $updateCustomer);
        $auth->addChild($admin, $deleteCustomer);
        $auth->addChild($admin, $customer);
        $auth->addChild($admin, $viewContractor);
        $auth->addChild($admin, $createContractor);
        $auth->addChild($admin, $updateContractor);
        $auth->addChild($admin, $deleteContractor);
        $auth->addChild($admin, $contractor);
        $auth->addChild($admin, $viewMediator);
        $auth->addChild($admin, $createMediator);
        $auth->addChild($admin, $updateMediator);
        $auth->addChild($admin, $deleteMediator);
        $auth->addChild($admin, $mediator);
        $auth->addChild($admin, $viewProvider);
        $auth->addChild($admin, $createProvider);
        $auth->addChild($admin, $updateProvider);
        $auth->addChild($admin, $deleteProvider);
        $auth->addChild($admin, $provider);

        // Назначение ролей пользователям. 1 и 2 это IDs возвращаемые IdentityInterface::getId()
        // обычно реализуемый в модели User.
        //$auth->assign($author, 2);
        //$auth->assign($admin, 1);
    }
}
