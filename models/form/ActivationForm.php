<?php

namespace rabint\user\models\form;

use rabint\helpers\str;
use rabint\user\models\User;
use rabint\user\models\UserToken;
use rabint\user\Module;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class ActivationForm extends Model
{

    /**
     * @var User
     */
    public $user;
    public $redirect;
    public $identity;
    public $token;
    public $activationSent = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            [['token','identity'], 'required'],
            ['token', 'integer'],
            ['redirect', 'safe'],
        ];

        switch (\rabint\user\Module::getConfig('baseAuthenticate')) {
            case 'email':
                $return[] = ['identity', 'email'];
                break;
            case 'mobile':
                $return[] = ['identity', 'integer','min' => 10,];
                break;
            case 'mobileEmail':
                $return[] = ['identity', 'string', 'min' => 5, 'max' => 190];
                break;
            default:
                $return[] = ['identity', 'string', 'min' => 5, 'max' => 190];
                break;
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
            'token' => Yii::t('rabint', 'کد شناسایی'),
        ];

    }


    /**
     * @param $identity
     * @return bool
     */
    public function sendActivation()
    {
        $identity = $this->identity;
//        if (UserToken::userHasActiveToken($this->user->id, UserToken::TYPE_ACTIVATION)) {
//            return false;
//        }
        if (Module::baseAuthenticateOnEmail() && !empty($this->user->email)) {
            $this->activationSent = time();
            return UserToken::sendEmailToken($this->user, UserToken::TYPE_ACTIVATION);
        } elseif (Module::baseAuthenticateOnMobile() && !empty($this->user->mobile)) {
            $this->activationSent = time();
            return UserToken::sendSmsToken($this->user, UserToken::TYPE_ACTIVATION);
        } else {
            return false;
        }
        return true;
    }

    /**
     * @param $identity
     * @return bool
     */
    public function checkActivationCode()
    {
        $token = UserToken::find()
            ->byType(UserToken::TYPE_ACTIVATION)
            ->byToken($this->token)
            ->byUserId($this->user->id)
            ->notExpired()
            ->one();

        if (!$token) {
            return false;
        }

        $user = $token->user;
        if ($user === null) {
            return false;
        }
        return true;
    }
}
