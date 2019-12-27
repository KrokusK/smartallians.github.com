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
                'OPTIONS /<controller:\w+>' => 'site/options',
                'OPTIONS /<controller:\w+>/<action:\w+>' => 'site/options',

                // API routing for tables
                'GET /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/view',
                'POST /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/create',
                'PUT,PATCH /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/update',
                'DELETE /api/<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/delete',
                //'POST /api/<controller:\w+>' => '<controller>/view',
                //'POST /api/<controller:\w+>' => '<controller>/create',
                //'PUT,PATCH /api/<controller:\w+>' => '<controller>/update',
                //'DELETE /api/<controller:\w+>' => '<controller>/delete',

                // API old routing for tables
                'GET /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/view',
                'POST /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/create',
                'PUT,PATCH /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/update',
                'DELETE /<controller:(region|city|specialization|profile|type-job|kind-user|contractor|attestation|portfolio|position|kind-job|request|response|status-request|status-response|photo|status-feedback|feedback|status-completion|status-payment|order|project|project-documents|job-stages|materials|material-type|status-material|delivery|delivery-place|departure-place|status-delivery)>' => '<controller>/delete',
                //'GET /<controller:\w+>' => '<controller>/view',
                //'POST /<controller:\w+>' => '<controller>/create',
                //'PUT,PATCH /<controller:\w+>' => '<controller>/update',
                //'DELETE /<controller:\w+>' => '<controller>/delete',

                // API data user
                'POST user' => 'user/data',
                'POST /api/user' => 'user/data',

                /*
                // API region
                //'GET region' => 'region/view',
                'POST region' => 'region/create',
                'PUT,PATCH region' => 'region/update',
                'DELETE region' => 'region/delete',

                // API city
                //'GET city' => 'city/view',
                'POST city' => 'city/create',
                'PUT,PATCH city' => 'city/update',
                'DELETE city' => 'city/delete',

                // API specialization
                //'GET specialization' => 'specialization/view',
                'POST specialization' => 'specialization/create',
                'PUT,PATCH specialization' => 'specialization/update',
                'DELETE specialization' => 'specialization/delete',

                // API profile
                //'GET profile' => 'profile/view',
                'POST profile' => 'profile/create',
                'PUT,PATCH profile' => 'profile/update',
                'DELETE profile' => 'profile/delete',

                // API type-job
                //'GET type-job' => 'type-job/view',
                'POST type-job' => 'type-job/create',
                'PUT,PATCH type-job' => 'type-job/update',
                'DELETE type-job' => 'type-job/delete',

                // API kind-user
                //'GET kind-user' => 'kind-user/view',
                'POST kind-user' => 'kind-user/create',
                'PUT,PATCH kind-user' => 'kind-user/update',
                'DELETE kind-user' => 'kind-user/delete',

                // API contractor
                //'GET contractor' => 'contractor/view',
                'POST contractor' => 'contractor/create',
                'PUT,PATCH contractor' => 'contractor/update',
                'DELETE contractor' => 'contractor/delete',

                // API attestation
                //'GET attestation' => 'attestation/view',
                'POST attestation' => 'attestation/create',
                'PUT,PATCH attestation' => 'attestation/update',
                'DELETE attestation' => 'attestation/delete',

                // API portfolio
                //'GET portfolio' => 'portfolio/view',
                'POST portfolio' => 'portfolio/create',
                'PUT,PATCH portfolio' => 'portfolio/update',
                'DELETE portfolio' => 'portfolio/delete',

                // API position
                //'GET position' => 'position/view',
                'POST position' => 'position/create',
                'PUT,PATCH position' => 'position/update',
                'DELETE position' => 'position/delete',

                // API kind-job
                //'GET kind-job' => 'kind-job/view',
                'POST kind-job' => 'kind-job/create',
                'PUT,PATCH kind-job' => 'kind-job/update',
                'DELETE kind-job' => 'kind-job/delete',

                // API request
                //'GET request' => 'request/view',
                'POST request' => 'request/create',
                'PUT,PATCH request' => 'request/update',
                'DELETE request' => 'request/delete',

                // API response
                //'GET response' => 'response/view',
                'POST response' => 'response/create',
                'PUT,PATCH response' => 'response/update',
                'DELETE response' => 'response/delete',

                // API status-request
                //'GET status-request' => 'status-request/view',
                'POST status-request' => 'status-request/create',
                'PUT,PATCH status-request' => 'status-request/update',
                'DELETE status-request' => 'status-request/delete',

                // API status-response
                //'GET status-response' => 'status-response/view',
                'POST status-response' => 'status-response/create',
                'PUT,PATCH status-response' => 'status-response/update',
                'DELETE status-response' => 'status-response/delete',

                // API photo
                'GET //photo' => 'photo/view',
                'POST photo' => 'photo/create',
                'PUT,PATCH photo' => 'photo/update',
                'DELETE photo' => 'photo/delete',

                // API status-feedback
                'GET //status-feedback' => 'status-feedback/view',
                'POST status-feedback' => 'status-feedback/create',
                'PUT,PATCH status-feedback' => 'status-feedback/update',
                'DELETE status-feedback' => 'status-feedback/delete',

                // API feedback
                //'GET feedback' => 'feedback/view',
                'POST feedback' => 'feedback/create',
                'PUT,PATCH feedback' => 'feedback/update',
                'DELETE feedback' => 'feedback/delete',

                // API status-completion
                //'GET status-completion' => 'status-completion/view',
                'POST status-completion' => 'status-completion/create',
                'PUT,PATCH status-completion' => 'status-completion/update',
                'DELETE status-completion' => 'status-completion/delete',

                // API status-payment
                //'GET status-payment' => 'status-payment/view',
                'POST status-payment' => 'status-payment/create',
                'PUT,PATCH status-payment' => 'status-payment/update',
                'DELETE status-payment' => 'status-payment/delete',

                // API order
                //'GET order' => 'order/view',
                'POST order' => 'order/create',
                'PUT,PATCH order' => 'order/update',
                'DELETE order' => 'order/delete',

                // API project
                //'GET project' => 'project/view',
                'POST project' => 'project/create',
                'PUT,PATCH project' => 'project/update',
                'DELETE project' => 'project/delete',

                // API project-documents
                //'GET project-documents' => 'project-documents/view',
                'POST project-documents' => 'project-documents/create',
                'PUT,PATCH project-documents' => 'project-documents/update',
                'DELETE project-documents' => 'project-documents/delete',

                // API job-stages
                //'GET job-stages' => 'job-stages/view',
                'POST job-stages' => 'job-stages/create',
                'PUT,PATCH job-stages' => 'job-stages/update',
                'DELETE job-stages' => 'job-stages/delete',

                // API materials
                //'GET materials' => 'materials/view',
                'POST materials' => 'materials/create',
                'PUT,PATCH materials' => 'materials/update',
                'DELETE materials' => 'materials/delete',

                // API material-type
                //'GET material-type' => 'material-type/view',
                'POST material-type' => 'material-type/create',
                'PUT,PATCH material-type' => 'material-type/update',
                'DELETE material-type' => 'material-type/delete',

                // API status-material
                //'GET status-material' => 'status-material/view',
                'POST status-material' => 'status-material/create',
                'PUT,PATCH status-material' => 'status-material/update',
                'DELETE status-material' => 'status-material/delete',

                // API delivery
                //'GET delivery' => 'delivery/view',
                'POST delivery' => 'delivery/create',
                'PUT,PATCH delivery' => 'delivery/update',
                'DELETE delivery' => 'delivery/delete',

                // API delivery-place
                //'GET delivery-place' => 'delivery-place/view',
                'POST delivery-place' => 'delivery-place/create',
                'PUT,PATCH delivery-place' => 'delivery-place/update',
                'DELETE delivery-place' => 'delivery-place/delete',

                // API departure-place
                //'GET departure-place' => 'departure-place/view',
                'POST departure-place' => 'departure-place/create',
                'PUT,PATCH departure-place' => 'departure-place/update',
                'DELETE departure-place' => 'departure-place/delete',

                // API status-delivery
                //'GET status-delivery' => 'status-delivery/view',
                'POST status-delivery' => 'status-delivery/create',
                'PUT,PATCH status-delivery' => 'status-delivery/update',
                'DELETE status-delivery' => 'status-delivery/delete',
                */

                // default routing
                '/<controller>' => 'site/index',
                '/<controller>/<action>' => 'site/index',
                '/<module>/<controller>' => 'site/index',
                '/<module>/<controller>/<action>' => 'site/index',
                '/' => 'site/index',
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
