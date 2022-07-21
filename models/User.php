<?php

namespace rabint\user\models;

use common\models\base\ActiveRecord;
use http\Exception\InvalidArgumentException;
use rabint\helpers\str;
use rabint\notify\models\Notification;
use rabint\user\models\query\UserQuery;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property integer $mobile
 * @property string $auth_key
 * @property string $access_token
 * @property string $oauth_client
 * @property string $oauth_client_user_id
 * @property string $publicIdentity
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $logged_at
 * @property string $password write-only password
 * @property string $displayName
 * @property string $login_ip
 * @property string $rate
 * @property string $socket_setting
 * @property string $socket_latest_status
 *
 * @property \rabint\user\models\UserProfile $userProfile
 */
class User extends ActiveRecord implements IdentityInterface
{

    const STATUS_NOT_ACTIVE = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_BANNED = 3;
    const STATUS_INVITED = 4;

    /* =================================================================== */
    const MATURITY_UNKNOWN = 0;
    const MATURITY_A = 1;
    const MATURITY_B = 2;
    const MATURITY_C = 4;
    const MATURITY_D = 8;
    const MATURITY_E = 16;
    const MATURITY_F = 32;
    /* =================================================================== */
    const IS_OFFICIAL_MASTER = 2;
    const IS_OFFICIAL_YES = 1;
    const IS_OFFICIAL_NO = 0;
    const IS_OFFICIAL_WAITING = -1;
    const IS_OFFICIAL_BAN = -2;

    /* =================================================================== */
    const ROLE_USER = 'user';
    const ROLE_CONTRIBUTOR = 'contributor'; //level3
    const ROLE_AUTHOR = 'author'; //level2
    const ROLE_REPORTER = 'reporter'; //level1
    const ROLE_EDITOR = 'editor'; //level1
    const ROLE_MANAGER = 'manager';
    const ROLE_ADMINISTRATOR = 'administrator';

    /* =================================================================== */
    const RULE_USER_OWN_MODEL = 'userOwnModel';
    const RULE_CONTRIBUTOR_OWN_MODEL = 'contributorOwnModel';
    const RULE_AUTHOR_OWN_MODEL = 'authorOwnModel';
    const RULE_EDITOR_OWN_MODEL = 'editorOwnModel';

    /* =================================================================== */
    const EVENT_AFTER_SIGNUP = 'afterSignup';
    const EVENT_AFTER_LOGIN = 'afterLogin';

    const SCENARIO_USER_PROFILE = 'user-profile';

    public $reCaptcha;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'auth_key' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key'
                ],
                'value' => Yii::$app->getSecurity()->generateRandomString()
            ],
            'access_token' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'access_token'
                ],
                'value' => function () {
                    return Yii::$app->getSecurity()->generateRandomString(40);
                }
            ]
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return ArrayHelper::merge(
            parent::scenarios(),
            [
                'oauth_create' => ['oauth_client', 'oauth_client_user_id', 'email', 'username', '!status'],
                'adminEdit' => ['username', 'email', 'password', 'status', 'roles'],
                'changeStatus' => ['status'],
                'changeUsername' => ['username'],
                'user-profile' => ['email'],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
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
            ['username', 'match', 'pattern' => '/^[a-zA-z0-9._-]*$/i', 'message' => Yii::t('rabint', 'فقط حروف لاتین، اعداد، خط ، نقطه و زیر خط مجاز است')],
            ['username', 'string', 'min' => 5, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            ['mobile', 'integer'],
            [
                'email', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'This email has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                },
            ],
            ['status', 'default', 'value' => self::STATUS_NOT_ACTIVE],
            //                ['is_official', 'default', 'value' => self::IS_OFFICIAL_NO],
            ['status', 'in', 'range' => array_keys(self::statuses())],
            //                ['is_official', 'in', 'range' => array_keys(self::officiality())],
            [['username'], 'filter', 'filter' => '\yii\helpers\Html::encode'],
            [['username', 'email'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            //            ['is_official', 'integer', 'on' => 'adminEdit']
        ];


        /**
         * mail validations
         */
        $return[] = ['username', 'match', 'pattern' => '/^[a-zA-z0-9._-]*$/i', 'message' => Yii::t('rabint', 'فقط حروف لاتین، اعداد، خط ، نقطه و زیر خط مجاز است')];
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
        if(\rabint\user\Module::getConfig('enableCaptcha')){
            switch(\rabint\user\Module::getConfig('enableCaptcha')){
                    case 'reCaptcha3':

                        $return[] = [['reCaptcha'], \kekaadrenalin\recaptcha3\ReCaptchaValidator::className(), 'acceptance_score' => 0];
                    break;
                    default:
                        echo '';
                    break;
                }
        }
        /**
         * password policies
         */
        // if (Module::$passwordPolicy == "strong") {
        //     $return[] = ['password', 'match', 'pattern' => '/^((?=.*[0-9])(?=.*[a-z]))|((?=.*[۱۲۳۴۵۶۷۸۹۰])).+$/i', 'message' => Yii::t('rabint', ' کلمه عبور باید شامل حروف و عدد بوده و حداقل 8 حرف طول داشته باشد')];
        //     $return[] = [['password', 'confirm'], 'string', 'min' => 8];
        // } elseif (Module::$passwordPolicy == "medium") {
        //     $return[] = ['password', 'match', 'pattern' => '/^((?=.*[0-9])(?=.*[a-z]))|((?=.*[۱۲۳۴۵۶۷۸۹۰])).+$/i', 'message' => Yii::t('rabint', ' کلمه عبور باید شامل حروف و عدد بوده و حداقل 6 حرف طول داشته باشد')];
        //     $return[] = [['password', 'confirm'], 'string', 'min' => 6];
        // } elseif (Module::$passwordPolicy == "cheap") {
        //     $return[] = [['password', 'confirm'], 'string', 'min' => 5];
        // } else {
        //     $return[] = [['password', 'confirm'], 'string', 'min' => 6];
        // }
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rabint', 'Id'),
            'username' => Yii::t('rabint', 'Username'),
            'email' => Yii::t('rabint', 'E-mail'),
            'mobile' => Yii::t('rabint', 'تلفن همراه'),
            'status' => Yii::t('rabint', 'Status'),
            'access_token' => Yii::t('rabint', 'API access token'),
            'created_at' => Yii::t('rabint', 'Created at'),
            'updated_at' => Yii::t('rabint', 'Updated at'),
            'logged_at' => Yii::t('rabint', 'Last login'),
            //            'is_official' => Yii::t('rabint', 'کاربر رسمی'),
        ];
    }

    /**
     * @return \common\models\base\ActiveQuery
     */
    public function getUserProfile()
    {
        static $checkUserProfile = true;

        $return = $this->hasOne(UserProfile::className(), ['user_id' => 'id']);
        if($checkUserProfile && empty($return->one())){
            $userProfile = new UserProfile;
            $userProfile->user_id = $this->id;
            $userProfile->nickname = $this->username;
            $userProfile->save();
        }
        $checkUserProfile = false;
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()
            ->active()
            ->andWhere(['access_token' => $token, 'status' => self::STATUS_ACTIVE])
            ->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->active()
            ->andWhere(['username' => $username, 'status' => self::STATUS_ACTIVE])
            ->one();
    }

    /**
     * Finds user by username or email
     *
     * @param string $login
     * @return static|null
     */
    public static function findByLogin($login)
    {
        return static::find()
            ->active()
            ->andWhere([
                'and',
                ['or', ['username' => $login], ['email' => $login]],
                'status' => self::STATUS_ACTIVE
            ])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * Returns user statuses list
     * @return array|mixed
     */
    public static function statuses()
    {
        return [
            self::STATUS_NOT_ACTIVE => ['title' => Yii::t('rabint', 'غیر فعال')],
            self::STATUS_ACTIVE => ['title' => Yii::t('rabint', 'فعال')],
            self::STATUS_BANNED => ['title' => Yii::t('rabint', 'غیرفعال')],
            self::STATUS_INVITED => ['title' => Yii::t('rabint', 'دعوت شده')]
        ];
    }

    public static function officiality()
    {
        return [
            self::IS_OFFICIAL_NO => ['title' => Yii::t('rabint', 'غیر رسمی')],
            self::IS_OFFICIAL_YES => ['title' => Yii::t('rabint', 'رسمی')],
            self::IS_OFFICIAL_MASTER => ['title' => Yii::t('rabint', 'پروفایل ویژه حرم')],
            self::IS_OFFICIAL_WAITING => ['title' => Yii::t('rabint', 'در حال بررسی')],
            self::IS_OFFICIAL_BAN => ['title' => Yii::t('rabint', 'عدم تایید درخواست')],
        ];
    }

    public static function globalRoles()
    {
        return [
            self::ROLE_USER => ['level' => 1, 'title' => Yii::t('rabint', 'کاربر عادی')],
            self::ROLE_CONTRIBUTOR => ['level' => 2, 'title' => Yii::t('rabint', 'نویسنده مقدماتی')],
            self::ROLE_AUTHOR => ['level' => 3, 'title' => Yii::t('rabint', 'نویسنده')],
            self::ROLE_EDITOR => ['level' => 4, 'title' => Yii::t('rabint', 'ویرایشگر')],
            self::ROLE_MANAGER => ['level' => 9, 'title' => Yii::t('rabint', 'مدیر')],
            self::ROLE_ADMINISTRATOR => ['level' => 10, 'title' => Yii::t('rabint', 'مدیر کل')],
        ];
    }

    /**
     * Creates user profile and application event
     * @param array $profileData
     */
    public function afterSignup(array $profileData = [])
    {
        //        $this->refresh();
        //        Yii::$app->commandBus->handle(new AddToTimelineCommand([
        //            'category' => 'user',
        //            'event' => 'signup',
        //            'data' => [
        //                'public_identity' => $this->getPublicIdentity(),
        //                'user_id' => $this->getId(),
        //                'created_at' => $this->created_at
        //            ]
        //        ]));
        $profile = UserProfile::findOne(['user_id'=>$this->getId()]);
        $isNewProfile = false;
        if($profile==null){
            $isNewProfile = true;
            $profile = new UserProfile();
        }
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        if($this->userProfile==null){
           $this->link('userProfile', $profile);
        }
        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        if($isNewProfile){
            $auth = Yii::$app->authManager;
            $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
        }
    }

    public static function inviteUser($username, $email, $mobile, $profileData = [], $isUserActiveted = false, $sendNotify = false)
    {
//        if (in_array(config('SERVICE.user.baseAuthenticate'),['mobile','mobileEmail']) && empty($mobile)) {
//            throw new InvalidArgumentException(Yii::t('app', 'فیلد موبایل اجباری است'));
//        }
//        if (!\rabint\user\Module::$cellBaseAuth && empty($email)) {
//            throw new InvalidArgumentException(Yii::t('app', 'فیلد ایمیل اجباری است'));
//        }
        $user = new self();
        $user->username = $username;
        //$user->password = str::random(12);
        if (isset($profileData['password'])) {
            $password = $profileData['password'];
            unset($profileData['password']);
        } else {
            $password = rand(100000, 999999);
        }
        $user->password = $password;
        $user->email = $email;
        $user->mobile = ($mobile?str::CellphoneSanitise($mobile, "+98") : null );
        if ($isUserActiveted == true) {
            $user->status = self::STATUS_ACTIVE;
        } else {
            $user->status = self::STATUS_INVITED;
        }
        if (!$user->save(false)) {
            return false;
        }
//        $profileData['cell'] = str::CellphoneSanitise($mobile, "+98");
        $user->afterSignup($profileData);

        /**
         * notify
         */
        $notifyLink = Url::to(['/user/sign-in/login'], true);
        if ($sendNotify) {
            $text = <<<EOT
سامانه آموزش مجازی دیدار
$notifyLink
رمز ورود:
$password
EOT;
            $has_sent = Yii::$app->notify->send($user->id, $text, $notifyLink, [
                'priority' => Notification::MEDIA_ALL,
            ]);
        }

        return $user->id;
    }

    /**
     * @return string
     */
    public function getPublicIdentity()
    {
        if ($this->userProfile && $this->userProfile->getFullname()) {
            return $this->userProfile->getFullname();
        }
        if ($this->username) {
            return $this->username;
        }
        return $this->email;
    }

    public function getDisplayName()
    {
        if ($this->status == static::STATUS_BANNED) {
            return \Yii::t('rabint', 'کاربر حذف شده');
        }
        if (!empty($this->userProfile->nickname)) {
            $return = $this->userProfile->nickname;
            return \yii\helpers\Html::encode($return);
        }
        if ($this->userProfile->firstname || $this->userProfile->lastname) {
            $return = implode(' ', [$this->userProfile->firstname, $this->userProfile->lastname]);
            return \yii\helpers\Html::encode($return);
        }
        return \yii\helpers\Html::encode($this->username);
    }
    //
    //    public function getPosts() {
    //        return $this->hasMany(\app\modules\post\models\Post::className(), ['user_id' => 'id']);
    //    }

    public function getIsOfficial()
    {
        return false;
        //        return $this->is_official >= static::IS_OFFICIAL_YES ? $this->is_official : FALSE;
    }

    public function setActive()
    {
        $this->scenario = "changeStatus";
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function removeUser()
    {
        $this->username = base64_encode($this->username);
        $this->status = static::STATUS_BANNED;
        if ($this->save()) ;
    }

    /**
     * Creates user profile and application event
     * @param array $profileData
     */
    public function afterInvite(array $profileData = [])
    {
        $profile = UserProfile::find()->where(['user_id' => $this->id])->one();
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $this->link('userProfile', $profile);
        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
    }

    public static function getUserByIdentity($identity)
    {
        /**
         * get user by identity as username
         */
        $user = User::find()->andWhere(['username' => $identity])->one();
        if ($user != null) {
            return $user;
        }

        /**
         * not find by username
         * try if identity is email
         */
        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            $user = User::find()->andWhere(['email' => $identity])->one();
            if ($user != null) {
                return $user;
            }
        }

        /**
         * try identity by mobile
         */
        if ($mobileNumber = str::formatCellphone($identity)) {
            $user = User::find()->andWhere(['mobile' => $mobileNumber])->one();
            if ($user != null) {
                return $user;
            }
            $user = User::find()->andWhere(['username' => $mobileNumber])->one();
            return $user;
        }

        return false;
    }
}
