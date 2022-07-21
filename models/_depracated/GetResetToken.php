<?php

namespace rabint\user\models;

use rabint\user\models\User;
use rabint\user\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class GetResetToken extends Model
{

    /**
     * @var
     */
    public $redirect="";
    public $token;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['redirect', 'safe'],
            ['token', 'required'],
        ];
    }
      /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'token' => Yii::t('rabint', 'کد فعال سازی'),
        ];
    }
    /**
     * return UserToken
     */
    public function checkToken()
    {
        /** @var UserToken $tokenModel */
        $this->token = UserToken::find()
            ->notExpired()
            ->byType(UserToken::TYPE_SMS_ACTIVATION)
            ->byToken($this->token)
            ->one();

        if (!$this->token) {
            return false;
        }
        return $this->token;
    }

    /**
     * return UserToken
     */
    public function checkActiveToken()
    {
        //var_dump($this->token);
        //var_dump($this->expire_at);
        /** @var UserToken $tokenModel */
        $this->token = UserToken::find()
            ->notExpired()
            ->byType(UserToken::TYPE_SMS_ACTIVATION)
            ->byToken($this->token)
            ->one();

        if (!$this->token) {
            return false;
        }
        return $this->token;
    }
  
}
