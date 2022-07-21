<?php

namespace rabint\user\models;

use common\models\base\ActiveRecord;
use rabint\cheatsheet\Time;
use rabint\commands\SendEmailCommand;
use rabint\services\sms\sms;
use rabint\user\models\query\UserTokenQuery;
use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_token}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $type
 * @property string $token
 * @property integer $expire_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $user
 */
class UserToken extends ActiveRecord
{
    const TOKEN_LENGTH = 40;

    //const TYPE_ACTIVATION = 'sms_activation';
    const TYPE_ACTIVATION = 'activation';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_ASYNC_LOGIN = 'async_login';
    const TYPE_ASYNC_LOGIN_REMEMMBER = 'async_login_rememmber';

    const MEDIA_SMS = 'sms';
    const MEDIA_EMAIL = 'email';

    public static $smsTokenLen = 5;
    public static $smsExpireTime = 120;
    public static $emailExpireTime = Time::SECONDS_IN_A_DAY;

    /**
     * @return string
     */
    function __toString()
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_token}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className()
        ];
    }

    /**
     * @return UserTokenQuery
     */
    public static function find()
    {
        return new UserTokenQuery(get_called_class());
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'token'], 'required'],
            [['user_id', 'expire_at'], 'integer'],
            [['type'], 'string', 'max' => 255],
            [['token'], 'string', 'max' => 40],
            [['agent'], 'string', 'max' => 190],
            [['ip'], 'string', 'max' => 48]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rabint', 'ID'),
            'user_id' => Yii::t('rabint', 'User ID'),
            'type' => Yii::t('rabint', 'Type'),
            'token' => Yii::t('rabint', 'Token'),
            'agent' => Yii::t('rabint', 'Agent'),
            'ip' => Yii::t('rabint', 'Ip'),
            'expire_at' => Yii::t('rabint', 'Expire At'),
            'created_at' => Yii::t('rabint', 'Created At'),
            'updated_at' => Yii::t('rabint', 'Updated At'),
        ];
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param mixed $user_id
     * @param string $type
     * @param int|null $duration
     * @return bool|UserToken
     */
    public static function userHasActiveToken($user_id, $type)
    {
        $token = UserToken::find()
            ->notExpired()
            ->byType($type)
            ->byUserId($user_id)
            ->one();

        if ($token === null) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $user_id
     * @param string $type
     * @param int|null $duration
     * @return bool|UserToken
     */
    public static function userRecentlyGetToken($user_id, $type)
    {
        $token = UserToken::find()
            ->andWhere(['>', 'created_at', time() - 120])
            ->byType($type)
            ->byUserId($user_id)
            ->one();

        if ($token === null) {
            return false;
        }
        return true;
    }

    public static function activeTokenExist($token)
    {
        $token = UserToken::find()
            ->notExpired()
            ->byToken($token)
            ->one();

        if ($token === null) {
            return false;
        }
        return true;
    }


    /**
     * @param mixed $user_id
     * @param string $type
     * @param int|null $duration
     * @return bool|UserToken
     */
    public static function create($user_id, $type, $duration = null, $token_postfix = "")
    {
        $has = true;
        while ($has) {
            $token = rand(10 ** (self::$smsTokenLen - 1), (10 ** self::$smsTokenLen) - 1) . $token_postfix;
            $has = self::activeTokenExist($token);
        }
        $model = new self;
        $model->setAttributes([
            'user_id' => $user_id,
            'type' => $type,
            'token' => $token,
            'expire_at' => time() + ($duration ?: static::getDuration($type)),
            'ip' => \rabint\helpers\user::ip(),
            'agent' => \rabint\helpers\user::agent(),
        ]);

        if (!$model->save()) {
            var_dump($model->getErrors());
            die();
            throw new InvalidCallException;
        };

        return $model;
    }

    public static function getDuration($type)
    {
        switch ($type) {
            case static::TYPE_ACTIVATION:
                return 150;
            case static::TYPE_PASSWORD_RESET:
                return (Time::SECONDS_IN_AN_HOUR / 2);
            case static::TYPE_ASYNC_LOGIN:
                return config('SERVICE.user.session_timeout', 3600);
            case static::TYPE_ASYNC_LOGIN_REMEMMBER:
                return Time::SECONDS_IN_A_MONTH;
            default:
                return null;
        }

    }

    /**
     * @param int|null $duration
     */
    public function renew($duration = null)
    {
        return $this->updateAttributes([
            'expire_at' => time() + ($duration ?: static::getDuration($this->type))
        ]);
    }

//    public static function sendActivation($user, $type, $media = null)
//    {
//        if ((empty($media) && \rabint\user\Module::$cellBaseAuth) OR $media = self::MEDIA_SMS) {
//            $type = ($type == self::TYPE_ACTIVATION) ? self::TYPE_ACTIVATION : $type;
//            return static::sendSmsToken($user, $type);
//        } else {
//            return static::sendEmailToken($user, $type);
//        }
//    }

    public static function sendSmsToken($user, $type = null)
    {
        $type = empty($type) ? self::TYPE_ACTIVATION : $type;
        /**
         * check user not has active token
         */
        if (UserToken::userHasActiveToken($user->id, $type)) {
            return false;
        }

        $token = UserToken::create($user->id, $type, self::$smsExpireTime);
        if (empty($token)) {
            return false;
        }
        $tokenMsg = $token->token;
//        $message = <<<EOT
//کد فعال سازی شما:
//$tokenMsg
//EOT;
//        return Yii::$app->notify->send($user->id, $message, '', [
//            'media' => Notification::MEDIA_SMS
//        ]);

        return sms::sendVerify($user->mobile, $token->token);
    }


    public static function sendEmailToken($user, $type = null)
    {
        $type = empty($type) ? self::TYPE_ACTIVATION : $type;
        $token = UserToken::create($user->id, $type, self::$emailExpireTime);
        if ($token) {
            try {
                return Yii::$app->commandBus->handle(
                    new SendEmailCommand([
                        'to' => $user->email,
                        'subject' => Yii::t('rabint', 'کد فعال سازی {name}', ['name' => Yii::$app->name]),
                        'view' => 'activatinEmail',
                        'params' => [
                            'user' => $user,
                            'token' => $token->token
                        ]
                    ]));
            } catch (\Exception $exception) {
                \Yii::$app->session->setFlash('warning', \Yii::t('app', 'امکان ارسال ایمیل از طریق سرور ایمیل وجود ندارد ، لطفا با مدیریت تماس بگیرید'));
                return false;
            }
        }

    }

}
