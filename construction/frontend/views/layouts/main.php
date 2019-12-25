<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset=utf-8>
    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name=viewport content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <link rel=stylesheet href=fonts/_fonts.css>

    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>

    <link href=/css/chunk-01dd175a.72393b08.css rel=prefetch>
    <link href=/css/chunk-5fed29bc.bc26259b.css rel=prefetch>
    <link href=/css/chunk-64270723.8e63a075.css rel=prefetch>
    <link href=/css/chunk-b5aa61f8.a6b9f33d.css rel=prefetch>
    <link href=/css/chunk-c6d5b5d2.207dbab3.css rel=prefetch>
    <link href=/css/app.d33537c5.css rel=preload as=style>
    <link href=/css/app.d33537c5.css rel=stylesheet>
    <link href=/js/chunk-01dd175a.9171cbe4.js rel=prefetch>
    <link href=/js/chunk-5fed29bc.b5434ad0.js rel=prefetch>
    <link href=/js/chunk-64270723.c945e296.js rel=prefetch>
    <link href=/js/chunk-b5aa61f8.811fdaea.js rel=prefetch>
    <link href=/js/chunk-c6d5b5d2.fde16e82.js rel=prefetch>
    <link href=/js/app.4d4a80ed.js rel=preload as=script>
    <link href=/js/chunk-vendors.29d4b946.js rel=preload as=script>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container">

        <?= $content ?>
    </div>
</div>



<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
