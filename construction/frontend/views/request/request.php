<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<h1>Form for GET request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'get', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>


<h1>Form for POST request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'post', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

<h1>Form for PUT request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'put', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

<h1>Form for DELETE request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'delete', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

