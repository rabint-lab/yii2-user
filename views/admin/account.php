<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model rabint\user\models\UserProfile */
/* @var $form yii\bootstrap4\ActiveForm */
$this->title = Yii::t('rabint', 'Edit account')
?>

<div class="user-profile-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'username') ?>

    <?php echo $form->field($model, 'email') ?>

    <?php echo $form->field($model, 'password')->passwordInput() ?>

    <?php echo $form->field($model, 'password_confirm')->passwordInput() ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('rabint', 'Update'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
