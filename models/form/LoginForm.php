<?php

namespace rabint\user\models\form;

use rabint\cheatsheet\Time;
use rabint\helpers\str;
use rabint\user\models\User;
use rabint\user\Module;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{

    public $user;
    public $identity;
    public $password;
    public $redirect = null;
    public $rememberMe = true;
    public $guestname;
    public $guestlogin;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['identity', 'password'], 'required'],
            ['identity', 'validateIdentity'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['redirect', 'safe'],
            [['redirect', 'password', 'identity'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
        ];
    }

    public function attributeLabels()
    {
        switch (\rabint\user\Module::getConfig('baseAuthenticate')) {
            case 'email':
                $identityTitle = Yii::t('app', 'ایمیل');
                break;
            case 'mobile':
                $identityTitle = Yii::t('app', 'تلفن همراه');
                break;
            case 'mobileEmail':
                $identityTitle = Yii::t('app','شناسه کاربری یا تلفن همراه یا ایمیل');
                break;
            default:
                $identityTitle = Yii::t('app', 'نام کاربری');
                break;
        }
        return [
            'identity' => $identityTitle,
            'password' => Yii::t('rabint', 'Password'),
            'rememberMe' => Yii::t('rabint', 'Remember Me'),
            'verifyCode' => Yii::t('rabint', 'کد امنیتی'),
            'guestname' => Yii::t('rabint', 'نام و نام خانوادگی'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validateIdentity(){

    }
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', Yii::t('rabint', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $rememberTime = (($this->rememberMe == 1) ? Time::SECONDS_IN_A_MONTH : Module::getConfig('sessionExpireTime'));
            if (Yii::$app->user->login($this->getUser(), $rememberTime)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        $this->user = User::getUserByIdentity($this->identity);
        return $this->user;
    }
}
