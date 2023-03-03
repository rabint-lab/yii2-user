<?php

use rabint\helpers\widget;
use trntv\filekit\widget\Upload;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model rabint\models\MultiModel */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('rabint', 'اطلاعات شخصی');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'n'), 'url' => ['channel']];
$this->params['breadcrumbs'][] = $this->title;
$profileModel = $model->getModel('profile');

?>

<div class="user-profile-form">

    <?php $form = ActiveForm::begin(); ?>

    <h2><?php echo $this->title; ?></h2>
    <div class="row">

        <div class="col-sm-12 col-md-6">
            <?php echo $form->field($profileModel, 'firstname')->textInput(['maxlength' => 255]) ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?php echo $form->field($profileModel, 'lastname')->textInput(['maxlength' => 255]) ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?php echo $form->field($profileModel, 'nickname')->textInput(['maxlength' => 255]) ?>
        </div>


        <!--        <div class="col-sm-12 col-md-6">
        <?php // echo $form->field($profileModel, 'locale')->dropDownlist(Yii::$app->params['availableLocales'])
        ?>
                </div>-->
        <div class="col-sm-12 col-md-6">
            <?php
            echo $form->field($profileModel, 'gender')->dropDownlist([
                \rabint\user\models\UserProfile::GENDER_FEMALE => Yii::t('rabint', 'Female'),
                \rabint\user\models\UserProfile::GENDER_MALE => Yii::t('rabint', 'Male')
            ], ['prompt' => ''])
            ?>
        </div>
        <?php
        /*
        <div class="col-sm-12 col-md-6">
            <?php echo $form->field($profileModel, 'cell')->textInput(['maxlength' => 11]) ?>
        </div>
        */
        ?>

        <div class="col-sm-12 col-md-6">
            <?php echo \rabint\helpers\widget::datePicker($form, $profileModel, 'brithdate'); ?>
            <?php echo $form->field($profileModel, 'description')->textarea(['rows' => 3]) ?>
        </div>

        <div class="col-sm-12 col-md-6">
            <div class="form-group field-userprofile-firstname">
                <label class="avatar-label" for="userprofile-avatar"><?= \Yii::t('app', 'تصویر'); ?></label>
                <?php echo widget::uploader($form, $profileModel, 'avatar_url', ['returnType' => 'path', 'url' => ['/user/default/avatar-upload']]); ?>
            </div>
        </div>

    </div>
    <div class="row">
        <?php
        $json = json_decode($profileModel->others ?: '[]', 1);
        $other_fileds = config('SERVICE.user.other_profile_fields', []);
        $other_fileds = $other_fileds();
        foreach ($other_fileds as $k => $field) {
            echo '<div class="col-12 col-md-6">';
            echo $form->field($profileModel, $field['field'])->textInput();
            echo '</div>';
        }

        ?>

    </div>
    <?php /*
    <h2><?php echo Yii::t('rabint', 'تغییر اطلاعات کاربری') ?></h2>
    <div class="row">
        <!--      <div class="col-sm-12 col-md-6">
        <?php //echo $form->field($model->getModel('account'), 'username')
        ?>
              </div>-->
        <div class="col-sm-12 col-md-4">
            <?php echo $form->field($model->getModel('account'), 'email')->input('email',['autocomplete'=>'off']) ?>
        </div>

        <div class="col-sm-6 col-md-4">
            <?php echo $form->field($model->getModel('account'), 'password')->passwordInput(['autocomplete'=>'off']) ?>
        </div>
        <div class="col-sm-6 col-md-4">
            <?php echo $form->field($model->getModel('account'), 'password_confirm')->passwordInput(['autocomplete'=>'off']) ?>
        </div>
    </div>

    */ ?>
    <div class="form-group text-center">
        <?php echo \yii\helpers\Html::submitButton(Yii::t('rabint', 'ثبت تغییرات'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

    <!-- =================================================================== -->

</div>