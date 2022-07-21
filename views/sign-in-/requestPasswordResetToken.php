<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\PasswordResetRequestForm */
$this->context->layout = '@themeLayouts/login';
$this->title = Yii::t('rabint', 'Request password reset');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-request-password-reset">
    <div class="clearfix spacer"></div>

    <div class="row">
        <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
        <?php echo Html::activeHiddenInput($model,'redirect') ?>
        <div class="col-sm-12">
            <div class="clearfix spacer"></div>

            <?= \Yii::t('rabint', 'لطفا ایمیل خود را در قسمت زیر وارد نمایید تا لینک بازیابی رمز برای شما ایمیل گردد.'); ?>
        </div>
        <div class="clearfix spacer"></div>

        <div class="col-sm-12">
            <?php echo $form->field($model, 'email') ?>
            <div class="form-group center ">
                <?php echo Html::submitButton(\Yii::t('rabint', 'ارسال  ایمیل'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
