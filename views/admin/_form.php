<?php

use rabint\user\models\User;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/* @var $this yii\web\View */
/* @var $model rabint\user\models\AdminUserForm */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $roles yii\rbac\Role[] */
/* @var $permissions yii\rbac\Permission[] */
?>
<div class="clearfix"></div>
<div class="form-box user-form">
    <div class="row">
        <div class="col-sm-12">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <?php echo $form->field($model, 'username') ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php echo $form->field($model, 'email')->input('email', ['autocomplete' => 'off']) ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php
                    echo $form->field($model, 'password')->passwordInput(['autocomplete' => 'off'])
                        ->hint(
                            $model->model->isNewRecord ? '' : \Yii::t('app', 'در صورتی که نمی خواهید رمز کاربر را تغییر دهید ، این قسمت را خالی بگذارید')
                        );
                    ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php echo $form->field($model, 'confirm')->passwordInput(['autocomplete' => 'off']) ?>
                </div>

                <div class="col-sm-12 col-md-6">
                    <?= $form->field($model, 'roles')->checkboxList($roles, ['name' => 'AdminUserForm[roles][]', 'class' => 'adminCheckboxList']) ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php
                    if (class_exists('\app\modules\post\models\Group')) {
                        if (!$model->isNewRecord) {
                            $model->groups = \app\modules\post\models\GroupMember::find()->andWhere(
                                ['user_id' => $model->getModel()->id]
                            )->select('group_id')->column();
                        }
                        $groups = \app\modules\post\models\Group::find()->all();
                        $groups = \yii\helpers\ArrayHelper::map($groups, 'id', 'title');
                        echo $form->field($model, 'groups')->checkboxList($groups, ['class' => 'adminCheckboxList']);
                    }
                    ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php echo $form->field($model, 'status')->dropDownList(\yii\helpers\ArrayHelper::getColumn(User::statuses(), 'title')) ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label for=""> </label>
                    <?php
                    echo $form->field($model, 'aset_must_changed_password')->checkbox(); ?>
                </div>

            </div>
        </div>
        <div class="spacer"></div>
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <?php echo $form->field($model, 'nickname') ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php echo $form->field($model, 'firstname') ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php echo $form->field($model, 'lastname') ?>
                </div>
                <div class="col-sm-12 col-md-6">

                    <?php echo $form->field($model, 'cell') ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php echo \rabint\helpers\widget::datePickerBs4($form, $model, 'brithdate'); ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?php
                    echo $form->field($model, 'gender')->dropDownlist([
                        \rabint\user\models\UserProfile::GENDER_FEMALE => Yii::t('rabint', 'Female'),
                        \rabint\user\models\UserProfile::GENDER_MALE => Yii::t('rabint', 'Male')
                    ], ['prompt' => ''])
                    ?>
                </div>
                <?php
                //echo $form->field($model, 'locale')
                ?>
                <!-- <div class="col-sm-12 col-md-12">
                <div class="form-group field-userprofile-firstname">
                    <label class="avatar-label" for="userprofile-avatar"><?= \Yii::t('app', 'تصویر'); ?></label>
                    <?php //echo widget::uploader($form, $model, 'avatar_url', ['returnType' => 'path', 'url' => ['/user/default/avatar-upload']]); ?>
                </div>
            </div> -->
            </div>
        </div>
        <div class="spacer"></div>
        <div class="col-sm-12">
            <div class="form-group text-center">
                <?php echo Html::submitButton(Yii::t('rabint', 'Save'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>