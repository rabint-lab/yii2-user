<?php

use rabint\user\models\User;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model rabint\user\models\AdminUserForm */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $roles yii\rbac\Role[] */
/* @var $permissions yii\rbac\Permission[] */
?>
<div class="clearfix"></div>
<div class="form-box user-form">
    <div class="row">
        <div class="col-sm-12">

            <?php $form = ActiveForm::begin(); ?>
            <?php echo $form->field($model, 'username') ?>
            <?php echo $form->field($model, 'email')->input('email',['autocomplete' => 'off']) ?>
            <?php
            echo $form->field($model, 'password')->passwordInput(['autocomplete' => 'off'])
                ->hint(
                    $model->model->isNewRecord ? '' : \Yii::t('app', 'در صورتی که نمی خواهید رمز کاربر را تغییر دهید ، این قسمت را خالی بگذارید')
                );
            ?>
            <?php echo $form->field($model, 'confirm')->passwordInput(['autocomplete' => 'off']) ?>
            <?php echo $form->field($model, 'status')->dropDownList(\yii\helpers\ArrayHelper::getColumn(User::statuses(), 'title')) ?>
            <?= $form->field($model, 'roles')->checkboxList($roles, ['name' => 'AdminUserForm[roles][]']) ?>
            <?php
            echo $form->field($model, 'aset_must_changed_password')->checkbox(); ?>
        </div>
        <div class="spacer"></div>
        <div class="col-sm-12">

            <?php echo $form->field($model, 'nickname') ?>
            <?php echo $form->field($model, 'firstname') ?>
            <?php echo $form->field($model, 'lastname') ?>
            <?php //echo $form->field($model, 'locale') 
            ?>
            <?php echo $form->field($model, 'cell') ?>

            <?php echo \rabint\helpers\widget::datePickerBs4($form, $model, 'brithdate'); ?>

            <?php
            echo $form->field($model, 'gender')->dropDownlist([
                \rabint\user\models\UserProfile::GENDER_FEMALE => Yii::t('rabint', 'Female'),
                \rabint\user\models\UserProfile::GENDER_MALE => Yii::t('rabint', 'Male')
            ], ['prompt' => ''])
            ?>
            <!-- <div class="col-sm-12 col-md-12">
                <div class="form-group field-userprofile-firstname">
                    <label class="avatar-label" for="userprofile-avatar"><?= \Yii::t('app', 'تصویر'); ?></label>
                    <?php //echo widget::uploader($form, $model, 'avatar_url', ['returnType' => 'path', 'url' => ['/user/default/avatar-upload']]); ?>
                </div>
            </div> -->
        </div>
        <div class="spacer"></div>
        <div class="col-sm-12">
            <div class="form-group">
                <?php echo Html::submitButton(Yii::t('rabint', 'Save'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>