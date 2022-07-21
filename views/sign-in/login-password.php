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
$mergeRegLogin = \rabint\user\Module::getConfig('mergeRegisterAndLogin');
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-login">

    <div class="col-sm-12">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?php echo Html::activeHiddenInput($model, 'redirect') ?>

        <?php
        if (\rabint\user\Module::getConfig('tenants')) {
            $t = \rabint\user\Module::getConfig('tenants');
            $t = \yii\helpers\ArrayHelper::getColumn($t, 'title');

            ?>
            <div class="form-group field-loginform-tenant">
                <label for="loginform-tenant"><?= \Yii::t('app', rabint_getTenantLabel()); ?></label>
                <?php echo Html::dropDownList('_tenant_', rabint_getCurrentTenant(), $t, ['id' => 'selTenant', 'class' => 'form-control']); ?>
            </div>

            <script>
                <?php ob_start(); ?>
                $(document).ready(function () {
                    $('#selTenant').on('change', function () {
                        $val = $(this).val();
                        $.get('<?=\rabint\helpers\uri::to(['/site/switch-tenant', 'tenant' => ''])?>' + $val, function (res) {
                            window.location.reload();
                        });
                    });
                });
                <?php
                $script = ob_get_clean();
                $this->registerJs($script, $this::POS_END);
                ?>
            </script>
            <?php
        }
        ?>
        <?php echo $form->field($model, 'identity', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]]) ?>

        <div class="switch_pass_visibility">
            <?php echo $form->field($model, 'password', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter showPassword"]])->passwordInput() ?>
            <i class="fas fa-eye toggle_pass"></i>
        </div>
        <?php if (\rabint\user\Module::getConfig('enableCaptcha')): ?>
            <div class="form-group center">
                <?php
                switch (\rabint\user\Module::getConfig('enableCaptcha')) {
                    case 'reCaptcha3'://\kekaadrenalin\recaptcha3\ReCaptchaWidget::class
                        echo $form->field($model, 'reCaptcha')->widget(\kekaadrenalin\recaptcha3\ReCaptchaWidget::class);
                        break;
                    default:
                        echo '';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        <div class="form-group center">
            <?php echo Html::submitButton(
                $mergeRegLogin ? Yii::t('rabint', 'ورود / ثبت نام') : Yii::t('rabint', 'ورود'),
                ['class' => 'btn btn-primary login-button btn-lg pl-5 pr-5', 'name' => 'login-button']) ?>
        </div>
        <div class="form-group center" style="color:#999;margin:1em 0">
            <p>
                <?php
                if (!$mergeRegLogin && \rabint\user\Module::getConfig('enableActivation')) {
                    echo Html::a(Yii::t('rabint', 'ثبت نام'), ['activation', 'identity' => '', 'redirect' => $model->redirect], $linkOptions);
                    echo ' | ';
                }
                ?>
                <?php
                echo Html::a(Yii::t('rabint', 'بازیابی رمز'), ['activation', 'identity' => $model->identity, 'redirect' => $model->redirect], $linkOptions);
                ?>
            </p>
        </div>
        <?php ActiveForm::end(); ?>
        <style>
            .disabledIdentityLogin {
                position: relative;
            }

            .go_back_login {
                position: absolute;
                width: 25px;
                bottom: 13px;
                right: 10px;
            }

            .go_back_login svg {
                fill: #8c8c8c;
                transition: ease all .5s;
            }

            .go_back_login svg:hover {
                fill: #4e9aec;
            }

            .disabledIdentityLogin input {
                background: #f0f0f0 !important;
            }
        </style>

    </div>
</div>


<script>
    <?php ob_start(); ?>
    $(document).ready(function () {

        $('.switch_pass_visibility').on('click', '.toggle_pass', function () {
            $parent = $(this).parents('.switch_pass_visibility');
            type = $parent.find('input').attr('type');
            if (type === "password") {
                $(this).removeClass('fa-eye');
                $(this).addClass('fa-eye-slash');
                $('.showPassword').attr('type', "text");
            } else {
                $(this).removeClass('fa-eye-slash');
                $(this).addClass('fa-eye');
                $('.showPassword').attr('type', "password");
            }
        })

    });
    <?php
    $script = ob_get_clean();
    $this->registerJs($script, $this::POS_END);
    ?>
</script>
<style>
    .switch_pass_visibility {
        position: relative;
    }

    .switch_pass_visibility .toggle_pass {
        position: absolute;
        right: 9px;
        top: 40px;
        cursor: pointer;
    }

</style>
