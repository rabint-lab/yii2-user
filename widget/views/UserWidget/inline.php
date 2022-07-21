<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $title
 * @var $redirect
 */
if (rabint\helpers\user::isGuest()) {
    /* ################################################################### */
    ?>
    <?php
    $form = ActiveForm::begin([
                'id' => 'login-form',
                'action' => \yii\helpers\Url::to(['/user/sign-in/login']),
    ]);
    echo $form->field($model, 'redirect', ['template' => "{input}", 'options' => ['tag' => false]])->hiddenInput(['value' => $redirect]);
    ?>
    <div class="row">

        <div class="col-sm-4 padRight5 padLeft5">
            <?= $form->field($model, 'identity', ['template' => "{input}", 'options' => ['tag' => false]])->textInput(['autocomplete' => 'off', 'class' => 'login-text', 'placeholder' => \Yii::t('rabint', 'نام کاربری')])->label(FALSE); ?>
        </div>
        <div class="col-sm-4 padRight5 padLeft5">
            <?= $form->field($model, 'password', ['template' => "{input}", 'options' => ['tag' => false]])->passwordInput(['autocomplete' => 'off', 'class' => 'login-text', 'placeholder' => \Yii::t('rabint', 'رمز عبور')])->label(FALSE); ?>
        </div>
        <div class="col-sm-4 padRight5 padLeft5">
            <button type="submit" name="loginSubmit" class="login-button" id="loginSubmit"><?= \Yii::t('rabint', 'ورود'); ?></button><div class="clearfix"></div>
        </div>
    </div>    
    <div class="row" style="height: 23px; overflow: hidden;">

        <div class="col-sm-4 padRight5 padLeft5">
            <a href="<?= Url::to(['/user/sign-in/signup']); ?>" class="login-link"><?= \Yii::t('rabint', 'ثبت نام کاربر جدید'); ?></a><div class="clearfix"></div>
        </div>
        <div class="col-sm-4 padRight5 padLeft5">
            <a href="<?= Url::to(['/user/sign-in/request-password-reset']); ?>" class="login-link"><?= \Yii::t('rabint', 'فراموشی کلمه عبور'); ?></a><div class="clearfix"></div>
        </div>
        <div class="col-sm-4 padRight5 padLeft5">  
            <label class="login-link">
                <?= $form->field($model, 'rememberMe', ['template' => '{input}', 'options' => ['tag' => false]])->checkbox(['class' => 'login-link'], false) ?>
                <span><?= \Yii::t('rabint', 'من را به خاطر بسپار'); ?></span>
            </label>
            <div class="clearfix"></div>
        </div> 
    </div>    
    <?php ActiveForm::end(); ?>
    <?php
} else {
    /* ################################################################### */
    ?>

    <div class="pull-right float-right">
        <div class="user-info-total padding10 hidden-xs">
            <div class="pull-right float-right">
                <button class="headerUserMenuBtn" data-toggle="dropdown" type="button">
                    <span class="headerAvatar pull-right float-right" >
                        <img src = "<?php echo Yii::$app->user->identity->userProfile->getAvatar(\yii\helpers\Url::home() . '/img/noAvatarTiny.png', 'tiny') ?>" class = "user-image">
                    </span>
                    <span class="pull-right float-right" >
                        <?= \Yii::t('rabint', 'wellcome {username}', ['username' => rabint\helpers\user::name()]); ?>
                    </span>
                </button>
                <?= rabint\user\widget\UserMenu::widget(['menuClass' => 'dropdown-menu dropdown-menu-right']); ?>
            </div>
        </div>
    </div>
    <div class="pull-right float-right">
        <div class="user-info-online padding10">
            <?= \app\modules\pm\widget\PmWidget::widget(['style' => 'popover']); ?>
        </div>
    </div>
    <div class="pull-right float-right">
        <div class="user-info-online padding10">
            <?= \app\modules\notify\widget\NotifyWidget::widget(['style' => 'popover']); ?>
        </div>
    </div>
    <!-- =================================================================== -->

    <div class="pull-right float-right">
        <div class="user-info-online padding10">
            <a href="<?= Url::to(\rabint\helpers\uri::dashboardRoute()); ?>" class="login-link"><?= \Yii::t('rabint', 'پیشخوان'); ?></a>
        </div>
    </div>
    <div class="pull-right float-right">
        <div class="user-info-online padding10">
            <a href="<?= Url::to(['/user/sign-in/logout']); ?>" class="login-link"><?= \Yii::t('rabint', 'logout'); ?></a>
        </div>
    </div>
<?php } ?>
