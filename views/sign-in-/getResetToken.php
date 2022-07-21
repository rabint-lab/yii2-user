<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->context->layout = '@themeLayouts/login';
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\GetResetToken */

$this->title = Yii::t('rabint', 'تایید کد فعال سازی');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-reset-password">
   
    <div class="clearfix"></div>

        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
        <?php echo Html::activeHiddenInput($model,'redirect') ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="clearfix spacer"></div>
            <p>
                <?= \Yii::t('rabint', 'کد فعال سازی برای شما پیامک شد.'); ?>
           
            <?= \Yii::t('rabint', 'لطفا کد فعال سازی را در قسمت زیر وارد نمایید.'); ?>
            </p>
        </div>
        <div class="clearfix spacer"></div>

        <div class="col-sm-12">
            <?php echo $form->field($model, 'token')->textInput()->hint(\Yii::t('rabint', 'شما دو دقیقه فرصت دارید تا کد فعال سازی را وارد نمایید')); ?>
            <div class="form-group center">
                <?php echo Html::submitButton(\Yii::t('rabint', 'بررسی کد'), ['class' => 'btn btn-primary']) ?>
                <?php echo Html::a(\Yii::t('rabint', 'ارسال دوباره کد'),['request-password-reset','type'=>'activation','redirect'=>$model->redirect], ['class' => 'btn btn-warning']) ?>
            </div>
        </div>
    </div>
        <?php ActiveForm::end(); ?>
</div>
