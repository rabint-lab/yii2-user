<?php

use yii\bootstrap4\Nav;

//$this->context->layout = '@theme/views/layouts/common';
/* @var $this yii\web\View */
/* @var $searchModel app\modules\post\models\search\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $user rabint\user\models\User */

$this->context->layout = "@themeLayouts/full";
//$this->title =  Yii::t('rabint', \Yii::t('rabint', 'پروفایل {user}', ['user' => $user->displayName]));
$this->title = Yii::t('rabint', \Yii::t('rabint', 'پیشخوان من : {user}', ['user' => $user->displayName]));
//$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'پروفایل ها'), 'url' => ['channels']];
$this->params['breadcrumbs'][] = $this->title;
/* =================================================================== */
?>
<?php
if ($act = \rabint\user\Module::getConfig('inner_dashboard_action')) {
    echo Yii::$app->runAction($act, ['nolayout' => true]);
}
?>
<div class="container">
    <div class="row mt-3 Cardss">
        <!-- <div class="spacer"></div> -->

        <?php

        $menusConf = include Yii::getAlias("@app/config/menus.php");


        $ModuleMenu = [];
        //    if (!empty($menusConf['dashboard'])) {
        //        $ModuleMenu[] = $menusConf['dashboard'];
        //    }
        $modules = include(Yii::getAlias('@app/config/modules.php'));
        $userMenu = null;
        foreach ((array)$modules as $item) {
            $moduleClass = $item['class'];
            if (strpos($moduleClass, 'rabint\user\Module') !== false) {
                $userMenu = call_user_func([$moduleClass, 'dashboardMenu']);
                continue;
            }
            if (method_exists($moduleClass, 'dashboardMenu')) {
                $ModuleMenu[] = call_user_func([$moduleClass, 'dashboardMenu']);
            }
        }

        ?>

        <?php

        foreach ($ModuleMenu as $menu) {
            if (empty($menu)) {
                continue;
            }
            foreach ($menu['items'] as &$item) {
                $item['linkOptions'] = ['class'=>'btn btn-success btn-sm'];
            }
            ?>
            <div class="col-sm-12 col-lg-6 CardDashboard">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= $menu['icon'] ?? '<i class="far fa-circle"></i>'; ?><?= $menu['label'] ?? ''; ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= $menu['hit'] ?? ""; ?></h6>
                        <?php
                        echo Nav::widget([
                            'options' => ['class' => 'card-menu'],
                            'items' => $menu['items'] ?? []
                        ]);
                        ?>
                        <!-- <a href="#" class="card-link">Another link</a> -->
                    </div>
                </div>
                <div class="spacer"></div>

            </div>
        <?php } ?>
        <?php if (!empty($userMenu)) {
            foreach ($userMenu['items'] as &$item) {
                $item['linkOptions'] = ['class'=>'btn btn-success btn-sm'];
            }
            ?>
            <div class="col-sm-12 col-md-12 CardDashboard2">

                <div class="card  bg-light special_card">
                    <div class="card-body">
                        <h5 class="card-title"><?= $userMenu['icon'] ?? '<i class="far fa-circle"></i>'; ?><?= $userMenu['label'] ?? ''; ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= $userMenu['hit'] ?? ""; ?></h6>
                        <?php
                        echo Nav::widget([
                            'options' => ['class' => 'card-menu'],
                            'items' => $userMenu['items'] ?? []
                        ]);
                        ?>
                        <!-- <a href="#" class="card-link">Another link</a> -->
                    </div>
                </div>
                <div class="spacer"></div>

            </div>
        <?php } ?>

    </div>
</div>	
