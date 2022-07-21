<?php

namespace rabint\user\models\form;

use rabint\user\models\User;
use rabint\user\Module;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class RegisterForm extends Model
{

    /**
     * @var User
     */
    public $user;
    public $identity;
    public $nickname;
    public $name;
    public $family;
    public $username;
    public $confirm;
    public $password;
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
            [['identity','password', 'confirm','nickname'], 'required'],
            ['redirect', 'string'],
            [['redirect', 'password', 'identity'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            ['nickname', 'string'],
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
            'nickname' => Yii::t('rabint', 'نام نمایشی'),
            'name' => Yii::t('rabint', 'نام'),
            'family' => Yii::t('rabint', 'نام خانوادگی'),
            'confirm' => Yii::t('rabint', 'تکرار رمز عبور'),
        ];
    }


    public function signup()
    {
        if ($this->validate()) {
            $this->user->status = User::STATUS_ACTIVE;
            $this->user->setPassword($this->password);
            if (!$this->user->save(false)) {
                return false;
            }
            $this->user->afterSignup([
                'nickname' => $this->nickname,
                'firstname' => $this->name,
                'lastname' => $this->family
            ]);
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
