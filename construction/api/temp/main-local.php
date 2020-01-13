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

                // API test
                'PUT,PATCH /<module:\w+>/photo-update' => '<module>/photo/update',

                // API modules
                'GET /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/view',
                'POST /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/create',
                'PUT,PATCH /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/update',
                'DELETE /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/delete',

                // default routing
                '/<controller:\w*>' => 'site/index',
                '/<controller:\w+>/<action:\w*>' => 'site/index',
                '/<module:\w+>/<controller:\w*>' => 'site/index',
                '/<module:\w+>/<controller:\w+>/<action:\w*>' => 'site/index',
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
