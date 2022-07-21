<?php

namespace rabint\user\models;

use rabint\cheatsheet\Time;
use rabint\commands\SendEmailCommand;
use rabint\user\models\User;
use rabint\user\models\UserToken;
use rabint\user\Module;
use yii\base\Exception;
use yii\base\Model;
use Yii;
use yii\helpers\Url;
use rabint\helpers\str;
use rabint\notify\models\Notification;

/**
 * Signup form
 */
class SignupForm extends Model
{

    public $redirect="";
    public $username;
    public $name;
    public $family;
    public $email;
    public $password;
    public $confirm;
    public $legal;
    public $verifyCode;

    public function rules()
    {
        $return = [
            ['redirect', 'safe'],
            [['password', 'confirm', 'username', 'legal', 'name', 'family'], 'required'],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],

            ['email', 'filter', 'filter' => 'trim'],

            ['email', 'email'],
            [
                'email', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'This email has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ],
            [['name', 'family'], 'string', 'max' => 45],
            ['confirm', 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('rabint', "تکرار کلمه عبور صحیح نیست")],
            ['legal', 'integer', 'min' => 1, 'max' => 1, 'tooBig' => \Yii::t('rabint', 'شما باید قوانین را بپذیرید'), 'tooSmall' => \Yii::t('rabint', 'شما باید قوانین را بپذیرید')],
            ['verifyCode', 'captcha'],
        ];

        /**
         * cell validations
         */
        if (\rabint\user\Module::$cellBaseAuth) {
            $return[] = ['username', 'match', 'pattern' => '/^[0-9\+]\w*$/i', 'message' => Yii::t('rabint', 'لطفا شماره تلفن همراه وارد فرمایید.')];
            $return[] = ['username', 'string', 'min' => 10, 'max' => 13];
            $return[] = [
                'username', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'این شماره تلفن همراه قبلا ثبت شده است.'),
                'filter' => function ($query) {
                    $query->orWhere(['username' => str::formatCellphone($this->username)]);
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ];
        } else {
            /**
             * mail validations
             */
            $return[] = ['username', 'match', 'pattern' => '/^[a-zA-z0-9_]\w*$/i', 'message' => Yii::t('rabint', 'فقط حروف لاتین ، اعداد و خط زیر مجاز است')];
            $return[] = ['username', 'string', 'min' => 5, 'max' => 255];
            $return[] = ['email', 'required'];
            $return[] = [
                'username', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'This username has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ];
        }

        /**
         * password policies
         */
        if (Module::$passwordPolicy == "strong") {
            $return[] = ['password', 'match', 'pattern' => '/^((?=.*[0-9])(?=.*[a-z]))|((?=.*[۱۲۳۴۵۶۷۸۹۰])).+$/i', 'message' => Yii::t('rabint', ' کلمه عبور باید شامل حروف و عدد بوده و حداقل 8 حرف طول داشته باشد')];
            $return[] = [['password', 'confirm'], 'string', 'min' => 8];
        } elseif (Module::$passwordPolicy == "medium") {
            $return[] = ['password', 'match', 'pattern' => '/^((?=.*[0-9])(?=.*[a-z]))|((?=.*[۱۲۳۴۵۶۷۸۹۰])).+$/i', 'message' => Yii::t('rabint', ' کلمه عبور باید شامل حروف و عدد بوده و حداقل 6 حرف طول داشته باشد')];
            $return[] = [['password', 'confirm'], 'string', 'min' => 6];
        } elseif (Module::$passwordPolicy == "cheap") {
            $return[] = [['password', 'confirm'], 'string', 'min' => 5];
        } else {
            $return[] = [['password', 'confirm'], 'string', 'min' => 6];
        }
        return $return;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => (\rabint\user\Module::$cellBaseAuth) ? Yii::t('rabint', 'تلفن همراه') : Yii::t('rabint', 'نام کاربری'),
            'email' => Yii::t('rabint', 'E-mail'),
            'name' => Yii::t('rabint', 'نام'),
            'family' => Yii::t('rabint', 'نام خانوادگی'),
            'password' => Yii::t('rabint', 'Password'),
            'confirm' => Yii::t('rabint', 'تکرار رمز'),
            'legal' => Yii::t('rabint', 'شرایط و قوانین سایت را می پذیرم'),
            'verifyCode' => Yii::t('rabint', 'کد امنیتی'),
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $shouldBeActivated = $this->shouldBeActivated();
            $user = new User();
            if (\rabint\user\Module::$cellBaseAuth) {
                $user->username = str::formatCellphone($this->username);
            } else {
                $user->username = $this->username;
            }
            $user->email = $this->email;
            $user->status = $shouldBeActivated ? User::STATUS_NOT_ACTIVE : User::STATUS_ACTIVE;
            $user->setPassword($this->password);
            if (!$user->save(false)) {
                throw new Exception("User couldn't be  saved");
            }
            if (\rabint\user\Module::$cellBaseAuth) {
                $user->afterSignup(['cell' => str::formatCellphone($this->username, "+98"), 'firstname' => $this->name, 'lastname' => $this->family]);
            } else {
                $user->afterSignup(['firstname' => $this->name, 'lastname' => $this->family]);
            }
            if ($shouldBeActivated) {
                $this->sendActivation($user);
            }
            return $user;
        }

        return null;
    }


    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendActivation($user)
    {
        return UserToken::sendActivation($user, UserToken::TYPE_ACTIVATION);
//        if (\rabint\user\Module::$cellBaseAuth) {
//            return $this->sendSms($user);
//        } else {
//            return $this->sendEmail($user);
//        }
    }


//    public function sendSms($user)
//    {
//        /**
//         * check user not has active token
//         */
//        if (UserToken::userHasActiveToken($user->id, UserToken::TYPE_SMS_ACTIVATION)) {
//            return false;
//        }
//
//        $token = UserToken::create($user->id, UserToken::TYPE_SMS_ACTIVATION);
//        if (empty($token)) {
//            return false;
//        }
//        کد فعال سازی شما:
//
//
//        $message = <<<EOT
//کد فعال سازی شما:
//$token
//EOT;
//        return Yii::$app->notify->send($user->id, $message, '', [
//            'priority' => Notification::MEDIA_SMS
//        ]);
//    }
//
//
//    public function sendEmail($user)
//    {
//
//        $token = UserToken::create(
//            $user->id,
//            UserToken::TYPE_ACTIVATION,
//            Time::SECONDS_IN_A_DAY
//        );
//        return  Yii::$app->commandBus->handle(new SendEmailCommand([
//            'subject' => Yii::t('rabint', 'Activation email'),
//            'view' => 'activation',
//            'to' => $this->email,
//            'params' => [
//                'url' => Url::to(['/user/sign-in/activation', 'token' => $token->token], true)
//            ]
//        ]));
//    }

    /**
     * @return bool
     */
    public function shouldBeActivated()
    {
        return \rabint\user\Module::$shouldBeActivated;
        // /** @var Module $userModule */
        // $userModule = Yii::$app->getModule('user');
        // if (!$userModule) {
        //     return false;
        // } elseif ($userModule->shouldBeActivated) {
        //     return true;
        // } else {
        //     return false;
        // }
    }
}
