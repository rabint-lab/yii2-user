<?php

use rabint\helpers\widget;
use rabint\user\models\User;
use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model rabint\user\models\AdminUserForm */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $roles yii\rbac\Role[] */
/* @var $permissions yii\rbac\Permission[] */


if(Yii::$app->user->can('administrator')){
    $groups = app\modules\user_group\models\UserGroup::find()->all();
}else{
   $groups = \app\modules\user_group\models\UserGroupRelation::find()
                    ->where(['user_id'=> Yii::$app->user->id,'grade_id'=>[1,2]])
                    ->select(['user_group.id','user_group.title'])
                    ->join('left join','user_group',['user_group.id'=>'user_group_relation.group_id'])
//                    ->asArray()
                    ->all(); 
}


?>

<script>
    <?php ob_start() ?>
    $(document).ready(function(){
        $('#adminuserform-username').change(function(){
            $.get(
                '<?= Url::home() ?>user/admin/validate-user',
        {
                    'username':$(this).val()
        },
                function(data){
                    
                    if(data == 1){
                        $('.otherFields').fadeOut();
                    }else{
                        $('.otherFields').fadeIn();
                    }
                }
                );
        });
    });    
    <?php
    $script = ob_get_clean(); 
    $this->registerJs($script,$this::POS_END);
            ?>
</script>
<div class="clearfix"></div>
<div class="form-box user-form">
    <div class="row">
        <div class="col-sm-12">

            <?php $form = ActiveForm::begin(); ?>
            <?php echo $form->field($model, 'group')->dropDownList(\yii\helpers\ArrayHelper::map($groups,'id','title')) ?>
            <?php echo $form->field($model, 'username') ?>
            <div class="otherFields" style="display: none" >
                <div>
                <?php echo $form->field($model, 'email') ?>
                <?php
                echo $form->field($model, 'password')->passwordInput()
                    ->hint(
                        $model->model->isNewRecord ? '' : \Yii::t('app', 'در صورتی که نمی خواهید رمز کاربر را تغییر دهید ، این قسمت را خالی بگذارید')
                    );
                ?>
                <?php echo $form->field($model, 'confirm')->passwordInput() ?>
                <?php // echo $form->field($model, 'status')->dropDownList(\yii\helpers\ArrayHelper::getColumn(User::statuses(), 'title')) ?>
                <?php //  $form->field($model, 'roles')->dropDownList($roles, ['name' => 'AdminUserForm[roles][]']) ?>
                <?php
    //            echo $form->field($model, 'aset_must_changed_password')->checkbox(); ?>
            </div>
            <div class="spacer"></div>
            <div class="col-sm-12">

                <?php echo $form->field($model, 'nickname') ?>
                <?php echo $form->field($model, 'firstname') ?>
                <?php echo $form->field($model, 'lastname') ?>
                <?php //echo $form->field($model, 'locale') 
                ?>
                <?php // echo $form->field($model, 'cell') ?>

                <?php // echo \rabint\helpers\widget::datePickerBs4($form, $model, 'brithdate'); ?>

                <?php
                echo $form->field($model, 'gender')->dropDownlist([
                    \rabint\user\models\UserProfile::GENDER_FEMALE => Yii::t('rabint', 'Female'),
                    \rabint\user\models\UserProfile::GENDER_MALE => Yii::t('rabint', 'Male')
                ], ['prompt' => ''])
                ?>

            </div>
        
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
</div>

