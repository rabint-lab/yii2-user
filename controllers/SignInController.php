<?php


namespace rabint\user\controllers;


use rabint\helpers\str;
use rabint\helpers\uri;
use rabint\user\models\form\ActivationForm;
use rabint\user\models\form\LoginForm;
use rabint\user\models\form\PasswordForm;
use rabint\user\models\form\RegisterForm;
use rabint\user\models\User;
use rabint\user\models\UserToken;
use rabint\user\Module;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Response;

class SignInController extends \rabint\controllers\DefaultController
{
    var $defaultAction = 'login';

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
                            'actions' => [
                                'login',
                                'register',
                                'activation',
                                'register-form',
                                'reset-password',

                            ],
                            'allow' => true,
                            'roles' => ['?']
                        ],
                        [
                            'actions' => [
                                'login',
                                'register',
                                'activation',
                                'reset-password'
                            ],
                            'allow' => false,
                            'roles' => ['@'],
                            'denyCallback' => function () {
                                return Yii::$app->controller->redirect(uri::dashboardRoute());
                            }
                        ],
                        [
                            'actions' => ['logout'],
                            'allow' => true,
                            'roles' => ['@'],
                        ]
                    ]
                ],
            ];
    }


    public function actionLogin($redirect = null)
    {
        /**
         * save default redirect page
         */
        $redirect = $this->_defaultRedirect($redirect);

        $model = new LoginForm();
        $model->redirect = $redirect;
        if ($model->load(Yii::$app->request->post())) {
            $model->getUser();
            /**
             * user not exists ###############################################################
             */
            if (!$model->user) {
                if (Module::getConfig('enableActivation') && Module::getConfig('mergeRegisterAndLogin')) {
                    return $this->redirect(['activation', 'identity' => $model->identity, 'redirect' => $model->redirect]);
                } else {
                    Yii::$app->session->setFlash('danger', Yii::t('rabint', 'کاربری با این اطلاعات یافت نشد'));
                    return $this->redirect(['login', 'redirect' => $model->redirect]);
                }
            }
            /**
             * user exists ###################################################################
             */

            //check user status:
            if ($model->user->status == User::STATUS_BANNED) {
                Yii::$app->session->setFlash(
                    'danger', \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا به مدیریت تماس بگیرید')
                );
                return $this->render('login', [
                    'model' => $model
                ]);
            }

            /**
             * if user not active (invited or deActiveate)
             */
            if ($model->user->status != User::STATUS_ACTIVE) {
                return $this->redirect(['activation', 'identity' => $model->identity, 'redirect' => $model->redirect]);
            }

            if (!empty($model->password) && $model->login()) {
                return $this->redirect($model->redirect);
            }

        }
        return $this->render('login-password', [
            'model' => $model
        ]);


        /**
         * render default view
         */
        return $this->render('login', [
            'model' => $model
        ]);
    }

    /**
     * فراموشی رمز و فعالسازی حساب کاربری
     * @param $identity
     * @param null $redirect
     * @return string
     */
    public function actionActivation($identity = '', $redirect = null)
    {
        if (!Module::getConfig('enableActivation')) {
            Yii::$app->session->setFlash('danger', Yii::t('rabint', 'امکان فعال سازی حساب کاربری و ثبت نام وجود ندارد.'));
            return $this->redirect(['login']);
        }

        $redirect = $this->_defaultRedirect($redirect);

        $model = new ActivationForm();
        $model->redirect = $redirect;
        $model->identity = Yii::$app->request->post('ActivationForm')['identity'] ?? $identity;
        if ($model->load(Yii::$app->request->post())) {

            $model->user = User::getUserByIdentity($model->identity);

            /**
             *  check user not block !!!
             */
            if ($model->user && $model->user->status == User::STATUS_BANNED) {
                Yii::$app->session->setFlash(
                    'danger', \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا به مدیریت تماس بگیرید')
                );
                return $this->redirect(['login', 'redirect' => $model->redirect]);

                return $this->render('login', [
                    'model' => $model
                ]);
            }

            /**
             * do activation and send to set password
             */
            if ($model->user && !empty($model->token)) {
                if ($model->checkActivationCode()) {
                    if ($model->user->status == User::STATUS_INVITED) {
                        $must_register = true;
                    } else {
                        $must_register = false;
                    }

                    if (!Module::getConfig('mustFillProfile')) {
                        $model->user->status = User::STATUS_ACTIVE;
                        $model->user->save(false);
                    }
                    $regToken = str::unique() . str::random(10);
                    $_SESSION['tmp_session_ckeck_password'] = $regToken;
                    $_SESSION['tmp_session_ckeck_user_identity'] = $model->identity;
                    if (!$must_register) {
                        return $this->redirect(['reset-password', 'identity' => $model->identity, 'token' => $regToken, 'redirect' => $model->redirect]);
                    }
                    return $this->redirect(['register', 'identity' => $model->identity, 'token' => $regToken, 'redirect' => $model->redirect]);
                } else {
                    /**
                     * render default view
                     */
                    Yii::$app->session->setFlash(
                        'danger', \Yii::t('rabint', 'کد فعال سازی وارد شده نا معتبر است و یا زمان استفاده از آن گذشته است')
                    );
                    return $this->render('activation', [
                        'model' => $model
                    ]);
                }


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
                    Yii::$app->session->setFlash(
                        'danger', \Yii::t('rabint', 'شناسه وارد شده نا معتبر است')
                    );
                    return $this->render('activation', [
                        'model' => $model
                    ]);
                }
                if (!$newUser->save(false)) {
                    Yii::$app->session->setFlash(
                        'danger', \Yii::t('rabint', 'امکان ارسال کد فعال سازی برای این شناسه ممکن نیست')
                    );
                    return $this->redirect(['login', 'identity' => $model->identity, 'redirect' => $model->redirect]);
                }
                $newUser->afterSignup(['cell' => $newUser->mobile]);
                $model->user = $newUser;
            }

            /**
             * sending activation code
             */
            if (UserToken::userRecentlyGetToken($model->user->id, UserToken::TYPE_ACTIVATION)) {
                Yii::$app->session->setFlash(
                    'danger', \Yii::t('rabint', 'کاربر گرامی! کد فعال سازی قبلی شما هنوز ابطال نشده است ، لطفا از همان کد استفاده نمایید یا به مدت دو وقیقه صبر نمایید.')
                );
                return $this->render('activation-token', [
                    'model' => $model
                ]);
            }
            if ($model->sendActivation()) {

                Yii::$app->session->setFlash(
                    'success', \Yii::t('rabint', 'کد فعال سازی برای شما ارسال گردید')
                );
                return $this->render('activation-token', [
                    'model' => $model
                ]);
            } else {
                /**
                 * render default view
                 */
                Yii::$app->session->setFlash(
                    'danger', \Yii::t('rabint', 'امکان ارسال کد فعال سازی برای این شناسه ممکن نیست')
                );
                //return $this->redirect(['activation', 'identity' => $model->identity, 'redirect' => $model->redirect]);
            }

        }
        return $this->render('activation', [
            'model' => $model
        ]);
    }

    public function actionResetPassword($identity, $token, $redirect = null)
    {
        $redirect = $this->_defaultRedirect($redirect);
        $r1 = (isset($_SESSION['tmp_session_ckeck_password'])) && ($_SESSION['tmp_session_ckeck_password'] == $token);
        $r2 = (isset($_SESSION['tmp_session_ckeck_user_identity'])) && ($_SESSION['tmp_session_ckeck_user_identity'] == $identity);

        if (!$r1 OR !$r2) {
            Yii::$app->session->setFlash(
                'danger', \Yii::t('rabint', 'کاربرگرامی، صفحه مورد نظر معتبر نیست')
            );
            return $this->redirect(['login', 'redirect' => $redirect]);
        }

        $user = User::getUserByIdentity($identity);

        if (empty($user)) {
            Yii::$app->session->setFlash(
                'danger', \Yii::t('rabint', 'کاربرگرامی، صفحه مورد نظر معتبر نیست')
            );
            return $this->redirect(['login', 'redirect' => $redirect]);
        }

        /**
         *  check user not invited !!!
         */
        if ($user && $user->status == User::STATUS_INVITED) {
            Yii::$app->session->setFlash('danger', Yii::t('rabint', 'کاربر گرامی! لطفا از بخش ثبت نام اقدام به فعال سازی حساب خود نمایید.'));
            return $this->redirect(['login']);
        }

        /**
         *  check user not block !!!
         */
        if ($user && $user->status == User::STATUS_BANNED) {
            Yii::$app->session->setFlash(
                'danger', \Yii::t('rabint', 'کاربر گرامی، حساب کاربری شما موقتا مسدود شده است. لطفا به مدیریت تماس بگیرید')
            );
            return $this->redirect(['login', 'redirect' => $model->redirect]);

            return $this->render('login', [
                'model' => $model
            ]);
        }

        $model = new PasswordForm();
        $model->redirect = $redirect;
        $model->identity = $identity;
        if ($model->load(Yii::$app->request->post())) {
            $model->getUser($identity);

            if ($model->setPassword()) {
                if (Yii::$app->user->login($model->getUser(), Module::getConfig('sessionExpireTime'))) {
                    return $this->redirect(['login', 'redirect' => $redirect]);
                }
                return $this->redirect(['login', 'redirect' => $redirect]);
            }
            Yii::$app->session->setFlash(
                'danger', \Yii::t('rabint', 'خطا در ذخیره سازی اطلاعات')
            );

        }
        return $this->render('set-password', [
            'model' => $model
        ]);


    }

    public function actionRegister($identity, $token, $redirect = null)
    {

        if (!Module::getConfig('enableRegistration')) {
            Yii::$app->session->setFlash('danger', Yii::t('rabint', 'امکان ثبت نام وجود ندارد.'));
            return $this->redirect(['login']);
        }
        $redirect = $this->_defaultRedirect($redirect);
        $r1 = (isset($_SESSION['tmp_session_ckeck_password'])) && ($_SESSION['tmp_session_ckeck_password'] == $token);
        $r2 = (isset($_SESSION['tmp_session_ckeck_user_identity'])) && ($_SESSION['tmp_session_ckeck_user_identity'] == $identity);

        if (!$r1 OR !$r2) {
            Yii::$app->session->setFlash(
                'danger', \Yii::t('rabint', 'کاربرگرامی، صفحه مورد نظر معتبر نیست')
            );
            return $this->redirect(['login', 'redirect' => $redirect]);
        }

        $regAction = Module::getConfig('register_action');

        return $this->run($regAction, ['identity' => $identity, 'token' => $token, 'redirect' => $redirect]);
//        $model = new RegisterForm();
//        $model->redirect = $redirect;
//        $model->identity = $identity;
//        if ($model->load(Yii::$app->request->post())) {
//            $model->getUser($identity);
//
//            if ($model->signup()) {
//                if (Yii::$app->user->login($model->getUser(), Module::getConfig('sessionExpireTime'))) {
//                    return $this->redirect(['login', 'redirect' => $redirect]);
//                }
//                return $this->redirect(['login', 'redirect' => $redirect]);
//            }
////            var_dump($model->errors);
////            die('=---');
//            Yii::$app->session->setFlash(
//                'danger', \Yii::t('rabint', 'خطا در ذخیره سازی اطلاعات')
//            );
//            //save data
//            //do login
//            //redirect to $redirect
//
//        }
//        return $this->render('register', [
//            'model' => $model
//        ]);
    }

    public function actionRegisterForm($identity, $token, $redirect = null)
    {
        $model = new RegisterForm();
        $model->redirect = $redirect;
        $model->identity = $identity;
        if ($model->load(Yii::$app->request->post())) {
            $model->getUser($identity);

            if ($model->signup()) {
                if (Yii::$app->user->login($model->getUser(), Module::getConfig('sessionExpireTime'))) {
                    return $this->redirect(['login', 'redirect' => $redirect]);
                }
                return $this->redirect(['login', 'redirect' => $redirect]);
            }
//            var_dump($model->errors);
//            die('=---');
            Yii::$app->session->setFlash(
                'danger', \Yii::t('rabint', 'خطا در ذخیره سازی اطلاعات')
            );
            //save data
            //do login
            //redirect to $redirect

        }
        return $this->render('register', [
            'model' => $model
        ]);
    }

    /**
     * @return Response
     */
    public function actionLogout($redirect = null)
    {
        if ($redirect == null) {
            $redirect = Module::getConfig('defaultLogoutRedirect') ?: Yii::$app->homeUrl;
        }
        $adminUser = Yii::$app->session->get('adminUser') ?? false;
        Yii::$app->user->logout();
        if ($adminUser) {
            Yii::$app->user->login(User::findOne($adminUser));
        }
        return $this->redirect($redirect);
    }

    /**
     * @param $identity
     * @return bool
     */
    protected function _defaultRedirect($redirect = null)
    {
        if ($redirect == null) {
            $redirect = Module::getConfig('defaultLoginRedirect');
            if (empty($redirect)) {
                 $defaultPage = Url::to(uri::dashboardRoute(), true);
                 $redirect = Yii::$app->request->referrer ?: $defaultPage;
            }
        }
        return $redirect;
    }

}
