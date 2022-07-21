<?php

use common\grid\EnumColumn;
use rabint\user\models\User;
use yii\helpers\Html;
use rabint\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel rabint\user\models\search\AdminUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('rabint', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grid-box post-index">
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-header">
                    <div class="action-box">
                        <h2 class="master-title">
                            <?= Html::encode($this->title) ?>
                            <?= Html::a(Yii::t('rabint', 'Create user'), ['create'], ['class' => 'btn btn-success btn-xs btn-flat']) ?>
                        </h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="user-index">

                        <?php
                        echo GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'showAddBtn'=>false,
                            'showRefreshBtn'=>false,
                            'pager' => [
                                'firstPageLabel' => '««',
                                'lastPageLabel' => '»»'
                            ],
                            'columns' => [
                                    [
                                    'attribute' => 'id',
                                    'filterOptions' => ['style' => 'max-width:100px;'],
                                    'format' => 'raw',
                                ],
                                    [
                                    'attribute' => 'username',
                                    'value' => function($model) {
                                        return $model->username;
                                    },
                                ],
                                    [
                                    'attribute' => 'email',
                                ],
//                                    [
//                                    'attribute' => 'melli_code',
//                                    'label' => \Yii::t('rabint', 'کد ملی'),
//                                    'value' => function($model) {
//                                        return $model->userProfile->melli_code;
//                                    },
//                                ],
                                    [
                                    'attribute' => 'realName',
                                    'label' => \Yii::t('rabint', 'نام واقعی'),
                                    'value' => function($model) {
                                        if(!isset($model->userProfile)){
                                            return Yii::t('app', '(بدون پروفایل)');
                                        }
                                        return Html::decode($model->userProfile->firstname . ' ' . $model->userProfile->lastname);
                                    },
                                ],
                                    [
                                    'attribute' => 'displayName',
                                    'label' => \Yii::t('rabint', 'نام نمایشی'),
                                    'value' => function($model) {
                                        if(!isset($model->userProfile)){
                                            return Yii::t('app', '(بدون پروفایل)');
                                        }
                                        return $model->userProfile->nickname;
                                    },
                                ],
                                    [
                                    'attribute' => 'role',
                                    'label' => \Yii::t('rabint', 'نقش کاربری'),
                                    'class' =>\rabint\components\grid\EnumColumn::className(),
                                    'enum' => $roles,
                                    'value' => function($model) {
                                        return rabint\helpers\user::roleTitle($model->id);
                                    },
                                ],
                                    [
                                    'attribute' => 'gender',
                                    'label' => \Yii::t('rabint', 'جنسیت'),
                                    'class' => \rabint\components\grid\EnumColumn::className(),
                                    'enum' => [
                                        1 => Yii::t('rabint', 'مرد'),
                                        2 => Yii::t('rabint', 'زن'),
                                    ],
                                    'value' => function($model) {
                                        if(!isset($model->userProfile)){
                                            return Yii::t('app', '(بدون پروفایل)');
                                        }
                                        return $model->userProfile->gender;
                                    },
                                ],
                                    [
                                    'class' => \rabint\components\grid\JDateColumn::className(),
                                    'attribute' => 'created_at',
                                ],
                                    [
                                    'class' => \rabint\components\grid\JDateColumn::className(),
                                    'attribute' => 'logged_at',
                                ],
//                                [
//                                    'attribute' => 'wallet',
//                                    'label' => \Yii::t('rabint', 'موجودی حساب'),
//                                    'value' => function($model) {
//                                        return number_format(\rabint\finance\models\FinanceWallet::credit($model->id)) . ' ' . \Yii::t('rabint', 'تومان');
//                                        ;
//                                    },
//                                    'visible' => class_exists('rabint\finance\models\FinanceWallet')
//                                ],
                                    [
                                    'attribute' => 'status',
                                    'class' => \rabint\components\grid\EnumColumn::className(),
                                    'enum' => \yii\helpers\ArrayHelper::getColumn(User::statuses(), 'title'),
                                ],
                                    [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view} {update} {delete} {organ} {loginAs} {permission}',
                                    'buttons' => [
                                        'loginAs' => function ($url, $model) {
//                                            if($model->id == rabint\helpers\user::id() or !rabint\helpers\user::can('loginAs'))return;
//                                            if(rabint\helpers\user::role($model->id)=='administrator')return;
                                            
                                            $url = \yii\helpers\Url::to(['/user/default/login-as', 'uid' => $model->id]);
                                            return Html::a('<i class="fas fa-sign-in-alt"></i>', $url, [
                                                        'title' => Yii::t('rabint', 'ورود به سیستم با این کاربر'), 'target' => '_BLANK']);
                                        },
                                        'access' => function ($url, $model) {
                                            $url = \yii\helpers\Url::to(['/admin-access/set-user-access', 'username' => $model->username]);
                                            return Html::a('<span class="glyphicon glyphicon-lock"></span>', $url, [
                                                        'title' => Yii::t('rabint', 'تنظیم دسترسی ها'), 'target' => '_BLANK']);
                                        },
                                        'delete' => function ($url, $model) {
                                            if(!Yii::$app->user->can('manageَUsers') )return false;
                                            $url = \yii\helpers\Url::to(['admin/delete', 'id' => $model->id]);
                                            if(rabint\helpers\user::role($model->id)=='administrator')
                                                return '';
                                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                                        'title' => Yii::t('rabint', 'حذف کاربر '),
                                                    'data'=>[
                                                        'method' => 'post',
                                                        'params'=>[
                                                            'id' => $model->id,
                                                        ],
                                                    ]

                                            ]);
                                        },
                                        'permission' => function ($url, $model) {
                                            if(!Yii::$app->user->can('manageَUsers') )return false;
                                            $url = \yii\helpers\Url::to(['admin/update-permissions', 'id' => $model->id]);
                                            if(rabint\helpers\user::role($model->id)=='administrator')
                                                return '';
                                            return Html::a('<span class="fa fa-archive"></span>', $url, [
                                                        'title' => Yii::t('rabint', 'مشاهده دسترسی ها'),
                                                    ]

                                            );
                                        },
//                                        'organ' => function ($url, $model) {
//                                            if(rabint\helpers\user::role($model->id)=='administrator' or $model->id == yii::$app->user->id)
//                                                return '';
//                                            $url = \yii\helpers\Url::to(['/user_group/admin-user-grade/set-user-access', 'uid' => $model->id]);
//                                            return Html::a('<span class="glyphicon glyphicon-lock"></span>', $url, [
//                                                        'title' => Yii::t('rabint', 'تنظیم دسترسی ها'),'target' => '_BLANK',
//                                            ]);
//                                        },
                                    ]
                                ],
                            ],
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
