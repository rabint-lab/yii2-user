<?php

namespace rabint\user\models;

use rabint\user\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class ChangePasswordForm extends Model {

    /**
     * @var
     */
    public $redirect="";
    public $password;
    public $confirm;

    /**
     * @var \rabint\user\models\UserToken
     */

    /**
     * Creates a form model given a token.
     *
     * @param  string                          $token
     * @param  array                           $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($config = []) {
        if (Yii::$app->user->isGuest) {
            throw new InvalidParamException(\Yii::t('rabint', 'لطفا ابتدا لاگین نمایید.'));
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['redirect', 'safe'],
                ['password', 'required'],
                ['password', 'string', 'min' => 6],
                ['confirm', 'required'],
                ['confirm', 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('rabint', "تکرار کلمه عبور صحیح نیست")],
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function changePassword() {
        $user = Yii::$app->user->identity;
        $user->password = $this->password;
        if ($user->save(false)) {
            $profile = UserProfile::findOne(['user_id' => $user->id]);
            if (!empty($profile)) {
                $profile->scenario = UserProfile::SCENARIO_ADMIN_SETTING;
                $profile->aset_must_changed_password = 0;
                $profile->save();
            }
        }else{
            \Yii::$app->session->setFlash('danger', \rabint\helpers\str::modelErrors($user->errors));
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function attributeLabels() {
        return [
            'password' => Yii::t('rabint', 'رمز جدید'),
            'confirm' => Yii::t('rabint', 'تکرار رمز')
        ];
    }

}
