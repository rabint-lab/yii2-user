<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model rabint\user\models\User */
/* @var $roles yii\rbac\Role[] */

$this->title = Yii::t('rabint', 'Update {Username}: ', ['Username' => $model->username]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->email, 'url' => ['view', 'id' => $model->email]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'Update')];
?>
<div class="user-update">

    <?php
    echo $this->render('_form', [
        'model' => $model,
        'roles' => $roles
    ])
    ?>
<!--    <div class="form-box user-form">-->
<!--        <div class="row">-->
<!--            <div class="col-sm-12">-->
<!--                <div class="box box-info">-->
<!--                    <div class="box-header with-border">-->
<!--                        <h3 class="box-title">--><?//= \Yii::t('rabint', 'اطلاعات هویتی کاربر'); ?><!--</h3>-->
<!--                        <div class="box-tools pull-left float-left">-->
<!--                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fas fa-minus"></i></button>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="box-body">-->
<!--                        --><?php
//                        $modelProfile = \rabint\helpers\user::profile($user_id);
////                        $modelProfile->melli_cart_url = \rabint\cdn\models\Attachment::getUrlByPath($modelProfile->melli_cart_url);
////                        echo yii\widgets\DetailView::widget([
////                            'model' => $modelProfile,
////                            'attributes' => [
////                                'user_id',
////                                'firstname',
////                                'lastname',
////                                'nickname',
////                                'melli_code',
////                                    [
////                                    'attribute' => 'brithdate',
////                                    'value' => \rabint\helpers\locality::jdate('j F Y', $modelProfile->brithdate),
////                                ],
////                                'phone',
////                                'cell',
////                                'avatar:image',
////                                'melli_cart_url:image',
////                                'address:ntext',
////                                'description:ntext',
////                            ],
////                        ])
//                        ?>
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
</div>
