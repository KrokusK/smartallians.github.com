<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Авторизация';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Пожалуйста заполните ниже поля для авторизации:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'email_phone')->textInput(['autofocus' => true])->hint('Пожалуйста, введите email или телефон в формате: +7 (999) 999-99-99')->label('Email / Телефон в формате: +7 (999) 999-99-99') ?>

                <?= $form->field($model, 'password')->passwordInput()->hint('Пожалуйста, введите пароль')->label('Пароль') ?>

                <?= $form->field($model, 'rememberMe')->checkbox()->label('Запомнить меня') ?>

                <div style="color:#999;margin:1em 0">
                    Если Вы забыли свой пароль, то перейдите по ссылке <?= Html::a('сбросить пароль', ['site/request-password-reset']) ?>.
                    <br>
                    Проверить почту? <?= Html::a('выслать ключ верификации', ['site/resend-verification-email']) ?>
                </div>

                <div class="form-group">
                    <?= Html::submitButton('Вход', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
