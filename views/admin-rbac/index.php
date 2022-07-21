<?php

use common\grid\EnumColumn;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $roles */

$this->title = Yii::t('rabint', 'نقش های کاربری');
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
                            <?= Html::a(Yii::t('rabint', 'ایجاد رول جدید'), ['detail'], ['class' => 'btn btn-success btn-xs btn-flat']) ?>
                        </h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="user-index">
                        <table class="table table-striped table-bordered"><thead>
                            <tr>
                                <th>
                                    <?=\Yii::t('app','عنوان');?>
                                </th>
                                <th>
                                    <?=\Yii::t('app','توضیحات');?>
                                </th>
                                <th>
                                    <?=\Yii::t('app','عملیات');?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($roles as $role) {
                                ?>
                                <tr>
                                    <td><?=$role->name?></td>
                                    <td><?=$role->description?></td>
                                    <td>
                                        <a href="<?=url(['detail','name'=>$role->name])?>" class="remove_role" >
                                            <i class="glyphicon glyphicon-pencil"></i>
                                        </a>
                                        <?php
                                        if(!\rabint\user\models\AdminRoleForm::isSystemRole($role->name)){?>
                                            &nbsp;&nbsp;
                                            <a href="<?=url(['delete','name'=>$role->name])?>" onclick="return confirm('آیا اطمینان به حذف این مورد دارید؟')">
                                                <i class="glyphicon glyphicon-trash"></i>
                                            </a>
                                        <?php }?>
                                    </td>
                                </tr>
                            <?php
                            }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
