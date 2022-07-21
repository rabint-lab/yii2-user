<?php

namespace rabint\user\models;

use rabint\helpers\str;
use rabint\user\Module;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Signup form
 */
class SignupFastForm extends Model
{

    public $redirect = "";
    public $nickname;
    public $username;
    public $password;
    public $confirm;
    public $verifyCode;

    public function rules()
    {
        $return = [
            ['redirect', 'safe'],
            [['password', 'confirm', 'username','nickname'], 'required'],
            ['nickname', 'string'],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['confirm', 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('rabint', "تکرار کلمه عبور صحیح نیست")],
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
            $return[] = ['username', 'email'];
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
            'password' => Yii::t('rabint', 'Password'),
            'confirm' => Yii::t('rabint', 'تکرار رمز'),
            'verifyCode' => Yii::t('rabint', 'کد امنیتی'),
            'nickname' => Yii::t('rabint', 'نام نمایشی'),
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
                $user->email = $this->username;
            }
            $user->status = $shouldBeActivated ? User::STATUS_NOT_ACTIVE : User::STATUS_ACTIVE;
            $user->setPassword($this->password);
            if (!$user->save(false)) {
                throw new Exception("User couldn't be  saved");
            }
            if (\rabint\user\Module::$cellBaseAuth) {
                $user->afterSignup(['cell' => str::formatCellphone($this->username, "+98"),'nickname' => $this->nickname]);
            } else {
                $user->afterSignup(['nickname' => $this->nickname]);
            }
            if ($shouldBeActivated) {
                UserToken::sendActivation($user, UserToken::TYPE_ACTIVATION);
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
