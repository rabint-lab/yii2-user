<?php

namespace rabint\user\models\form;

use rabint\user\models\User;
use rabint\user\Module;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class PasswordForm extends Model
{

    /**
     * @var User
     */
    public $user;
    public $identity;
    public $password;
    public $confirm;
    public $redirect = null;
    public $rememberMe = false;
//    public $verifyCode;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            // username and password are both required
            [['identity','password', 'confirm'], 'required'],
            ['redirect', 'string'],
            [['redirect', 'password', 'identity'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            ['confirm', 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('rabint', "تکرار کلمه عبور صحیح نیست")],
        ];
        /**
         * password policies
         */
        if (Module::getConfig('passwordPolicy') == "strong") {
            $return[] = ['password', 'match', 'pattern' => '/^((?=.*[0-9])(?=.*[a-z]))|((?=.*[۱۲۳۴۵۶۷۸۹۰])).+$/i', 'message' => Yii::t('rabint', ' کلمه عبور باید شامل حروف و عدد بوده و حداقل 8 حرف طول داشته باشد')];
            $return[] = [['password', 'confirm'], 'string', 'min' => 8];
        } elseif (Module::getConfig('passwordPolicy')== "medium") {
            $return[] = ['password', 'match', 'pattern' => '/^((?=.*[0-9])(?=.*[a-z]))|((?=.*[۱۲۳۴۵۶۷۸۹۰])).+$/i', 'message' => Yii::t('rabint', ' کلمه عبور باید شامل حروف و عدد بوده و حداقل 6 حرف طول داشته باشد')];
            $return[] = [['password', 'confirm'], 'string', 'min' => 6];
        } elseif (Module::getConfig('passwordPolicy')== "cheap") {
            $return[] = [['password', 'confirm'], 'string', 'min' => 5];
        } else {
            $return[] = [['password', 'confirm'], 'string', 'min' => 6];
        }
        return $return;
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
                $identityTitle = Yii::t('app', 'تلفن همراه یا ایمیل');
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
            'confirm' => Yii::t('rabint', 'تکرار رمز عبور'),
        ];
    }


    public function setPassword()
    {
        if ($this->validate()) {
            $this->user->status = User::STATUS_ACTIVE;
            $this->user->setPassword($this->password);
            if (!$this->user->save(false)) {
                return false;
            }
            return true;
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
