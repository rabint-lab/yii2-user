<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

$this->context->layout = '@themeLayouts/login';
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\Form\ActivationForm */

$this->title = Yii::t('rabint', 'فعالسازی حساب کاربری');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax) ? ['role' => "modal-remote"] : [];
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-login">

    <div class="col-sm-12">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?php echo Html::activeHiddenInput($model, 'redirect') ?>
        <?php echo Html::activeHiddenInput($model, 'identity') ?>
        <div class="form-group disabledIdentityLogin">
            <label for="loginform-identity"><?=$model->attributeLabels()['identity'];?></label>
            <input type="text" value="<?=$model->identity;?>"  disabled="disabled"  class="form-control ltrCenter ">
            <a href="<?=\yii\helpers\Url::to(['login'])?>" class="go_back_login">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 8c137 0 248 111 248 248S393 504 256 504 8 393 8 256 119 8 256 8zm113.9 231L234.4 103.5c-9.4-9.4-24.6-9.4-33.9 0l-17 17c-9.4 9.4-9.4 24.6 0 33.9L285.1 256 183.5 357.6c-9.4 9.4-9.4 24.6 0 33.9l17 17c9.4 9.4 24.6 9.4 33.9 0L369.9 273c9.4-9.4 9.4-24.6 0-34z"/></svg>
            </a>
        </div>

        <?php echo $form->field($model, 'token', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control ltrCenter"]])->input('number'); ?>
        <div class="form-group center">
            <?php echo Html::submitButton(Yii::t('rabint', 'فعال سازی'), ['class' => 'btn btn-success', 'name' => 'login-button']) ?>
        </div>
        <div class="form-group center" style="color:#999;margin:1em 0">
            <?php echo Html::a(Yii::t('rabint', 'ارسال دوباره کد فعال سازی'),['activation','identity'=>$model->identity,'redirect'=>$model->redirect],$linkOptions) ?>
        </div>
        <?php ActiveForm::end(); ?>

        <style>
            .disabledIdentityLogin {
                position: relative;
            }

            .go_back_login {
                position: absolute;
                width: 25px;
                bottom: 13px;
                right: 10px;
            }

            .go_back_login svg {
                fill: #8c8c8c;
                transition: ease all .5s;
            }

            .go_back_login svg:hover {
                fill: #4e9aec;
            }

            .disabledIdentityLogin input {
                background: #f0f0f0 !important;
            }
        </style>

    </div>
</div>