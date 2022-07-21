<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

$this->context->layout = '@themeLayouts/login';
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\LoginForm */

$this->title = Yii::t('rabint', 'فعالسازی حساب کاربری');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax) ? ['role' => "modal-remote"] : [];


$mergeRegLogin = \rabint\user\Module::getConfig('mergeRegisterAndLogin');
$baseAuth = \rabint\user\Module::getConfig('baseAuthenticate');
$baseTitle = ' ' . $model->attributeLabels()['identity'] . ' ';
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


        <?php


        echo $form->field($model, 'identity', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]])
            ->hint(
                empty($model->identity) ? Yii::t('app', 'لطفا {title}‍ خود را در بخش فوق وارد نمایید و سپس دکمه زیر را کلیک نمایید', ['title' => $baseTitle]) :
                    Yii::t('app', 'در صورتی که از صحت {title} خود اطمینان دارید، دکمه زیر را کلیک نمایید', ['title' => $baseTitle])
            );
        ?>

        <div class="form-group center">
            <?php echo Html::submitButton(Yii::t('rabint', 'ارسال کد فعال سازی'), ['class' => 'btn btn-success', 'name' => 'login-button']) ?>
        </div>
        <div class="form-group center" style="color:#999;margin:1em 0">
            <p>
                <?php
                echo Html::a(Yii::t('rabint', 'ورود به حساب کاربری'), ['login', 'redirect' => $model->redirect], $linkOptions);
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