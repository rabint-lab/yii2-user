<?php

namespace rabint\user\models;

use common\models\base\ActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_profile".
 *
 * @property integer $user_id
 * @property integer $locale
 * @property string $firstname
 * @property string $nickname
 * @property string $lastname
 * @property string $picture
 * @property string $avatar
 * @property string $avatar_url
 * @property string $phone
 * @property string $cell
 * @property integer $gender
 * @property integer $email_activated
 * @property integer $cell_activated
 * @property integer $displayName
 * @property string $nationality
 * @property string $religion
 * @property string $others
 * @property integer $sejel_id
 * @property string $sejel_serial
 * @property string $brithdate
 *
 * @property User $user
 */
class UserProfile extends ActiveRecord
{

    var $email_activated = 1;
    var $cell_activated = 1;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const SCENARIO_VISIT_COUNT = 'visitCount';
    const SCENARIO_ADMIN_SETTING = 'adminSetting';

    /* admin setting fields */

    /**
     * @var
     */
//    public $picture;

    public static function genders()
    {
        return [
            static::GENDER_MALE => ['title' => \Yii::t('rabint', 'مرد'), 'name' => 'MALE'],
            static::GENDER_FEMALE => ['title' => \Yii::t('rabint', 'زن'), 'name' => 'FEMALE'],
        ];
    }

    public function scenarios()
    {
        return parent::scenarios() + [
                self::SCENARIO_VISIT_COUNT => ['channel_visit'],
                self::SCENARIO_ADMIN_SETTING => ['aset_max_upload_size', 'aset_must_changed_password']
            ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => \rabint\behaviors\MetaBehavior::className(),
                'fields' => ['aset_max_upload_size', 'aset_must_changed_password'],
                'destinationField' => 'admin_setting',
            ],
            [
                'class' => \rabint\behaviors\MetaBehavior::class,
                'fields' => ArrayHelper::getColumn($this->othersMetaFields(), 'field'),
                'destinationField' => 'others',
            ],
            [
                'class' => \rabint\attachment\behaviors\AttechmentBehavior::className(),
                'attributes' => [
                    'avatar_url' => [
                        'storage' => 'local',
                        'component' => 'userAvatar',
                        'saveFilePath' => true,
                        'rules' => [
//                            'imageSize' => ['minWidth' => 300, 'minHeight' => 300],
                            'mimeTypes' => ['image/png', 'image/gif', 'image/jpg', 'image/jpeg'],
                            'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
//                            'maxSize' => 1024 * 1024 * 10, // 1 MB
                            'tooBig' => Yii::t('rabint', 'File size must not exceed') . ' 1Mb'
                        ],
                        'preset' => \rabint\attachment\attachment::imgPresetsFn('userAvatar'),
                        'applyPresetAfterUpload' => '*'
                    ]
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }


    public function othersMetaFields()
    {
        $other_fileds = config('SERVICE.user.other_profile_fields', []);
        $return = [];
        foreach ($other_fileds() as $field) {
            if ($this->hasAttribute($field['field'])) {
                continue;
            }
            $return[] = $field;
        }
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $return = [
            [['user_id'], 'required'],
            [['user_id', 'gender'], 'integer'],
            [['gender'], 'in', 'range' => [NULL, self::GENDER_FEMALE, self::GENDER_MALE]],
            [['firstname', 'nickname', 'lastname'], 'string', 'max' => 255],
            [['phone', 'cell', 'sejel_serial'], 'string', 'max' => 13],
            [['melli_code'], 'string', 'max' => 10],
            [['state'], 'string', 'max' => 30],
            [['city'], 'string', 'max' => 45],
            [['nationality', 'religion'], 'string', 'max' => 32],
            ['locale', 'default', 'value' => Yii::$app->language],
//                ['locale', 'in', 'range' => array_keys(Yii::$app->params['availableLocales'])],
            [['avatar_url', 'address', 'description'], 'safe'],
            [['admin_setting'], 'safe', 'except' => static::SCENARIO_DEFAULT],
            [['admin_setting'], 'safe', 'except' => static::SCENARIO_VISIT_COUNT],
            [['admin_setting'], 'safe', 'on' => static::SCENARIO_ADMIN_SETTING],
            [['aset_max_upload_size'], 'integer', 'except' => static::SCENARIO_DEFAULT],
            [['aset_max_upload_size'], 'integer', 'except' => static::SCENARIO_VISIT_COUNT],
            [['aset_max_upload_size'], 'integer', 'on' => static::SCENARIO_ADMIN_SETTING],
            [['aset_must_changed_password'], 'integer', 'except' => static::SCENARIO_DEFAULT],
            [['aset_must_changed_password'], 'integer', 'except' => static::SCENARIO_VISIT_COUNT],
            [['aset_must_changed_password'], 'integer', 'on' => static::SCENARIO_ADMIN_SETTING],
            ['brithdate', 'safe'],
            ['others', 'safe'],
            [['firstname', 'nickname', 'lastname', 'avatar_url', 'description', 'melli_code', 'state', 'city', 'address', 'brithdate', 'nationality', 'religion'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
        ];
        foreach ($this->othersMetaFields() as $field) {
            $return[] = [
                $field['field'],
                'string'
            ];
        }
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $return = [
            'user_id' => Yii::t('rabint', 'User ID'),
            'firstname' => Yii::t('rabint', 'Firstname'),
            'nickname' => Yii::t('rabint', 'Nickname'),
            'lastname' => Yii::t('rabint', 'Lastname'),
            'locale' => Yii::t('rabint', 'Locale'),
            'gender' => Yii::t('rabint', 'Gender'),
            'phone' => Yii::t('rabint', 'Phone'),
            'cell' => Yii::t('rabint', 'Cell'),
            'country' => Yii::t('rabint', 'Country'),
            'state' => Yii::t('rabint', 'State'),
            'city' => Yii::t('rabint', 'City'),
            'address' => Yii::t('rabint', 'Address'),
            'melli_code' => Yii::t('rabint', 'شماره ملی/کد ثبت/شماره معرفی نامه'),
            'brithdate' => Yii::t('rabint', 'Brithdate'),
            'education' => Yii::t('rabint', 'Education'),
            'education_field' => Yii::t('rabint', 'Education Field'),
            'nationality' => Yii::t('rabint', 'ملیت'),
            'religion' => Yii::t('rabint', 'مذهب'),
            'description' => Yii::t('rabint', 'توضیحات پروفایل'),
            'avatar_url' => Yii::t('rabint', 'تصویر کاربر'),
            'avatar' => Yii::t('rabint', 'تصویر کاربر'),
            'admin_setting' => Yii::t('rabint', 'تنظیمات مدیر'),
            'aset_max_upload_size' => Yii::t('rabint', 'بیشترین حجم قابل آپلود'),
            'aset_must_changed_password' => Yii::t('rabint', 'تعویض رمز در اولین ورود'),
            'channel_visit' => Yii::t('rabint', 'میزان بازدید پروفایل'),
            'others' => Yii::t('rabint', 'دیگر اطلاعات'),
            'sejel_id' => Yii::t('rabint', 'شماره شناسنامه'),
            'sejel_serial' => Yii::t('rabint', 'سریال شناسنامه'),
        ];
        $other_fileds = config('SERVICE.user.other_profile_fields', []);
        foreach ($other_fileds() as $field) {
            if (!empty($field['title']))
                $return[$field['field']] = $field['title'];
        }
        return $return;
    }

    /**
     * @return \common\models\base\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return null|string
     */
    public function getFullName()
    {
        if ($this->firstname || $this->lastname) {
            $res = implode(' ', [$this->firstname, $this->lastname]);
            return \yii\helpers\Html::encode($res);
        }
        return null;
    }

    /**
     * @param null $default
     * @return bool|null|string
     */
    public function getAvatar($default = null, $size = 'medium')
    {
        if (empty($this->avatar_url)) {
            if (empty($default)) {
                return $size == 'medium' ? \yii\helpers\Url::home(1) . '/img/noAvatarMedium.png' : \yii\helpers\Url::home(1) . '/img/noAvatarTiny.png';
            }
            return $default;
        }
        return \rabint\attachment\models\Attachment::getUrlByPath($this->avatar_url, $size);
    }

    /**
     * @return type
     * @deprecated since version 1
     */
    public function getDisplayName()
    {
        return $this->user->displayName;
//        if (!empty($this->nickname)) {
//            $return = $this->nickname;
//            return \yii\helpers\Html::encode($return);
//        }
//        if ($this->firstname || $this->lastname) {
//            $return = implode(' ', [$this->firstname, $this->lastname]);
//            return \yii\helpers\Html::encode($return);
//        }
//        return $this->user->username;
    }

    public function incChannelVisit()
    {
        $this->scenario = self::SCENARIO_VISIT_COUNT;
        $this->channel_visit = $this->channel_visit + 1;
        $this->save();
        return $this->channel_visit;
    }

    public function validateEmail($validate = TRUE, $flash = true)
    {
        $res = self::updateAll(['email_activated' => ($validate ? 1 : 0)], ['user_id' => $this->user_id]);
        if (\rabint\helpers\user::hasRole(User::ROLE_USER, $this->user_id)) {
            $auth = Yii::$app->authManager;
            $auth->revokeAll($this->user_id);
            $auth->assign($auth->getRole(User::ROLE_CONTRIBUTOR), $this->user_id);
            $flash && Yii::$app->session->setFlash('success', \Yii::t('rabint', 'کاربر گرامی! سطح کاربری شما ارتقاء یافت.'));
        }
        return true;
    }

    public function validateCell($validate = TRUE, $flash = true)
    {
        $res = self::updateAll(['cell_activated' => ($validate ? 1 : 0)], ['user_id' => $this->user_id]);
        if (
            \rabint\helpers\user::hasRole(User::ROLE_USER, $this->user_id) ||
            \rabint\helpers\user::hasRole(User::ROLE_CONTRIBUTOR, $this->user_id)
        ) {
            $auth = Yii::$app->authManager;
            $auth->revokeAll($this->user_id);
            $auth->assign($auth->getRole(User::ROLE_AUTHOR), $this->user_id);
            $flash && Yii::$app->session->setFlash('success', \Yii::t('rabint', 'کاربر گرامی! سطح کاربری شما ارتقاء یافت.'));
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if ($this->brithdate !== null && $this->brithdate !== '' && !is_numeric($this->brithdate)) {
            $this->brithdate = \rabint\helpers\locality::anyToTimeStamp($this->brithdate);
        }
        if (is_array($this->others)) {
            $this->others = json_encode($this->others);
        }
        return parent::beforeSave($insert);
    }

}
