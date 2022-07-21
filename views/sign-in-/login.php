<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\captcha\Captcha;

$this->context->layout = '@themeLayouts/login';
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\LoginForm */

$this->title = Yii::t('rabint', 'Login');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
$redirect = (Yii::$app->request->get('redirect')?['redirect'=>Yii::$app->request->get('redirect')]:[] );
$login_image = \rabint\helpers\option::get('general','login_image',true);

?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-login">
    
    <div class="row">
        <div class="clearfix spacer"></div>
        <div class="clearfix spacer"></div>
        <div class="clearfix spacer"></div>

        <div class="<?=($login_image)?'col-sm-6':'d-none'?>">
            <img src="<?=$login_image?>" class="img-fluid">
        </div>

        <div class="<?=($login_image)?'col-sm-6':'col-sm-12'?>">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
            <?php echo Html::activeHiddenInput($model,'redirect') ?>
            <?php echo $form->field($model, 'identity', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]]) ?>
            <?php echo $form->field($model, 'password', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]])->passwordInput() ?>
            <?php if ($model->scenario != \rabint\user\models\LoginForm::SCENARIO_FIRST_TRY) { ?>
                <div class="col-sm-12  horizontal-input-captcha">
                    <div class="row">
                        <?php
                        //                             echo   $form->field($model, 'verifyCode', ['template' => '<div class="captcha-label">{label}</div>{input}{error}{hint}', 'inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])
                        //                                ->widget(Captcha::className(), [
                        //                                    'captchaAction' => '/main/captcha',
                        //                                    'template' => '<div class="captcha-input">{input}</div><div class="captcha-img">{image}</div>',
                        //                                ])->hint(\Yii::t('rabint', 'برای تغییر کد، روی آن کلیک نمایید.'));
                        ?>

                        <?=
                            $form->field($model, 'verifyCode')
                                ->widget(Captcha::className(), [
                                    'captchaAction' => '/site/captcha',
                                    'template' => '<div class="row"><div class="col-sm-12 center center-block">{image}</div><div class="clearfix"></div><div class="col-sm-12">{input}</div></div>',
                                ])->hint(\Yii::t('rabint', 'برای تغییر کد روی عکس کلیک نمایید'));
                        ?>

                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            <?php } ?>
            <?php echo $form->field($model, 'rememberMe')->checkbox() ?>

            <div class="form-group center">
                <?php echo Html::submitButton(Yii::t('rabint', 'Login'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
            <div class="form-group center" style="color:#999;margin:1em 0">
                <?php 
                echo \Yii::t('rabint', 'رمز ورود خود را فراموش کرده اید؟');
                echo " ";
                echo Html::a(Yii::t('rabint', 'باز یابی رمز'), array_merge(['sign-in/request-password-reset'],$redirect),$linkOptions);
                ?>
            </div>
            <div class="form-group center">
                
                <?php 
                
                echo Html::a(Yii::t('rabint', 'Need an account? Sign up.'), array_merge(['signup'],$redirect),$linkOptions) ?>
            </div>
            <?php /* ================================================================= * /?>
            <h2><?php echo Yii::t('rabint', 'Log in with')  ?>:</h2>
            <div class="form-group">
                <?php echo yii\authclient\widgets\AuthChoice::widget([
              'baseAuthUrl' => ['/user/sign-in/oauth']
              ]) ?>
            </div>
            <?php /* ================================================================== */ ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>