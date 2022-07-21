<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $title
 * @var $redirect
 */
?>
<div class="site-login popoverLoginBox">
    <!--<div class="row">-->
    <div class="col-sm-12">
        <?php
        $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'action' => \yii\helpers\Url::to(['/user/sign-in/login']),
        ]);
        ?>
        <?php echo $form->field($model, 'redirect', ['template' => "{input}", 'options' => ['tag' => false]])->hiddenInput(['value' => $redirect]); ?>
        <?= $form->field($model, 'identity', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]]) ?>
        <?= $form->field($model, 'password', ['inputOptions' => ['autocomplete' => 'off', 'class' => "form-control"]])->passwordInput() ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>
        <div class="form-group center-block">
            <?= Html::submitButton(Yii::t('rabint', 'ورود'), ['class' => 'btn btn-primary center-block', 'name' => 'login-button']) ?>
        </div>
        <div class="form-group  center">
            <?php echo Html::a(Yii::t('rabint', 'عضویت در سایت'), ['/user/sign-in/signup']) ?>
            | 
            <?php
            echo Yii::t('rabint', '<a href="{link}">بازیابی رمز</a>', [
                'link' => yii\helpers\Url::to(['/user/sign-in/request-password-reset'])
            ])
            ?>
        </div>
        <?php /*
          <h2><?php echo Yii::t('rabint', 'ورود با')  ?>:</h2>
          <div class="form-group">
          <?= yii\authclient\widgets\AuthChoice::widget([
          'baseAuthUrl' => ['/user/sign-in/oauth']
          ]) ?>
          </div> */ ?>
        <?php ActiveForm::end(); ?>
    </div>
    <!--</div>-->
</div>
