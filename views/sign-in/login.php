<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;


$this->context->layout = '@themeLayouts/login';
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\LoginForm */

$this->title = Yii::t('rabint', 'ورود به حساب کاربری');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax) ? ['role' => "modal-remote"] : [];
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-login">
    <div class="col-sm-12">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?php echo Html::activeHiddenInput($model, 'redirect') ?>
        <?php echo $form->field($model, 'identity', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]]) ?>
        <div class="password" style="display: none">
            <?php echo $form->field($model, 'password', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]])->passwordInput() ?>
        </div>

        <div class="form-group center">
            <?php echo Html::submitButton(
                Yii::t('rabint', 'ادامه') . ' <i class="form_loader fas fa-sync-alt fa-spin" style="display: none"></i>',
                ['class' => 'btn btn-primary login-button btn-lg pl-5 pr-5', 'name' => 'login-button']) ?>

        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<script>
    <?php ob_start(); ?>
    $(document).ready(function () {
        $('#login-form').on('submit', function () {
            $('#login-form .login-button').attr('disabled', 'disable');
            $('#login-form .form_loader').show();
            url = $(this).attr('action');
            method = $(this).attr('method');
            data = $(this).serialize();

            if (typeof xhr !== 'undefined') {
                xhr.abort();
            }
            var xhr = $.ajax({
                'dataType': "html",
                'url': url,
                'method': method,
                'data': data
            });

            xhr.done(function (Html) {
                $('#login-form .login-button').removeAttr('disabled');
                $('#login-form .form_loader').hide();
                $html = $(Html).find('.site-login').html();
                $('.site-login').html($html);
            });

            xhr.fail(function () {
                $('#login-form .login-button').removeAttr('disabled');
                $('#login-form .form_loader').hide();
                $('.site-login').prepend('<div class="alert alert-danger" role="alert">' +
                    '<?=Yii::t('app', 'خطا در دریافت اطلاعات');?>' +
                    '</div>')
            });

            return false;
        });
    });
    <?php
    $script = ob_get_clean();
    $this->registerJs($script, $this::POS_END);
    ?>
</script>