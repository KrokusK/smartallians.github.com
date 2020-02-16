<?php
$config = [
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
                // Route OPTIONS request
                'OPTIONS /<controller:\w*>' => 'site/options',
                'OPTIONS /<controller:\w+>/<action:\w*>' => 'site/options',

                // API modules
                'GET /api/<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/view',
                'POST /api/<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/create',
                'PUT,PATCH /api/<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/update',
                'DELETE /api/<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/delete',

                // API old routing for tables
                //'GET /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/view',
                //'POST /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/create',
                //'PUT,PATCH /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/update',
                //'DELETE /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/delete',
                'GET /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/view',
                'POST /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/create',
                'PUT,PATCH /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/update',
                'DELETE /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/delete',

                // API user data
                'POST /api/<module:\w+>/user' => '/user/data',
                'POST /user' => '/user/data',

                // API login
                'POST /site/<action:\w+>' => 'site/<action>',

                // default routing
                '/<controller:\w*>' => 'site/index',
                '/<controller:\w+>/<action:\w*>' => 'site/index',
                '/<module:\w+>/<controller:\w*>' => 'site/index',
                '/<module:\w+>/<controller:\w+>/<action:\w*>' => 'site/index',
            ],
        ],
    ],
];


if (!YII_ENV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['localhost','127.0.0.1','*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}


return $config;
