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

/**
 * Signup form
 */
class FastSignupForm extends Model
{

    public $redirect="";
    public $username;
    public $password;
    public $confirm;
    public $legal;
    public $verifyCode;

    public function rules()
    {
        $return =[
            ['redirect', 'safe'],
            [['password', 'confirm', 'username', 'legal'], 'required'],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            [
                'username', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'This username has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ],
            ['username', 'filter', 'filter' => 'trim'],
            
            [['password', 'confirm'], 'string', 'min' => 6],
            ['password', 'match', 'pattern' => '/^((?=.*[0-9])(?=.*[a-z]))|((?=.*[۱۲۳۴۵۶۷۸۹۰])).+$/i', 'message' => Yii::t('rabint', 'کلمه عبور باید شامل حروف و عدد باشد')],
            ['confirm', 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('rabint', "تکرار کلمه عبور صحیح نیست")],
            ['legal', 'integer', 'min' => 1, 'max' => 1, 'tooBig' => \Yii::t('rabint', 'شما باید قوانین را بپذیرید'), 'tooSmall' => \Yii::t('rabint', 'شما باید قوانین را بپذیرید')],
            ['verifyCode', 'captcha'],
        ];
        if(\rabint\user\Module::$cellBaseAuth){
            $return[]=['username', 'match', 'pattern' => '/^[0-9\+]\w*$/i', 'message' => Yii::t('rabint', 'لطفا شماره تلفن همراه وارد فرمایید.')];
            $return[]=['username', 'string', 'min' => 11, 'max' => 13];
        }else{
            $return[]=['username', 'string', 'min' => 5, 'max' => 255];
            $return[]=['username', 'email'];
            /*$return[]=[
                'username', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'This email has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ];*/
        }
        return $return;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => (\rabint\user\Module::$cellBaseAuth)?Yii::t('rabint', 'تلفن همراه'):Yii::t('rabint', 'نام کاربری'),
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
            $user->username = $this->username;
            $user->email = $this->email;
            $user->status = $shouldBeActivated ? User::STATUS_NOT_ACTIVE : User::STATUS_ACTIVE;
            $user->setPassword($this->password);
            if (!$user->save()) {
                throw new Exception("User couldn't be  saved");
            }
            $user->afterSignup(['firstname' => $this->name, 'lastname' => $this->family]);
            if ($shouldBeActivated) {
                $token = UserToken::create(
                    $user->id,
                    UserToken::TYPE_ACTIVATION,
                    Time::SECONDS_IN_A_DAY
                );
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('rabint', 'Activation email'),
                    'view' => 'activation',
                    'to' => $this->email,
                    'params' => [
                        'url' => Url::to(['/user/sign-in/activation', 'token' => $token->token], true)
                    ]
                ]));
            }
            return $user;
        }

        return null;
    }

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
