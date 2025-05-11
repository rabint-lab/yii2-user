<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model rabint\user\models\User */

$this->title = \Yii::t('app', 'نمایش اطلاعات کاربر') . ' «' . $model->displayName . '»';
$this->params['breadcrumbs'][] = ['label' => Yii::t('rabint', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isModalAjax = Yii::$app->request->isAjax;
?>
<div class="user-view">

    <br/>
    <p>
        <?php echo Html::a(Yii::t('rabint', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php
        //        echo Html::a(Yii::t('rabint', 'Delete'), ['delete', 'id' => $model->id], [
        //            'class' => 'btn btn-danger',
        //            'data' => [
        //                'confirm' => Yii::t('rabint', 'Are you sure you want to delete this user?'),
        //                'method' => 'post',
        //            ],
        //        ])
        ?>
    </p>
    <br/>
    <div class="row">
        <div class="col-12 col-sm-12 col-lg-6">

            <div class="card block block-rounded <?= $isModalAjax ? 'ajaxModalBlock' : ''; ?> ">
                <div class="card-header block-header block-header-default">
                    <h3 class="block-title">
                        <?= Html::encode(Yii::t('rabint', "اطلاعات کاربر")) ?>
                    </h3>
                </div>
                <div class="card-body block-content block-content-full">
                    <?php
                    echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'avatar',
                                'label' => \Yii::t('rabint', 'نمایه'),
                                'format' => 'image',
                                'value' => $model->userProfile->avatar,
                            ],
                            'id',
                            'username',
                            'access_token',
                            [
                                'attribute' => 'status',
                                'value' => rabint\user\models\User::statuses()[$model->status]['title'],
                            ],
                            [
                                'attribute' => 'level',
                                'value' => \common\models\User::levels()[$model->level]['title'],
                            ],
                            [
                                'attribute' => 'displayName',
                                'label' => \Yii::t('rabint', 'نام نمایشی (نام پروفایل)'),
                                'value' => $model->displayName,
                            ],
                            [
                                'label' => \Yii::t('rabint', 'نام و نام خانوادگی'),
                                'value' => Html::decode($model->userProfile->firstname . ' ' . $model->userProfile->lastname),
                            ],
//            'auth_key',
                            'email:email',

                            [
                                'attribute' => 'locale',
                                'label' => \Yii::t('rabint', 'زبان'),
                                'value' => (isset($model->userProfile->locale)) ? Yii::$app->params['availableLocales'][rabint\helpers\locality::baseLang($model->userProfile->locale)]['title'] : NULL,
                            ],
                            [
                                'attribute' => 'gender',
                                'label' => \Yii::t('rabint', 'جنسیت'),
                                'value' => ($model->userProfile->gender) ? rabint\user\models\UserProfile::genders()[$model->userProfile->gender]['title'] : NULL,
                            ],
                            [
                                'attribute' => 'phone',
                                'label' => \Yii::t('rabint', 'تلفن ثابت'),
                                'value' => $model->userProfile->phone,
                            ],
                            [
                                'attribute' => 'cell',
                                'label' => \Yii::t('rabint', 'تلفن همراه'),
                                'value' => $model->userProfile->cell,
                            ],
                            [
                                'attribute' => 'description',
                                'label' => Yii::t('rabint', 'توضیحات کانال'),
                                'value' => $model->userProfile->description,
                            ],
                            [
                                'attribute' => 'brithdate',
                                'label' => \Yii::t('rabint', 'تاریخ تولد'),
                                'value' => ($model->userProfile->brithdate) ? \rabint\helpers\locality::jdate('j F Y - H:i', $model->userProfile->brithdate) : NULL,
                            ],
                            [
                                'attribute' => 'melli_code',
                                'label' => Yii::t('rabint', 'شماره ملی/کد ثبت/شماره معرفی نامه'),
                                'value' => $model->userProfile->melli_code,
                            ],
                            [
                                'attribute' => 'education',
                                'label' => Yii::t('rabint', 'تحصیلات'),
                                'value' => $model->userProfile->education,
                            ],
                            [
                                'attribute' => 'address',
                                'label' => Yii::t('rabint', 'آدرس'),
                                'value' => $model->userProfile->address,

                            ],
                        ],
                    ]);
                    ?>

                </div>
            </div>
            <div class="card block block-rounded <?= $isModalAjax ? 'ajaxModalBlock' : ''; ?> ">
                <div class="card-header block-header block-header-default">
                    <h3 class="block-title">
                        <?= Html::encode(Yii::t('rabint', "دیگر اطلاعات کاربر")) ?>
                    </h3>
                </div>
                <div class="card-body block-content block-content-full">
                    <table class="table table-striped table-bordered detail-others-view">
                        <tbody>
                        <?php
                        $filld = false;
                        $json = json_decode($model->userProfile->others ?: '[]', 1);
                        $other_fileds = config('SERVICE.user.other_profile_fields', []);
                        $other_fileds = $other_fileds();
                        //            var_dump($other_fileds);
                        foreach ($json as $k => $v) {
                            $filld = true;
                            $k = strtolower($k);
                            $title = $other_fileds[$k] ?? $k;
                            echo "<tr><th>$title</th><td>$v</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                    <?php
                    if (!$filld) {
                        echo '<i>' . Yii::t('app', 'اطلاعاتی برای این کاربر ثبت نشده است') . '</i>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-lg-6">

            <div class="card card-warning card-solid">
                <div class="card-header with-border">
                    <h3 class="card-title"><?= Yii::t('rabint', 'Stat') ?></h3>
                </div><!-- /.card-header -->
                <div class="card-body no-padding">
                    <ul class="">
                        <!--                        <li>-->
                        <!--                            <a href="#">-->
                        <!--                                --><?php //= Yii::t('rabint', 'author') ?>
                        <!--                                <span class="pull-left float-left">-->
                        <?php //= $model->displayName ?><!--</span>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                        <li>
                            <a href="#">
                                <?= Yii::t('rabint', 'تاریخ ثبت نام') ?>
                                <span class="pull-left float-left"><?= \rabint\helpers\locality::jdate('j F Y - H:i', $model->created_at) ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <?= Yii::t('rabint', 'تاریخ آخرین ورود') ?>
                                <span class="pull-left float-left"><?= \rabint\helpers\locality::jdate('j F Y - H:i', $model->logged_at); ?></span>
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <?= Yii::t('rabint', 'تعداد بازدید پروفایل') ?>
                                <span class="pull-left float-left badge bg-blue"><?= $model->userProfile->channel_visit ?></span>
                            </a>
                        </li>
                        <?php if (class_exists('app\modules\post\models\Post')) { ?>

                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'تعداد مطالب ارسالی') ?>
                                    <span class="pull-left float-left badge bg-green"><?= app\modules\post\models\Post::find()->where(['user_id' => $model->id])->count(); ?></span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'تعداد مطالب ارسالی 30 روز اخیر') ?>
                                    <span class="pull-left float-left badge bg-green-active">
                                <?=
                                app\modules\post\models\Post::find()
                                    ->andWhere(
                                        [
                                            'user_id' => $model->id,
                                        ]
                                    )->andWhere(['>=', 'created_at', (time() - rabint\cheatsheet\Time::SECONDS_IN_A_MONTH)])
                                    ->count("*");
                                ?>
                            </span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'تعداد مطالب ارسالی 7 روز اخیر') ?>
                                    <span class="pull-left float-left badge bg-green-gradient">
                                <?=
                                app\modules\post\models\Post::find()
                                    ->andWhere(
                                        [
                                            'user_id' => $model->id,
                                        ]
                                    )->andWhere(['>=', 'created_at', (time() - rabint\cheatsheet\Time::SECONDS_IN_A_WEEK)])
                                    ->count("*");
                                //                                echo $res->createCommand()->rawSql;
                                ?>
                            </span>
                                </a>
                            </li>

                            <!--                    <li>
                                            <a href="#">
                    <?php //= Yii::t('rabint', 'تعداد فایل آپلود شده') ?>
                                                <span class="pull-left float-left badge bg-gray-active">
                    <?php //= \rabint\cdn\models\Attachment::find()->where(['user_id' => $model->id])->count(); ?></span>
                                            </a>
                                        </li>-->
                            <?php $sum = 0; ?>
                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'تعداد عکس ارسال شده') ?>
                                    <span class="pull-left float-left badge bg-green-gradient">
                                <?=
                                $t = Yii::$app->db
                                    ->createCommand("SELECT count(*) FROM `pst_attachment` "
                                        . "WHERE post_id in "
                                        . "(SELECT id FROM `pst_post` WHERE `format` = "
                                        . \app\modules\post\models\Post::FORMAT_IMAGE . " "
                                        . " AND user_id = {$model->id} )"
                                    )
                                    ->queryScalar();
                                ?>
                                <?php $sum += $t; ?>
                            </span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'تعداد ویدئو ارسال شده') ?>
                                    <span class="pull-left float-left badge bg-green-gradient">
                                <?=
                                $t = Yii::$app->db
                                    ->createCommand("SELECT count(*) FROM `pst_attachment` "
                                        . "WHERE post_id in "
                                        . "(SELECT id FROM `pst_post` WHERE `format` = "
                                        . \app\modules\post\models\Post::FORMAT_VIDEO . " "
                                        . " AND user_id = {$model->id} )"
                                    )
                                    ->queryScalar();
                                ?>
                                <?php $sum += $t; ?>
                            </span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'تعداد صوت ارسال شده') ?>
                                    <span class="pull-left float-left badge bg-green-gradient">
                                <?=
                                $t = Yii::$app->db
                                    ->createCommand("SELECT count(*) FROM `pst_attachment` "
                                        . "WHERE post_id in "
                                        . "(SELECT id FROM `pst_post` WHERE `format` = "
                                        . \app\modules\post\models\Post::FORMAT_AUDIO . " "
                                        . " AND user_id = {$model->id} )"
                                    )
                                    ->queryScalar();
                                ?>
                                <?php $sum += $t; ?>
                            </span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'تعداد مقاله ارسال شده') ?>
                                    <span class="pull-left float-left badge bg-green-gradient">
                                <?=
                                $t = app\modules\post\models\Post::find()
                                    ->andWhere(
                                        [
                                            'user_id' => $model->id,
                                            'format' => \app\modules\post\models\Post::FORMAT_ARTICLE,
                                        ]
                                    )
                                    ->count("*");
                                ?>
                                <?php $sum += $t; ?>
                            </span>
                                </a>
                            </li>

                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'مجموع فایل ها') ?>
                                    <span class="pull-left float-left badge bg-green-gradient">
                                <?= $sum; ?>
                            </span>
                                </a>
                            </li>
                        <?php } ?>

                    </ul>
                </div><!-- /.card-body -->
            </div><!-- /.card -->
            <?php if (class_exists('\rabint\finance\models\FinanceWallet')) {
                $cash = \rabint\finance\models\FinanceWallet::cash($model->id);
                ?>
                <div class="spacer"></div>
                <div class="card card-success card-solid">
                    <div class="card-header with-border">
                        <h3 class="card-title"><?= Yii::t('rabint', 'اطلاعات مالی') ?></h3>
                    </div><!-- /.card-header -->
                    <div class="card-body no-padding">
                        <ul class="">
                            <li>
                                <a href="#">
                                    <?= Yii::t('rabint', 'موجودی') ?>
                                    <span class="pull-left float-left"><?= number_format($cash); ?> <?=\Yii::t('app','ریال');?></span>
                                </a>
                            </li>
                        </ul>

                    </div><!-- /.card-body -->
                </div><!-- /.card -->
            <?php } ?>
        </div>
    </div>
</div>