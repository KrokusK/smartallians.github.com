<?php
$config = [
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
//        'v2' => [
//            'class' => 'app\modules\v2\Module',
//        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie v$
            'cookieValidationKey' => 'b8OmoPe4fhFCNXD4GTJce5a9UKRfwwoA',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                  ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/country'],
//                  ['class' => 'yii\rest\UrlRule', 'controller' => ['v2/user', 'v2/post']],
//                'PUT,POST random' => 'random/create',
//                'DELETE random' => 'random/delete',
//                'GET random' => 'random/view',
//                'GET,POST entry' => 'random/entry',
//                'GET,POST companyapp' => 'companyapp/addapp',
//                'GET,POST companyapp/profile' => 'companyapp/addcompanyprofile',
//                'POST companyapp/profilevalidate' => 'companyapp/profilevalidate',
            ],
        ],
    ],
];


if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
