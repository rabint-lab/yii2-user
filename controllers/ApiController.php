<?php


namespace rabint\user\controllers;


use rabint\helpers\str;
use rabint\user\models\form\ActivationForm;
use rabint\user\models\form\LoginForm;
use rabint\user\models\form\PasswordForm;
use rabint\user\models\form\RegisterForm;
use rabint\user\models\User;
use rabint\user\models\UserProfile;
use rabint\user\models\UserToken;
use rabint\user\Module;
use Yii;
use yii\helpers\Html;

class ApiController extends \rabint\controllers\NewRestApiController
{

    public function behaviors()
    {
        $ret = parent::behaviors();
        unset($ret['authenticator']);
        return $ret;
    }

    public function actionCheckuser()
    {
        $body = json_decode(Yii::$app->request->getRawBody(), 1);
        if (!isset($body['identity'])) {
            return $this->error(1);
        }
        $res = User::getUserByIdentity($body['identity']);

        return [
            'found' => ($res ? true : false)
        ];
    }

    public function actionLogin()
    {

        $model = new LoginForm();

        $body = json_decode(Yii::$app->request->getRawBody(), 1);

        if (!isset($body['identity']) OR !isset($body['password'])) {
            return $this->error(1);
        }
        $remember = false;
        if (isset($body['remember']) and !empty($body['remember'])) {
            $remember = true;
        }

        $model->identity = $body['identity'];
        $model->password = $body['password'];

        $model->getUser();
        /**
         * user not exists ###############################################################
         */
        if (!$model->user) {
            return $this->error(110, Yii::t('rabint', 'کاربری با این اطلاعات یافت نشد'));
        }
        /**
         * user exists ###################################################################
         */

        //check user status:
        if ($model->user->status == User::STATUS_BANNED) {
            return $this->error(111, \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا به مدیریت تماس بگیرید'));
        }

        /**
         * if user not active (invited or deActiveate)
         */
        if ($model->user->status != User::STATUS_ACTIVE) {
            return $this->error(112, \Yii::t('rabint', 'حساب کاربری شما فعال نشده است و نیاز به فعال شدن دارد.'));
        }

        if ($model->login()) {
            $token_type = $remember ? UserToken::TYPE_ASYNC_LOGIN_REMEMMBER : UserToken::TYPE_ASYNC_LOGIN;
            $token_postfix = 'z' . str::uniqueHash();
            $tokenModel = UserToken::create($model->user->id, $token_type, null, $token_postfix);
            return [
                "id" => $model->user->id,
                "username" => $model->user->username,
                "access_token_header" => 'X-User-Api-Key',
                "access_token" => $tokenModel->token,
                "email" => $model->user->email,
                "mobile" => $model->user->mobile,
                "status" => $model->user->status,
                "logged_at" => $model->user->logged_at
            ];
        }
        return $this->error(113, 'نام کاربری یا رمز عبور اشتباه است');
    }

    /**
     * فراموشی رمز و فعالسازی حساب کاربری
     * @param $identity
     * @param null $redirect
     * @return string
     */
    public function actionCheckActivation()
    {

        $body = json_decode(Yii::$app->request->getRawBody(), 1);
        if (!isset($body['identity']) OR empty($body['identity'])) {
            return $this->error(1);
        }
        if (!isset($body['token']) OR empty($body['token'])) {
            return $this->error(1);
        }
        $remember = false;
        if (isset($body['remember']) and !empty($body['remember'])) {
            $remember = true;
        }

        if (!Module::getConfig('enableActivation')) {
            return $this->error(121, Yii::t('rabint', 'امکان فعال سازی وجود ندارد، لطفا با پشتیبانی تماس بگیرید'));
        }


        $model = new ActivationForm();
        $model->identity = $body['identity'];
        $model->token = $body['token'];

        $model->user = User::getUserByIdentity($model->identity);

        if (!$model->user) {
            return $this->error(132, \Yii::t('rabint', 'چنین کاربری با این شناسه یافت نشد!'));
        }

        /**
         *  check user not block !!!
         */
        if ($model->user->status == User::STATUS_BANNED) {
            return $this->error(122, \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا با پشتیبانی تماس بگیرید'));
        }

        if ($model->user->status == User::STATUS_ACTIVE) {
            $need_register = false;
        } else {
            $need_register = true;
        }
        /**
         * do activation and send to set password
         */
        if ($model->checkActivationCode()) {
            if (!Module::getConfig('mustFillProfile')) {
                $model->user->status = User::STATUS_ACTIVE;
                $model->user->save(false);
            }
            $regToken = str::unique() . str::random(10);
            //$_SESSION['tmp_session_ckeck_password'] = $regToken;
            //$_SESSION['tmp_session_ckeck_user_identity'] = $model->identity;

            $token_type = $remember ? UserToken::TYPE_ASYNC_LOGIN_REMEMMBER : UserToken::TYPE_ASYNC_LOGIN;
            $token_postfix = 'z' . str::uniqueHash();
            $tokenModel = UserToken::create($model->user->id, $token_type, null, $token_postfix);

            return ['activated' => true, 'need_register' => $need_register, 'identity' => $model->identity, 'token' => $tokenModel->token, 'register_token' => $regToken];
        } else {
            return $this->error(135, \Yii::t('rabint', 'کد فعال سازی وارد شده نا معتبر است و یا زمان استفاده از آن گذشته است'));
        }
    }


    public function actionSendActivation()
    {

        $body = json_decode(Yii::$app->request->getRawBody(), 1);
        if (!isset($body['identity'])) {
            return $this->error(1);
        }

        if (!Module::getConfig('enableActivation')) {
            return $this->error(121, Yii::t('rabint', 'امکان فعال سازی وجود ندارد، لطفا با پشتیبانی تماس بگیرید'));
        }

        //send activation
        //$res = User::getUserByIdentity();

        $model = new ActivationForm();
        $model->identity = $body['identity'];


        $model->user = User::getUserByIdentity($model->identity);

        /**
         *  check user not block !!!
         */
        if ($model->user && $model->user->status == User::STATUS_BANNED) {
            return $this->error(122, \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا با پشتیبانی تماس بگیرید'));
        }


        /**
         * user not exists ###############################################################
         */
        if (!$model->user) {
            $newUser = new User();
            $newUser->status = User::STATUS_INVITED;
            $newUser->password = str::random();
            if (Module::baseAuthenticateOnEmail() && filter_var($model->identity, FILTER_VALIDATE_EMAIL)) {
                //activation by email
                $newUser->email = $model->identity;
                $newUser->username = $model->identity;
            } elseif (Module::baseAuthenticateOnMobile() && str::isValidCellphone($model->identity)) {
                //activation by mobile
                $newUser->mobile = str::formatCellphone($model->identity);
                $newUser->username = $newUser->mobile;
            } else {
                //error on validation
                return $this->error(123, \Yii::t('rabint', 'شناسه وارد شده نا معتبر است'));
            }
            if (!$newUser->save(false)) {
                return $this->error(124, \Yii::t('rabint', 'امکان ارسال کد فعال سازی برای این شناسه ممکن نیست'));
            }
            $newUser->afterSignup(['cell' => $newUser->mobile]);
            $model->user = $newUser;
        }

        /**
         * sending activation code
         */
        if (UserToken::userRecentlyGetToken($model->user->id, UserToken::TYPE_ACTIVATION)) {
            return $this->error(125, \Yii::t('rabint', 'کاربر گرامی! کد فعال سازی قبلی شما هنوز ابطال نشده است ، لطفا از همان کد استفاده نمایید یا به مدت دو وقیقه صبر نمایید.'));
        }
        if (!$model->sendActivation()) {
            return $this->error(126, \Yii::t('rabint', 'امکان ارسال کد فعال سازی برای این شناسه ممکن نیست'));
        }
        return ['sent' => true];
    }


    public function actionSetPassword(){

        $body = json_decode(Yii::$app->request->getRawBody(), 1);
        if (!isset($body['identity']) OR empty($body['identity'])) {
            return $this->error(1);
        }
        if (!isset($body['token']) OR empty($body['token'])) {
            return $this->error(1);
        }
        if (!isset($body['regToken']) OR empty($body['regToken'])) {
            return $this->error(1);
        }

        $user = User::getUserByIdentity($body['identity']);

        if (!$user) {
            return $this->error(132, \Yii::t('rabint', 'چنین کاربری با این شناسه یافت نشد!'));
        }


        if (!Module::getConfig('enableRegistration') && $user->status != User::STATUS_ACTIVE) {
            return $this->error(141, Yii::t('rabint', 'امکان ثبت نام وجود ندارد.'));
        }

        /**
         *  check user not block !!!
         */
        if ($user->status == User::STATUS_BANNED) {
            return $this->error(122, \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا با پشتیبانی تماس بگیرید'));
        }

        $tokenModel = UserToken::find()->notExpired()->byToken($body['token'])->one();

        if ($user->id != $tokenModel->user_id) {
            return $this->error(142, \Yii::t('rabint', 'توکن ثبت نام یا تغییر رمز شما نا معتبر است'));
        }

        $model = new PasswordForm();
        $model->identity = $body['identity'];
        $model->user = $user;
        $model->password = $body['password'] ?? '';
        $model->confirm = $body['confirm'] ?? '';

        if (!$model->setPassword()) {
            $errors = str::modelErrToStr($model->errors);
            return $this->error(143, \Yii::t('rabint', 'خطا در ذخیره سازی اطلاعات') . '. ' . $errors);
        }

        return ['set-password' => true, 'login_access_token' => $tokenModel->token];

    }

    public function actionRegister()
    {

        $body = json_decode(Yii::$app->request->getRawBody(), 1);
        if (!isset($body['identity']) OR empty($body['identity'])) {
            return $this->error(1);
        }
        if (!isset($body['token']) OR empty($body['token'])) {
            return $this->error(1);
        }
        if (!isset($body['regToken']) OR empty($body['regToken'])) {
            return $this->error(1);
        }

        $user = User::getUserByIdentity($body['identity']);



        if (!$user) {
            return $this->error(132, \Yii::t('rabint', 'چنین کاربری با این شناسه یافت نشد!'));
        }


        if (!Module::getConfig('enableRegistration') && $user->status != User::STATUS_ACTIVE) {
            return $this->error(141, Yii::t('rabint', 'امکان ثبت نام وجود ندارد.'));
        }
        /**
         *  check user not block !!!
         */
        if ($user->status == User::STATUS_BANNED) {
            return $this->error(122, \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا با پشتیبانی تماس بگیرید'));
        }

        $tokenModel = UserToken::find()->notExpired()->byToken($body['token'])->one();

        if ($user->id != $tokenModel->user_id) {
            return $this->error(142, \Yii::t('rabint', 'توکن ثبت نام یا تغییر رمز شما نا معتبر است'));
        }

        $model = new RegisterForm();
        $model->identity = $body['identity'];
        $model->user = $user;
        $model->name = $body['name'] ?? '';
        $model->family = $body['family'] ?? '';
        $model->nickname = $body['nickname'] ?? Html::decode($model->name . ' ' . $model->family);
        $model->password = $body['password'] ?? '';
        $model->confirm = $body['confirm'] ?? '';

        if (!$model->signup()) {
            $errors = str::modelErrToStr($model->errors);
            return $this->error(143, \Yii::t('rabint', 'خطا در ذخیره سازی اطلاعات') . '. ' . $errors);
        }

        /**
         * save other fields:
         */
        $profile = UserProfile::findOne(['user_id' => $model->user->id]);
        $others = [];
        foreach ($body as $pfkey => $pfval) {
            if (in_array($pfkey, ['identity', 'name', 'family', 'nickname', 'password', 'confirm'])) {
                continue;
            }
            if (in_array($pfkey, ['avatar_url', 'locale', 'gender', 'phone', 'cell', 'melli_code', 'postal_code', 'brithdate', 'education', 'education_field', 'nationality', 'religion', 'channel_visit', 'officiality', 'reagent_id', 'country', 'state', 'city', 'address'])) {
                $profile->$pfkey = $pfval;
            } else {
                $others[$pfkey] = $pfval;
            }
        }
        $profile->others = $others;
        $res = $profile->save(false);

        return ['registered' => true, 'login_access_token' => $tokenModel->token];
    }

}
