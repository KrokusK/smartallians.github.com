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
                'OPTIONS /<module:\w+>/<controller:\w*>' => '<module>/site/options',
                'OPTIONS /<module:\w+>/<controller:\w+>/<action:\w*>' => '<module>/site/options',

                // API photo
                // Not working: 'PUT,PATCH /<module:\w+>/<controller:(photo)>>' => '<module>/<controller>/update',
                'POST /<module:\w+>/photo-update' => '<module>/photo/update',

                // API RBAC
                'POST /<module:\w+>/<controller:(rbac)>/<action>' => '<module>/rbac-actions/<action>',

                // API modules
                'GET /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/view',
                'POST /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery|profile-avatar|profile-passport|project-file)>' => '<module>/<controller>/create',
                'PUT,PATCH /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/update',
                'DELETE /<module:\w+>/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<module>/<controller>/delete',
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
