<?php

use rabint\rbac\AutoMigration;
use rabint\rbac\rule\OwnModelRule;
use rabint\user\models\User;

class m201116_085831_user_management_rbac extends AutoMigration
{


    public function roles()
    {
        return [
//           'support' => [
//                'description' => \Yii::t('rabint', 'کاربر پشتیبان'),
//                'children' => [User::ROLE_USER],
//            ],
        ];
    }

    public function permissions()
    {
        return [
            'manageَUsers' => [
                'description' => \Yii::t('rabint', 'مدیریت کاربران'),
                'parents' => [User::ROLE_MANAGER],
                'data' => ['group'=>Yii::t('rabint','کاربری')]
            ],
            'viewUsers' => [
                'description' => \Yii::t('rabint', 'مشاهده لیست کاربران'),
                'parents' => [User::ROLE_MANAGER],
                'data' => ['group'=>Yii::t('rabint','کاربری')]
            ],
            'createUsers' => [
                'description' => \Yii::t('rabint', 'افزودن کاربر جدید'),
                'parents' => [User::ROLE_MANAGER],
                'data' => ['group'=>Yii::t('rabint','کاربری')]
            ],
            'loginAs' => [
                'description' => \Yii::t('rabint', 'ورود به کاربری سایرین'),
                'parents' => [User::ROLE_MANAGER],
                'data' => ['group'=>Yii::t('rabint','کاربری')]
            ]
        ];
    }

    public function rules()
    {
        return [
//            'updateOnwModel' => [
//                'class' => OwnModelRule::class,
//                'objConfig' => ['name' => 'updateOnwModel'],
//                'description' => \Yii::t('rabint', 'ویرایش آیتم های خود'),
//                'parents' => [
//                    User::ROLE_AUTHOR
//                ],
//            ],
//            'publishOnwModel' => [
//                'class' => OwnModelRule::class,
//                'objConfig' => ['name' => 'publishOnwModel'],
//                'description' => \Yii::t('rabint', 'انتشار آیتم های خود'),
//                'parents' => [
//                    User::ROLE_AUTHOR
//                    /* , User::ROLE_CONTRIBUTOR, User::ROLE_AUTHOR, User::ROLE_EDITOR */
//                ],
//            ],
        ];
    }

    public function seeds()
    {
        return [
//            ['role', 1, User::ROLE_ADMINISTRATOR],
//            ['role', 2, User::ROLE_MANAGER],
//            ['role', [3], User::ROLE_USER],
            //['permission', [1,2], 'loginToBackend'],
            //['rule', User::ROLE_USER, User::RULE_USER_OWN_MODEL],
        ];
    }
}
