<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\PasswordResetRequestForm */

$this->context->layout = '@themeLayouts/login';
$this->title = $type =='activation'?Yii::t('rabint', 'ارسال دوباره کد فعال سازی'):Yii::t('rabint', 'Request password reset');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-request-password-reset">

    <div class="clearfix spacer"></div>

    <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
    <?php echo Html::activeHiddenInput($model,'redirect') ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="clearfix spacer"></div>
            <?= \Yii::t('rabint', 'لطفا تلفن همراه خود را در قسمت زیر وارد نمایید تا کد بازیابی رمز برای شما پیامک گردد.'); ?>
        </div>
        <div class="clearfix spacer"></div>

        <div class="col-sm-12">
            <?php echo $form->field($model, 'username', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]]) ?>

        </div>
        <div class="col-sm-12  horizontal-input-captcha">
                <?=
                    $form->field($model, 'verifyCode', ['template' => '<div class="captcha-label">{label}</div>{input}{error}{hint}', 'inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])
                        ->widget(yii\captcha\Captcha::className(), [
                            'captchaAction' => '/site/captcha',
                            'template' => '<div class="captcha-input">{input}</div><div class="captcha-img">{image}</div>',
                        ])->hint(\Yii::t('rabint', 'برای تغییر کد، روی آن کلیک نمایید.'));
                ?>
                <div class="clearfix"></div>
        </div>
        <div class="spacer"></div>
        <div class="spacer"></div>
        <div class="col-sm-12">
        <div class="form-group center ">
                <?php echo Html::submitButton(\Yii::t('rabint', 'ارسال  کد بازیابی'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>