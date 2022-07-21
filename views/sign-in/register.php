<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;


$this->context->layout = '@themeLayouts/login';
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\LoginForm */

$this->title = Yii::t('rabint', 'تکمیل ثبت نام');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-login">

    <div class="col-sm-12">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?php echo Html::activeHiddenInput($model,'redirect') ?>
        <?php echo Html::activeHiddenInput($model,'identity') ?>
        <?php echo $form->field($model, 'nickname')->hint(Yii::t('app','این نام جهت نمایش به دیگران ، استفاده می شود')) ?>
<!--        <div class="row">-->
<!--            <div class="col-md-6 col-sm-6">-->
<!--                --><?php //echo $form->field($model, 'name') ?>
<!--            </div>-->
<!--            <div class="col-md-6 col-sm-6">-->
<!--                --><?php //echo $form->field($model, 'family') ?>
<!--            </div>-->
<!--        </div>-->
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <?php echo $form->field($model, 'password', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])->passwordInput()->hint(\Yii::t('rabint', 'حداقل 6 کاراکتر به صورت ترکیب حروف و عدد')); ?>
            </div>
            <div class="col-md-6 col-sm-6">
                <?php echo $form->field($model, 'confirm', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])->passwordInput() ?>
            </div>
        </div>
        <div class="form-group center">
            <?php echo Html::submitButton(Yii::t('rabint', 'تکمیل ثبت نام'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>