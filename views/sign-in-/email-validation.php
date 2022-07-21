<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \rabint\user\models\PasswordResetRequestForm */

$this->title =  Yii::t('rabint', 'فعال سازی ایمیل');
$this->params['breadcrumbs'][] = $this->title;
$linkOptions = (Yii::$app->request->isAjax)?['role'=>"modal-remote"]:[];
?>
<h2 class="ajaxModalTitle" style="display: none"><?= $this->title; ?></h2>
<div class="site-request-password-reset">
    <p class="regHelp">
        <?= \Yii::t('rabint', 'کاربر گرامی!<br/> برای شما ایمیلی حاوی کد فعال سازی ارسال گردیده است. لطفا به ایمیل خود رجوع نموده و بروی لینک مورد نظر کلیک نمایید.'); ?>
    </p>
</div>
