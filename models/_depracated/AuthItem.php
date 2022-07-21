<?php

namespace rabint\user\models;

use common\models\base\ActiveRecord;
use Yii;

class AuthItems extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_rbac_auth_item}}';
    }

    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rabint', 'Id'),
            'type' => Yii::t('rabint', 'Username'),
            'description' => Yii::t('rabint', 'E-mail'),
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
        return $this->hasOne(UserProfile::className(), ['user_id' => 'id']);
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
            self::STATUS_DELETED => ['title' => Yii::t('rabint', 'حذف شده')],
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
        $profile = new UserProfile();
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $this->link('userProfile', $profile);
        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
    }

    public static function inviteUser($username,$email,$mobile,$profileData=[]){
        if(\rabint\user\Module::$cellBaseAuth && empty($mobile)){
            throw new InvalidArgumentException(Yii::t('app', 'فیلد موبایل اجباری است'));
        }
        if(!\rabint\user\Module::$cellBaseAuth && empty($email)){
            throw new InvalidArgumentException(Yii::t('app', 'فیلد ایمیل اجباری است'));
        }
        $user = new self();
        $user->username = $username;
        $user->password = str::random(12);
        $user->email= $email;
        $user->status= self::STATUS_INVITED;
        if(!$user->save(false)){
            return false;
        }
        $profileData['cell']=str::CellphoneSanitise($mobile,"+98");
        $user->afterSignup($profileData);
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
        if ($this->status == static::STATUS_DELETED) {
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
        $this->status = static::STATUS_DELETED;
        if ($this->save());
    }
    
    /**
     * Creates user profile and application event
     * @param array $profileData
     */
    public function afterInvite(array $profileData = [])
    {
        $profile = UserProfile::find()->where(['user_id'=>$this->id])->one();
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $this->link('userProfile', $profile);
        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
    }
}
