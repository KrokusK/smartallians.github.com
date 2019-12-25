<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        //'css/chunk-01dd175a.72393b08.css',
        //'css/chunk-5fed29bc.bc26259b.css',
        //'css/chunk-64270723.8e63a075.css',
        //'css/chunk-b5aa61f8.a6b9f33d.css',
        //'css/chunk-c6d5b5d2.207dbab3.css',
        //'css/app.d33537c5.css',
    ];
    public $js = [
        //'js/chunk-01dd175a.9171cbe4.js',
        //'js/chunk-5fed29bc.b5434ad0.js',
        //'js/chunk-64270723.c945e296.js',
        //'js/chunk-b5aa61f8.811fdaea.js',
        //'js/chunk-c6d5b5d2.fde16e82.js',
        //'js/app.4d4a80ed.js',
        //'js/chunk-vendors.29d4b946.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
