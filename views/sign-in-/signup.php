<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use rabint\user\Module;
use rabint\helpers\uri;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\SignupForm */
//$this->context->layout = '@themelayouts/common';
$this->title = Yii::t('rabint', 'ثبت نام در سایت');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
$login_image = \rabint\helpers\option::get('general','login_image',true);
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-signup">
    <div class="clearfix spacer"></div>

    <div class="row">

        <div class="<?=($login_image)?'col-sm-6':'d-none'?>">
            <img src="<?=$login_image?>" class="img-fluid">
        </div>

        <div class="<?=($login_image)?'col-sm-6':'col-sm-12'?>">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
            <?php echo Html::activeHiddenInput($model,'redirect') ?>
            <?php echo $form->field($model, 'username', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]]) ?>
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <?php echo $form->field($model, 'name') ?>
                </div>
                <div class="col-md-6 col-sm-6">
                    <?php echo $form->field($model, 'family') ?>
                </div>
            </div>
            <?php echo $form->field($model, 'email', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]]) ?>
            <?php echo $form->field($model, 'password', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])->passwordInput()->hint(\Yii::t('rabint', 'حداقل 6 کاراکتر به صورت ترکیب حروف و عدد')); ?>
            <?php echo $form->field($model, 'confirm', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])->passwordInput() ?>
            <?php echo $form->field($model, 'legal')->checkbox(['label' => Yii::t('rabint', 'با {link} موافقم و آن را می پذیرم', ['link' => '<a href="' . uri::to(Module::$registerRulePage,true) . '">' . \Yii::t('rabint', 'شرایط و قوانین') . '</a>'])]) ?>

            <div class="col-sm-12  horizontal-input-captcha">
                <div class="row">
                    <?=
                            $form->field($model, 'verifyCode', ['template' => '<div class="captcha-label">{label}</div>{input}{error}{hint}', 'inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])
                            ->widget(yii\captcha\Captcha::className(), [
                                'captchaAction' => '/site/captcha',
                                'template' => '<div class="captcha-input">{input}</div><div class="captcha-img">{image}</div>',
                            ])->hint(\Yii::t('rabint', 'برای تغییر کد، روی آن کلیک نمایید.'));
                    ?>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>

            <div class="form-group text-center">
                <?php echo Html::submitButton(Yii::t('rabint', 'Signup'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>
            <?php /* ================================================================= * /?>
              <h2><?php echo Yii::t('rabint', 'Sign up with')  ?>:</h2>
              <div class="form-group">
              <?php echo yii\authclient\widgets\AuthChoice::widget([
              'baseAuthUrl' => ['/user/sign-in/oauth']
              ]) ?>
              </div>
              <?php /* ================================================================== */ ?>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-md-6 offset-md-3 col-sm-12">
            <p class="regHelp">
                <?php //= \rabint\page\models\Page::getPageBySlug('user-register'); ?>
            </p>
        </div>
    </div>
</div>
