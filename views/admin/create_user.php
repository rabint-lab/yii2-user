<?php
/* @var $this yii\web\View */
/* @var $model rabint\user\models\AdminUserForm */
/* @var $roles yii\rbac\Role[] */
$this->title = Yii::t('rabint', 'تعریف کاربر', [
    'modelClass' => 'User',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <?php echo $this->render('_form_user', [
        'model' => $model,
    ]) ?>

</div>
