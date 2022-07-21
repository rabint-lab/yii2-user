<?php

namespace rabint\user;

use rabint\cheatsheet\Time;
use rabint\helpers\str;
use rabint\helpers\user;
use Yii;

class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'rabint\user\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public static function getConfig($key)
    {
        $defaultConfigs = [
            'baseAuthenticate' => 'mobileEmail', // [mobile,email,mobileEmail,username]
            'tenants' => false, // [mobile,email,mobileEmail,username]
            'mergeRegisterAndLogin' => true, // [mobile,email,mobileEmail,username]
            //'shouldBeActivated' => true,
            'passwordPolicy' => 'normal',//cheap , medium , strong , normal
            'userAgreementPage' => 'path/to/action',
            'defaultLoginRedirect' => '/user/default/index',
            'defaultLogoutRedirect' => '/',
            'sessionExpireTime' => Time::SECONDS_IN_A_MONTH,
            'mustFillProfile' => true,
            'enableRegistration' => true,
            'enableActivation' => true,
            'session_timeout' => 3600 * 8,
            'register_action' => '/user/sign-in/register-form',
            'profile_action' => '/user/default/profile',
            'inner_dashboard_action' => false,
            'show_account_in_dropdown' => true,
            'account_action' => config('dashboardRoute', '/user/default/index'),
            'other_profile_table' => '',// if empty , data saved on user_profile.others
            'enableCaptcha' => false,
            'other_profile_fields' => [ // do not use translate in here
                //['field' => 'fathername', 'title' => 'نام پدر'],
            ]
        ];
        return config('SERVICE.user.' . $key, $defaultConfigs[$key]);
    }

    public static function baseAuthenticateOnEmail()
    {
        $res = self::getConfig('baseAuthenticate');
        if ($res == 'mobileEmail' OR $res == 'email') {
            return true;
        }
        return false;
    }

    public static function baseAuthenticateOnMobile()
    {
        $res = self::getConfig('baseAuthenticate');
        if ($res == 'mobileEmail' OR $res == 'mobile') {
            return true;
        }
        return false;
    }


    public static function dashboardMenu()
    {
        return [
            'label' => \Yii::t('rabint', 'حساب کاربری'),
            'url' => '#',
            'icon' => '<i class="fas fa-user"></i>',
            'hit' => \Yii::t('rabint', 'این بخش مربوط به مدیریت حساب کاربری شما می باشد'),
            'items' => [
                /* [
                     'label' => \Yii::t('rabint', 'پیام خصوصی'),
                     'url' => '#',
                     'visible' => Yii::$app->user->can('contributor'),
                     'icon' => '<i class="fas fa-image"></i>',
                 ],*/
                [
                    'label' => \Yii::t('app', 'ویرایش اطلاعات کاربری'),
                    //'url' => ['/user/default/profile'],
                    'url' => [\rabint\user\Module::getConfig('profile_action')],
                    'visible' => !Yii::$app->user->isGuest,
                    'icon' => '<i class="fas fa-user"></i>',
                ],
                [
                    'label' => \Yii::t('rabint', 'خروج'),
                    'url' => ['/user/sign-in/logout'],
                    'visible' => !Yii::$app->user->isGuest,
                    'icon' => '<i class="fas fa-lock"></i>',
                ],
            ],
        ];
    }


    public static function AdminMenu()
    {
        return [
            [
                'label' => Yii::t('rabint', 'سیستم'),
                'options' => ['class' => 'nav-main-heading'],
                'visible' => \rabint\helpers\user::can('manager'),
            ],
            [
                'label' => \Yii::t('rabint', 'کاربران'),
                'url' => '#',
                'visible' => (Yii::$app->user->can('administrator') || Yii::$app->user->can('manageَUsers') || Yii::$app->user->can('viewUsers') || Yii::$app->user->can('createUsers')),
                'icon' => '<i class="fas fa-user"></i>',
                'items' => [
                    [
                        'label' => \Yii::t('rabint', 'لیست کاربران'),
                        'url' => ['/user/admin/index'],
                        'visible' => (Yii::$app->user->can('administrator') || Yii::$app->user->can('manageَUsers') || Yii::$app->user->can('viewUsers')),
                        'icon' => '<i class="fas fa-users"></i>',
                    ],
//                [
//                    'label' => \Yii::t('rabint', 'افزودن کاربر جدید'),
//                    'url' => ['/user/admin/create-user'],
//                    'visible' => 1,//!Yii::$app->user->isGuest && !Yii::$app->user->can('administrator'),
//                    'icon' => '<i class="fas fa-user-plus"></i>',
//                ],
                    [
                        'label' => \Yii::t('rabint', 'افزودن کاربر جدید'),
                        'url' => ['/user/admin/create'],
                        'visible' => (Yii::$app->user->can('administrator') || Yii::$app->user->can('createUsers')),
                        'icon' => '<i class="fas fa-user-plus"></i>',
                    ],
                    [
                        'label' => \Yii::t('rabint', 'مدیریت نقش های کاربری'),
                        'url' => ['/user/admin-rbac/index'],
                        'visible' => Yii::$app->user->can('administrator'),
                        'icon' => '<i class="fas fa-user-shield"></i>',
                    ],
                ],
            ]];
    }
}
