<?php

namespace rabint\user\models;

use rabint\cheatsheet\Time;
use rabint\user\models\User;
use Yii;
use yii\base\Model;
use rabint\helpers\str;

/**
 * Login form
 */
class LoginForm extends Model
{

    public $identity;
    public $password;
    public $redirect = '';
    public $rememberMe = false;
    private $user = false;

    const SCENARIO_FIRST_TRY = "firstTry";

    public $verifyCode;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['identity', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['redirect', 'string'],
            [['redirect', 'password', 'identity'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            ['verifyCode', 'captcha', 'except' => self::SCENARIO_FIRST_TRY],
        ];
    }

    public function attributeLabels()
    {
        str::formatCellphone();
        return [
            'identity' => (\rabint\user\Module::$cellBaseAuth) ? Yii::t('rabint', 'تلفن همراه') : Yii::t('rabint', 'Username or email'),
            'password' => Yii::t('rabint', 'Password'),
            'rememberMe' => Yii::t('rabint', 'Remember Me'),
            'verifyCode' => Yii::t('rabint', 'کد امنیتی'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
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
        Yii::$app->session->set('login_try_count', (Yii::$app->session->get('login_try_count', 0) + 1));
        if ($this->validate()) {
            $rememberTime = (($this->rememberMe == 1) ? Time::SECONDS_IN_A_MONTH : 0);
            if (Yii::$app->user->login($this->getUser(), $rememberTime)) {
                Yii::$app->session->set('login_try_count', 0);
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
        if ($this->user === false) {
            if (\rabint\user\Module::$cellBaseAuth) {
                $cellUsername = str::formatCellphone($this->identity);
                if (!$cellUsername) {
                    $cellUsername = $this->identity;
                }
                $this->user = User::find()
                    ->active()
                    ->andWhere(['or', ['username' => $cellUsername], ['email' => $this->identity]])
                    ->one();
            } else {
                $this->user = User::find()
                    ->active()
                    ->andWhere(['or', ['username' => $this->identity], ['email' => $this->identity]])
                    ->one();
            }
        }

        return $this->user;
    }
}
