<?php

namespace rabint\user\models;

use rabint\user\models\User;
use rabint\user\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use rabint\user\Module;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{

    /**
     * @var
     */
    public $redirect="";
    public $password;
    public $confirm;

    /**
     * @var \rabint\user\models\UserToken
     */
    private $token;

    /**
     * Creates a form model given a token.
     *
     * @param  string                          $token
     * @param  array                           $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException(\Yii::t('rabint', 'لینک فعالسازی نا معتبر است'));
        }
        /** @var UserToken $tokenModel */
        $this->token = UserToken::find()
            ->notExpired()
            ->byType(UserToken::TYPE_PASSWORD_RESET)
            ->byToken($token)
            ->one();

        if (!$this->token) {
            throw new InvalidParamException(\Yii::t('rabint', 'لینک فعالسازی نا معتبر است'));
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            ['redirect', 'safe'],
            ['password', 'required'],
            //['password', 'string', 'min' => 6],
            ['confirm', 'required'],
            ['confirm', 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('rabint', "تکرار کلمه عبور صحیح نیست")],
        ];

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
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword($autoLogin = true)
    {
        $user = $this->token->user;
        $user->password = $this->password;
        if ($user->save()) {
            if ($autoLogin) {
                $res = Yii::$app->user->login($user);
            }
            $this->token->delete();
        }
        return true;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('rabint', 'رمز جدید'),
            'confirm' => Yii::t('rabint', 'تکرار رمز')
        ];
    }
}
