<?php

namespace rabint\user\controllers;

use rabint\helpers\str;
use rabint\helpers\user;
use rabint\models\MultiModel;
use rabint\user\models\AccountForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;

class DefaultController extends \rabint\controllers\PanelController
{

    var $layout = '@theme/views/layouts/main';

    /**
     * @return array
     * @return array
     */
    public function actions()
    {
        return [
            'avatar-upload' => [
                'class' => 'rabint\attachment\actions\UploadAction',
                'modelName' => \rabint\user\models\UserProfile::className(),
                'attribute' => 'avatar_url',
                'type' => 'image',
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $ret = parent::behaviors();
        return $ret + [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@']
                        ]
                    ]
                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'avatar-upload' => ['post']
                    ],
                ],
//            'environment' => [
//                'class' => \rabint\filters\EnvironmentFilter::className(),
//                'actions' => [
//                    'index' => 'panel',
//                    'profile' => 'panel',
//                ],
//            ],
            ];
    }

    public function actionIndex()
    {

        if (\rabint\helpers\user::isGuest()) {
            Yii::$app->session->setFlash('warning', \Yii::t('rabint', 'لطفا ابتدا وارد حساب کاربری خود شوید'));
            return $this->redirect(['/user/sign-in/login']);
        }
        $user_id = \rabint\helpers\user::id();
        $User = \rabint\user\models\User::findOne(['id' => $user_id]);

        if(!user::isProfileFull()){
            Yii::$app->session->setFlash('warning',
                \Yii::t(
                    'rabint',
                    'کاربر گرامی! اطلاعات حساب کاربری شما ناقص است، لطفا قبل از استفاده از سیستم ، {link} خود را کامل نمایید تا از همه امکانات سیستم بهره‌مند شوید.',
                ['link'=>Html::a(Yii::t('app', 'اطلاعات کاربری'),['/user/default/profile'])]
            )
            );
        }
        $EmailValidationSent = (isset($_SESSION['EmailValidationSent']) and $_SESSION['EmailValidationSent']) ? TRUE : FALSE;
        if (\rabint\helpers\user::profile()->email_activated == 0 AND !$EmailValidationSent) {//&& !\rabint\helpers\user::can('postCreate')
            Yii::$app->session->setFlash('warning',
                \Yii::t('rabint', 'کاربر گرامی! ایمیل شما فعال نشده است.') .
                '<br/>' .
                \Yii::t('rabint', 'برای  دسترسی بیشتر نظیر «ارسال صوت» ، «ارسال ویدئو»  و... نیاز به فعال سازی ایمیل دارید')
                . \yii\helpers\Html::a(\Yii::t('rabint', 'فعال سازی ایمیل'), ['/user/sign-in/email-validation'], ['class' => 'btn btn-success btn-xs'])
            );
        } elseif (\rabint\helpers\user::profile()->cell_activated == 0) {

        }
        unset($_SESSION['EmailValidationSent']);

        return $this->render('index', [
            'user' => $User,

        ]);


//        $this->layout = '@theme/views/layouts/common';
//        $dashboardContent = \app\models\Page::findOne(['slug' => 'user-dashboard']);
//        if ($dashboardContent !== NULL) {
//            $dashboardContent = $dashboardContent->body;
//        }
//        return $this->render('index', [
//                    'dashboardContent' => $dashboardContent,
//        ]);
    }

    public function actionProfile()
    {
        $accountForm = new AccountForm();
        $accountForm->setUser(Yii::$app->user->identity);
        $profileModel = Yii::$app->user->identity->userProfile;
        $model = new MultiModel([
            'models' => [
                //'account' => $accountForm,
                'profile' => $profileModel
            ]
        ]);
        /* =================================================================== */

        $EmailValidationSent = (isset($_SESSION['EmailValidationSent']) and $_SESSION['EmailValidationSent']) ? TRUE : FALSE;
        if (\rabint\helpers\user::profile()->email_activated == 0 AND !$EmailValidationSent) {//&& !\rabint\helpers\user::can('postCreate')
            Yii::$app->session->setFlash('warning',
                \Yii::t('rabint', 'کاربر گرامی! ایمیل شما فعال نشده است.')
                . "<br/>"
                . \Yii::t('rabint', 'برای  دسترسی بیشتر نظیر «ارسال صوت» ، «ارسال ویدئو»  و... نیاز به فعال سازی ایمیل دارید.') . ' '
                . \yii\helpers\Html::a(\Yii::t('rabint', 'فعال سازی ایمیل'), ['/user/sign-in/email-validation'], ['class' => 'btn btn-success btn-xs'])
            );
        } elseif ($profileModel->cell_activated == 0) {

        }
        unset($_SESSION['EmailValidationSent']);
        /* =================================================================== */
//        print_r(Yii::$app->request->post());
//        print_r($model);
//        die('--');
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('rabint', 'Your account has been successfully saved'));
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('danger', str::modelErrors($model->errors));
            }
        }
        return $this->render('profile', ['model' => $model]);
    }

    public function actionOfficialData()
    {
        if (\rabint\helpers\user::isGuest()) {
            Yii::$app->session->setFlash('warning', \Yii::t('rabint', 'لطفا ابتدا وارد حساب کاربری خود شوید'));
            return $this->redirect(['/user/sign-in/login']);
        }
        if (\rabint\helpers\user::object()->isOfficial) {
            Yii::$app->session->setFlash('success', \Yii::t('rabint', 'کاربر گرامی!‌شما هم اکنون کاربر رسمی هستید.'));
            return $this->redirect(['profile']);
        }
        $model = new \rabint\user\models\OfficialForm();
        $model->setModel(Yii::$app->user->identity->userProfile);
        /* =================================================================== */
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $user = \rabint\helpers\user::object();
                $user->scenario = 'adminEdit';
//                $user->is_official = \rabint\user\models\User::IS_OFFICIAL_WAITING;
                $user->save();
                Yii::$app->notify->send(
                    NULL, \Yii::t('rabint', 'کاربر {displayName} درخواست رسمی شدن پروفایل خود را داشته است.', ['displayName' => \rabint\helpers\user::name()]), ['/user/admin/update', 'id' => \rabint\helpers\user::id()], ['priority' => \app\modules\notify\models\Notification::PRIORITY_LOW]
                );
                Yii::$app->session->setFlash('success', Yii::t('rabint', 'درخواست شما با موفقیت  ثبت گردید و در اسرع وقت بررسی می گردد.'));
                return $this->redirect(['profile']);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('rabint', 'خطا در ثبت اطلاعات.'));
            }
        }
        return $this->render('official-data', ['model' => $model]);
    }

     public function actionLoginAs($uid){
        if(!user::can('loginAs')){
            throw new \yii\web\ForbiddenHttpException(\Yii::t('rabint', 'شما به این صفحه دسترسی ندارید.'));
        }
        $defaultPage = Url::to(\rabint\helpers\uri::dashboardRoute(), true);
        $lastUid = \rabint\helpers\user::id();
//        $redirect = Yii::$app->request->referrer ?: $defaultPage;
        
//        if($uid == null and $token = null ){
//            return $this->redirect($redirect);
//        }
//        
//        if($token != null){
//            $user  = User::findOne(['access_token'=>$token]);
//            if(!$user){
//                throw new NotFoundHttpException(\Yii::t('rabint', 'token is invalid !'));
//            }
//            Yii::$app->user->login($user);
//            return $this->redirect(['login-as']);
//        }else{
            $user  = \rabint\user\models\User::findOne($uid);
            if(!$user){
                throw new NotFoundHttpException(\Yii::t('rabint', 'user is invalid !'));
            }
            Yii::$app->user->logout();
            
            Yii::$app->session->set('adminUser',$lastUid );
            Yii::$app->user->login($user);
            
            return $this->redirect($defaultPage);
//        }

    }

    public function actionChangePassword($redirect = null, $cancel = 0)
    {
        if ($cancel == 1) {
            return $this->doRedirect($redirect);
        }
        $model = new \rabint\user\models\ChangePasswordForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->changePassword()) {
            if (!empty($model->redirect)) {
                $redirect = $model->redirect;
            }
            Yii::$app->session->setFlash('success', \Yii::t('rabint', 'New password was saved.'));
            return $this->doRedirect($redirect);
        }
        $model->redirect = (empty($redirect)) ? \rabint\helpers\uri::referrer() : $redirect;
        return $this->render('changePassword', [
            'model' => $model,
            'redirect' => $redirect,
        ]);
    }



    protected function doRedirect($redirect = null)
    {
        return \rabint\helpers\uri::redirectTo($redirect, true);
    }


}
