<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model \rabint\user\models\AdminRoleForm */
/* @var $readonly bool */

$this->context->layout = "@themeLayouts/full";
$this->title = Yii::t('rabint', 'ویرایش نقش کاربری {rolename}', ['rolename' => $model->name]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'نقش های کاربری'), 'url' => ['index']];
?>
<div class="grid-card post-index">
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-info">
                <div class="card-header">
                    <div class="action-card">
                        <h2 class="master-title">
                            <?= Html::encode($this->title) ?>
                        </h2>
                    </div>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                            if ($readonly) {
                                echo '<div class="alert alert-warning">' . Yii::t('app', 'این نقش کاربری سیستمی است و شما نمی توانید نام و دسترسی های این نقش را تغییر دهید ') . '</div>';
                            } ?>
                        </div>
                        <div class="col-sm-12">
                            <?php echo $form->field($model, 'name')
                                ->textInput(['class' => 'form-control', 'disabled' => !$model->isNewRecord])
                                ->hint(!$model->isNewRecord ? "فیلد نام غیر قابل ویرایش است." : "") ?>
                        </div>
                        <div class="col-sm-12">
                            <?php echo $form->field($model, 'description'); ?>
                        </div>
                        <div class="spacer"></div>

                        <div class="col-sm-12">
                            <?php
                            {
                            $allPermision = \yii\helpers\ArrayHelper::map(\Yii::$app->authManager->getPermissions(), 'name', 'description');
                            $allData = \yii\helpers\ArrayHelper::map(\Yii::$app->authManager->getPermissions(), 'name', 'data');
                            foreach ($allData as $key => $value) {
                                if (!isset($value['group']))
                                    $allData[$key]['group'] = 'سایر';
                            }

                            $aData = \yii\helpers\ArrayHelper::getColumn($allData, 'group');
                            foreach ($aData as $key => $value) {
                                $temp[$value][] = $key;
                            }
                            ?>
                            <input type="hidden" name="AdminRoleForm[permisions]" value="[]">
                            <?php
                            foreach ($temp as $key => $value) {
                                $params = \yii\helpers\ArrayHelper::filter($allPermision, $value);
                                ?>
                                <div class="spacer"></div>
                                <div class="form-group field-adminroleform-permisions">
                                    <label><?= Yii::t('rabint', $key) ?></label>
                                    <div class="roleCheckoxlist custom-control custom-checkbox">
                                        <?php foreach ($params as $k => $item) { ?>
                                            <div class="custom-control custom-checkbox">
                                                <input
                                                    <?= $readonly ? 'disabled' : 'name="AdminRoleForm[permisions][]"' ?>
                                                    value="<?= $k ?>" id="i<?= $k ?>" type="checkbox"
                                                    class="custom-control-input"
                                                    <?= in_array($k, $model->permisions) ? 'checked=""' : ''; ?>>
                                                <label class="custom-control-label"
                                                       for="i<?= $k ?>"><?= $item ?></label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>

                            <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="spacer"></div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <?php echo Html::submitButton(Yii::t('rabint', 'Save'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .roleCheckoxlist div {
        float: right;
        margin-left: 40px;
    }
</style>