<?php

namespace rabint\user\models;

use rabint\cheatsheet\Time;
use rabint\user\models\User;
use rabint\user\models\UserToken;
use common\commands\SendEmailCommand;
use Yii;
use yii\base\Model;
use rabint\helpers\str;
use rabint\helpers\uri;
use rabint\notify\models\Notification;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{

    /**
     * @var user email
     */
    public $redirect = "";
    public $username;
    public $email;
    public $verifyCode;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        if (\rabint\user\Module::$cellBaseAuth) {
            return [
                ['redirect', 'safe'],
                ['username', 'filter', 'filter' => 'trim'],
                ['username', 'required'],

                ['username', 'match', 'pattern' => '/^[0-9\+]\w*$/i', 'message' => Yii::t('rabint', 'لطفا شماره تلفن همراه وارد فرمایید.')],
                ['username', 'string', 'min' => 10, 'max' => 13],
                [
                    'username', 'exist',
                    'targetClass' => '\rabint\user\models\User',
                    'message' => Yii::t('rabint', 'حساب کاربری این شماره تلفن موجود نیست و یا غیر فعال شده است.'),
                    'filter' => function ($query) {
                        $query->orWhere(['username' => str::formatCellphone($this->username)]);
                        //$query->andWhere(['status' => User::STATUS_ACTIVE]);
                    }
                ],
                ['verifyCode', 'captcha'],
            ];
        } else {
            return [
                ['email', 'filter', 'filter' => 'trim'],
                ['email', 'required'],
                ['email', 'email'],
                [
                    'email', 'exist',
                    'targetClass' => '\rabint\user\models\User',
                    'filter' => ['status' => User::STATUS_ACTIVE],
                    'message' => \Yii::t('rabint', 'هیج حساب کاربری معتبری برای این ایمیل یافت نشد.')
                ],
                ['verifyCode', 'captcha'],
            ];
        }
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendActivation()
    {
        if (\rabint\user\Module::$cellBaseAuth) {
            $user = User::findOne([
                'status' => User::STATUS_ACTIVE,
                'username' => str::formatCellphone($this->username),
            ]);
        } else {
            $user = User::findOne([
                'status' => User::STATUS_ACTIVE,
                'email' => $this->email,
            ]);
        }
        if ($user === null) {
            return false;
        }
        return UserToken::sendActivation($user, UserToken::TYPE_ACTIVATION);
    }

//    public function sendSms($type = '')
//    {
//        /* @var $user User */
//        $user = User::findOne([
//            //'status' => User::STATUS_ACTIVE,
//            'username' => str::formatCellphone($this->username),
//        ]);
//
//        if ($user === null) {
//            return false;
//        }
//        /**
//         * check user not has active token
//         */
//        if (UserToken::userHasActiveToken($user->id, $type ? $type : UserToken::TYPE_SMS_ACTIVATION)) {
//            return false;
//        }
//
//        $token = UserToken::create($user->id, $type ? $type : UserToken::TYPE_SMS_ACTIVATION);
//        if (empty($token)) {
//            return false;
//        }
//        $tokenMsg = $token->token;
////        $message = <<<EOT
////کد فعال سازی شما:
////$tokenMsg
////EOT;
////        return Yii::$app->notify->send($user->id, $message, '', [
////            'media' => Notification::MEDIA_SMS
////        ]);
//
//        return Yii::$app->notify->sendVerify($user->id, $tokenMsg, '', [
//            'media' => Notification::MEDIA_SMS
//        ]);
//    }
//
//
//    public function sendEmail()
//    {
//        /* @var $user User */
//        $user = User::findOne([
//            'status' => User::STATUS_ACTIVE,
//            'email' => $this->email,
//        ]);
//
//        if ($user) {
//            $token = UserToken::create($user->id, UserToken::TYPE_PASSWORD_RESET, Time::SECONDS_IN_A_DAY);
//            if ($token) {
//                return Yii::$app->commandBus->handle(new SendEmailCommand([
//                    'to' => $this->email,
//                    'subject' => Yii::t('rabint', 'Password reset for {name}', ['name' => Yii::$app->name]),
//                    'view' => 'passwordResetToken',
//                    'params' => [
//                        'user' => $user,
//                        'token' => $token->token
//                    ]
//                ]));
//            }
//        }
//
//        return false;
//    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('rabint', 'ایمیل'),
            'username' => Yii::t('rabint', 'تلفن همراه'),
            'verifyCode' => Yii::t('rabint', 'کد امنیتی'),

        ];
    }
}
