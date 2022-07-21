<?php

use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model rabint\models\MultiModel */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('rabint', 'درخواست پروفایل رسمی');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'n'), 'url' => ['channel']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-profile-form">

    <?php $form = ActiveForm::begin(); ?>

    <h2><?php echo $this->title; ?></h2>
    <div class="row">

        <div class="col-sm-6">
            <?php echo $form->field($model, 'firstname')->textInput(['maxlength' => 255]) ?>
        </div>
        <div class="col-sm-6">
            <?php echo $form->field($model, 'lastname')->textInput(['maxlength' => 255]) ?>
        </div>

        <div class="col-sm-6">
            <?php echo $form->field($model, 'phone')->textInput(['maxlength' => 11]) ?>
        </div>
        <div class="col-sm-6">
            <?php echo $form->field($model, 'cell')->textInput(['maxlength' => 11]) ?>
        </div>


        <div class="col-sm-6">
            <?php echo $form->field($model, 'melli_code')->textInput() ?>
        </div>
        <div class="col-sm-6">
            <div class="form-group field-userprofile-firstname">
                <label class="avatar-label" for="userprofile-avatar"><?= \Yii::t('rabint', 'تصویر کارت ملی یا معرفی نامه'); ?></label>
                <?php //echo rabint\helpers\widget::uploader($form, $model, 'melli_cart_url', ['returnType' => 'path', 'url' => ['/user/default/avatar-upload']]); ?>
            </div>
        </div>
        <div class="col-sm-12">
            <?php echo $form->field($model, 'address')->textarea(['rows' => 3]) ?>
        </div>

        <div class="col-sm-12">
            <?php echo $form->field($model, 'dataisvalid')->checkbox() ?>
        </div>
    </div>
    <div class="form-group text-center">
        <?php echo Html::submitButton(Yii::t('rabint', 'ثبت درخواست'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
