<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<h1>Form for GET request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'get', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?= $form->field($modelRequest, 'adress') ?>

    <?= $form->field($modelRequest, 'name') ?>

    <?= $form->field($modelRequest, 'description') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>


<h1>Form for POST request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'post', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

<?= $form->field($modelRequest, 'adress') ?>

<?= $form->field($modelRequest, 'name') ?>

<?= $form->field($modelRequest, 'description') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

<h1>Form for PUT request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'put', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

<?= $form->field($modelRequest, 'adress') ?>

<?= $form->field($modelRequest, 'name') ?>

<?= $form->field($modelRequest, 'description') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

<h1>Form for DELETE request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'delete', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

<?= $form->field($modelRequest, 'adress') ?>

<?= $form->field($modelRequest, 'name') ?>

<?= $form->field($modelRequest, 'description') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

