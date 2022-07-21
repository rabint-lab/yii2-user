<?php

use yii\widgets\DetailView;
use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model rabint\user\models\User */

$this->title = \Yii::t('rabint', 'ویرایش دسترسی') . ' ' . $model->getPublicIdentity();
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-permisions col-md-12">
    <br/>
    <div class="pull-right float-right">
        <img src="<?= $model->userProfile->avatar; ?>" class="img-thumbnail img-circle" width="100px" height="100px" />
    </div>
    <div class="pull-right float-right" style="margin-right: 15px;">
        <div class="class" style="height: 25px;"><span class="class"><?= \Yii::t('rabint', 'نام کاربری'); ?></span>: <?= $model->username; ?></div>
        <div class="class" style="height: 25px;"><span class="class"><?= \Yii::t('rabint', 'نام و نام خانوادگی'); ?></span>: <?= Html::decode($model->userProfile->firstname . ' ' . $model->userProfile->lastname); ?></div>
        <div class="class" style="height: 25px;"><span class="class"><?= \Yii::t('rabint', 'نام نمایشی'); ?></span>: <?= $model->displayName; ?></div>
        <div class="class" style="height: 25px;"><span class="class"><?= \Yii::t('rabint', 'ایمیل'); ?></span>: <?= $model->email; ?></div>
    </div>
    <div class="clearfix spacer"></div>
    <div class="row">
        <div class="col-md-8" >
            <div class="row">
                <div class="col-md-6" >
                    <div class="box box-info">
                        <div class="box-header">
                            <div class="action-box">
                                <h4 class="master-title">
                                    نقش های  <?= $model->getPublicIdentity();?>
                                </h4>
                            </div>
                        </div>
                        <div class="box-body">
                            <ul class="class">
                                <?php
                                
                                foreach ($userRoles as $role) {
                                    echo '<li>' . $role->description.'</li>';

                                    $perms = Yii::$app->authManager->getPermissionsByRole($role->name);
                                    if(count($perms) ){
                                        echo "<ul>";
                                        foreach($perms as $perm){
                                            echo '<li>' . $perm->description.'</li>';
                                        }
                                        echo "</ul>";
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6" >
                    <div class="box box-info">
                        <div class="box-header">
                            <div class="action-box">
                                <h4 class="master-title">
                                    دسترسی های  <?= $model->getPublicIdentity();?>
                                </h4>
                            </div>
                        </div>
                        <div class="box-body">
                            <ul class="class">
                                <?php
                                foreach ($userPerms as $perm) {
                                    if(array_key_exists($perm->name, $rolePerms))
                                        echo '<li style="color:red" title="دسترسی بر اساس نقش (غیر قابل حذف)" >' . $perm->description.'</li>';
                                    else
                                        echo '<li style="color:green" title="دسترسی قابل ویرایش">' . $perm->description.'</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card ">
                        <div class="card-header bg-success text-white ">
                            افزودن دسترسی
                        </div>
                        <div class="card-body">
                            <?php $form = ActiveForm::begin(); ?>
                            <?= Html::dropDownList('add',[],$permsCanAdd,['prompt'=>''])?>
                            <?= Html::submitButton( Yii::t('app', 'افزودن')  , ['class' =>   'btn btn-success btn-flat','name'=>'add_btn']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header  bg-danger text-white">
                            حذف دسترسی
                        </div>
                        <div class="card-body">
                            <?php $form = ActiveForm::begin(); ?>
                            <?= Html::dropDownList('remove',[],$permsCanRemove,['prompt'=>''])?>
                            <?= Html::submitButton( Yii::t('app', 'حذف کردن')  , ['class' =>   'btn btn-danger btn-flat','name'=>'remove_btn']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
