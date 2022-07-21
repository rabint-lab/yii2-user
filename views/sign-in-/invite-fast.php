<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use rabint\user\Module;
use rabint\helpers\uri;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\SignupForm */
//$this->context->layout = '@themelayouts/common';
$this->title = Yii::t('rabint', 'تکمیل ثبت نام');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
$redirect = (Yii::$app->request->get('redirect')?['redirect'=>Yii::$app->request->get('redirect')]:[] );
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>

<div class="site-signup">
    <div class="clearfix spacer"></div>

    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-info">
                <p>
                    <i class="fas fa-info"></i> <?= \Yii::t('app', 'کاربر گرامی! شما از طریق یکی از اعضاء یا سازمان های موجود در این سامانه ، دعوت شده اید. لطفا جهت تکمیل ثبت نام خود ، اطلاعات زیر را تکمیل نمایید.'); ?>
                </p>
            </div>
            <div class="spacer"></div>
        </div>
        <div class=" col-sm-12">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
            <?php echo Html::activeHiddenInput($model,'redirect') ?>
            <div class="row">
                <div class="col-sm-6">
                    <?php echo $form->field($model, 'nickname', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]])->hint(Yii::t('app','این نام جهت نمایش به دیگران استفاده می شود.')) ?>
                </div>
                <div class="col-sm-6">
                    <?php echo $form->field($model, 'username', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]])->hint(Yii::t('app','جهت اطمینان از استفاده شخص شما از حساب کاربریتان، نیاز به صحت سنجی از طریق تلفن همراه داریم')) ?>
                </div>
            </div>
            <div class="row">

                <div class="col-sm-6">
                    <?php echo $form->field($model, 'password', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])->passwordInput();//->hint(\Yii::t('rabint', 'حداقل 6 کاراکتر به صورت ترکیب حروف و عدد')); ?>
                </div>
                <div class="col-sm-6">
                    <?php echo $form->field($model, 'confirm', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])->passwordInput() ?>
                </div>
            </div>
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
            <div class="spacer"></div>
            <div class="center">
                <?= Yii::t(
                    'rabint',
                    'با زدن به روی دکمه زیر،  {link} را می پذیرم.',
                    ['link' => '<a class="a_alt" target="_BLANK" href="' . uri::to(Module::$registerRulePage, true) . '">' . \Yii::t('rabint', 'شرایط و قوانین') . '</a>']
                ); ?>
                <div class="spacer"></div>
            </div>
            <div class="form-group text-center">
                <?php echo Html::submitButton(Yii::t('rabint', 'تکمیل ثبت نام'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                <?php echo Html::a(Yii::t('rabint', 'ورود'), array_merge(['login'],$redirect), ['class' => 'btn btn-link']) ?>
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
                <?php //= \rabint\page\models\Page::getPageBySlug('user-register');
                ?>
            </p>
        </div>
    </div>
</div>