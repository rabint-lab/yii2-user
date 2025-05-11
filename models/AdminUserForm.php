<?php

namespace rabint\user\models;

use app\modules\post\models\GroupMember;
use rabint\helpers\str;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Create user form
 */
class AdminUserForm extends Model
{

    public $redirect = "";
    public $username;
    public $email;
    public $level;
    public $password;
    public $confirm;
    public $status;
    public $roles;
    public $groups;
    /**
     * profile
     */
    public $nickname;
    public $lastname;
    public $firstname;
    public $locale;
    public $cell;
    public $brithdate;
    public $gender;
    public $avatar_url;


    // public $is_official;
    public $aset_max_upload_size;
    public $aset_must_changed_password = 1;
    private $model;

    public $group;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $required = [];
        if (\rabint\user\Module::baseAuthenticateOnEmail()) {
            $required[] = 'email';
            $required[] = 'username';
        }
        if (\rabint\user\Module::baseAuthenticateOnMobile()) {
            $required[] = 'cell';
        } else {
            $required[] = 'username';
        }

        return [
            ['redirect', 'safe'],
            ['username', 'filter', 'filter' => 'trim'],
            [$required, 'required'],
            [
                'username',
                'unique',
                'targetClass' => User::className(),
                'filter' => function ($query) {
                    if (!$this->getModel()->isNewRecord) {
                        $query->andWhere(['not', ['id' => $this->getModel()->id]]);
                    }
                }
            ],
            ['username', 'match', 'pattern' => '/^[a-zA-z0-9._-]*$/i', 'message' => Yii::t('rabint', 'فقط حروف لاتین، اعداد، خط ، نقطه و زیر خط مجاز است')],
            ['username', 'string', 'min' => 5, 'max' => 255],
            [['username'], 'filter', 'filter' => '\yii\helpers\Html::encode'],
            [['username', 'email'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            ['level', 'integer'],
            [
                'email',
                'unique',
                'targetClass' => User::className(),
                'filter' => function ($query) {
                    if (!$this->getModel()->isNewRecord) {
                        $query->andWhere(['not', ['id' => $this->getModel()->id]]);
                    }
                }
            ],
            ['password', 'required', 'on' => ['create', 'create_user']],
            ['password', 'string', 'min' => 6],
            ['confirm', 'required', 'on' => 'create'],
            ['confirm', 'compare', 'compareAttribute' => 'password'],
            [['status', 'group'], 'integer'],
            // [['is_official'], 'integer'],
            [['aset_max_upload_size'], 'integer', 'min' => 1, 'max' => 600],
            [['aset_must_changed_password'], 'integer', 'min' => 0, 'max' => 1],
            [
                ['roles'],
                'each',
                'rule' => [
                    'in',
                    'range' => ArrayHelper::getColumn(
                        Yii::$app->authManager->getRoles(),
                        'name'
                    )
                ]
            ],
            [
                ['groups'],
                'each',
                'rule' => [
                    'integer'
                ]
            ],
            /* profiles */
            [['nickname', 'lastname', 'firstname'], 'string', 'max' => 255],
            [['locale'], 'string', 'max' => 5],
            //[['cell'], 'string', 'max' => 13],
            //support only iran cellphone number
            ['cell', 'match', 'pattern' => '/^(?:\+98|0098|98|0)?9[0-9]{9}$/', 'message' => 'لطفاً یک شماره موبایل معتبر ایرانی وارد کنید.'],
            //support all world country cellphone number
            //['cell', 'match', 'pattern' => '/^\+?[1-9]\d{9,14}$/', 'message' => 'شماره موبایل وارد شده معتبر نیست.'],
            ['gender', 'integer'],
            [['avatar_url'], 'safe'],
            ['gender', 'in', 'range' => [NULL, UserProfile::GENDER_FEMALE, UserProfile::GENDER_MALE]],
            [['brithdate'], 'safe'],
        ];

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {

        return [
            'username' => Yii::t('rabint', 'نام کاربری'),
            'email' => Yii::t('rabint', 'Email'),
            'status' => Yii::t('rabint', 'Status'),
            'confirm' => Yii::t('rabint', 'تکرار رمز'),
            'password' => Yii::t('rabint', 'Password'),
            //  'is_official' => Yii::t('rabint', 'نوع پروفایل'),
            'aset_max_upload_size' => Yii::t('rabint', 'حجم مجاز آپلود(مگابایت)'),
            'aset_must_changed_password' => Yii::t('rabint', 'تعویض رمز در اولین ورود'),
            'roles' => Yii::t('rabint', 'Roles'),
            'nickname' => Yii::t('rabint', 'نام نمایشی'),
            'firstname' => Yii::t('rabint', 'نام'),
            'lastname' => Yii::t('rabint', 'نام خانوادگی'),
            'locale' => Yii::t('rabint', 'زبان'),
            'level' => Yii::t('rabint', 'سطح'),
            'cell' => Yii::t('rabint', 'تلفن همراه'),
            'gender' => Yii::t('rabint', 'جنسیت'),
            'brithdate' => Yii::t('rabint', 'تاریخ تولد'),
            'avatar_url' => Yii::t('rabint', 'تصویر کاربری'),
            'group' => Yii::t('rabint', 'سازمان'),
            'groups' => Yii::t('rabint', 'گروه های این کاربر'),
        ];
    }

    /**
     * @param User $model
     * @return mixed
     */
    public function setModel($model)
    {
        $this->username = $model->username;
        $this->email = $model->email;
        $this->status = $model->status;
        $this->level = $model->level;
        $this->model = $model;
        //$this->is_official = $model->is_official;
        $this->aset_max_upload_size = $model->userProfile->aset_max_upload_size;
        $this->aset_must_changed_password = $model->userProfile->aset_must_changed_password;

        $this->nickname = $model->userProfile->nickname;
        $this->lastname = $model->userProfile->lastname;
        $this->firstname = $model->userProfile->firstname;
        $this->locale = $model->userProfile->locale;
        $this->cell = $model->mobile;
        $this->brithdate = $model->userProfile->brithdate;
        $this->gender = $model->userProfile->gender;
        $this->avatar_url = $model->userProfile->avatar_url;


        $this->roles = ArrayHelper::getColumn(
            Yii::$app->authManager->getRolesByUser($model->getId()),
            'name'
        );
        return $this->model;
    }

    /**
     * @return User
     */
    public function getModel()
    {
        if (!$this->model) {
            $this->model = new User();
        }
        return $this->model;
    }


    /**
     * Signs user up.
     * @return User|null the saved model or null if saving fails
     * @throws Exception
     */
    public function getIsNewRecord()
    {
        return $this->getModel()->getIsNewRecord();
    }

    public function save()
    {

        if ($this->validate()) {
            $model = $this->getModel();
            $isNewRecord = $model->getIsNewRecord();
            $model->scenario = 'adminEdit';
            $this->cell = str::formatCellphone($this->cell);
            if (empty($this->username)) {
                $model->username = $this->cell;
            } else {
                $model->username = $this->username;
            }
            $model->email = $this->email;
            $model->status = $this->status;
            $model->level = $this->level;
            $model->mobile = $this->cell;
            // $model->is_official = $this->is_official;
            if ($this->password) {
                $model->setPassword($this->password);
            }
            if (!$model->save(false)) {
                Yii::$app->session->setFlash('danger', \rabint\helpers\str::modelErrToStr($model->errors));
                return false;
            }
            /* =================================================================== */
            if ($isNewRecord) {
                $model->afterSignup();
            }
            $profile = UserProfile::findOne(['user_id' => $model->id]);
            if (!empty($profile)) {
                $profile->scenario = UserProfile::SCENARIO_ADMIN_SETTING;
                $profile->aset_max_upload_size = $this->aset_max_upload_size;
                $profile->aset_must_changed_password = $this->aset_must_changed_password;

                $profile->nickname = $this->nickname;
                $profile->lastname = $this->lastname;
                $profile->firstname = $this->firstname;
                $profile->locale = $this->locale;
                $profile->cell = $this->cell;
                $profile->brithdate = \rabint\helpers\locality::anyToTimeStamp($this->brithdate);
                //$profile->avatar_url = $this->avatar_url;
                //$profile->brithdate = $this->brithdate;
                $profile->gender = $this->gender;

                $res = $profile->save(false);
            }
            if ($this->groups && is_array($this->groups)) {
                foreach ($this->groups as $grp) {
                    $gp = new GroupMember();
                    $gp->group_id = $grp;
                    $gp->user_id = $model->id;
                    $gp->save(false);
                }
            }
            $auth = Yii::$app->authManager;
            if ($this->roles && is_array($this->roles)) {
                $isChief = \rabint\helpers\user::userCan($model->getId(), 'isChief');

                $auth->revokeAll($model->getId());

                foreach ($this->roles as $role) {
                    $auth->assign($auth->getRole($role), $model->getId());
                }
                if ($isChief) {
                    $auth->assign($auth->getPermission('isChief'), $model->getId());
                }
            }

            return !$model->hasErrors();
        }
        return null;
    }
}
