<?php

namespace rabint\user\controllers;

use backend\models\AccountForm;
use rabint\user\models\AdminUserForm;
use rabint\user\models\search\AdminUserSearch;
use rabint\user\models\User;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class AdminController extends \rabint\controllers\AdminController
{

    public function behaviors()
    {
        $ret = parent::behaviors();
        return $ret + [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['post'],
                    ],
                ],
                'access' => [
                    'class' => \yii\filters\AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['administrator', 'manager', 'viewUsers'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['login-as'],
                            'roles' => ['administrator', 'loginAs'],
                        ]
                        // everything else is denied
                    ],
                ],
            ];
    }

    public function actions()
    {
        return [
//            'avatar-upload' => [
//                'class' => UploadAction::className(),
//                'deleteRoute' => 'avatar-delete',
//                'on afterSave' => function ($event) {
//                    /* @var $file \League\Flysystem\File */
//                    $file = $event->file;
//                    $img = ImageManagerStatic::make($file->read())->fit(215, 215);
//                    $file->put($img->encode());
//                }
//            ],
//            'avatar-delete' => [
//                'class' => DeleteAction::className()
//            ]
        ];
    }

    public function actionProfile()
    {
        $model = Yii::$app->user->identity->userProfile;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('rabint', 'Your profile has been successfully saved', [], $model->locale));
            return $this->refresh();
        }
        return $this->render('profile', ['model' => $model]);
    }

    public function actionAccount()
    {
        $user = Yii::$app->user->identity;
        $model = new AccountForm();
        $model->username = $user->username;
        $model->email = $user->email;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->username = $model->username;
            $user->email = $model->email;
            if ($model->password) {
                $user->setPassword($model->password);
            }
            $user->save();
            Yii::$app->session->setFlash('success', Yii::t('rabint', 'Your account has been successfully saved'));
            return $this->refresh();
        }
        return $this->render('account', ['model' => $model]);
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AdminUserSearch();
        if (!Yii::$app->user->can('viewUsers')) {
            throw new ForbiddenHttpException(yii::t('rabint', 'متاسفانه به این صفحه دسترسی ندارید.'));
        }

        if (!Yii::$app->user->can('administrator') and !Yii::$app->user->can('manageَUsers')) {
            $groups = \app\modules\group\models\GroupUser::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->select('group_concat(group_id) grp ')
                ->asArray()
                ->all();
            $searchModel->group = $groups[0]['grp'];
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'roles' => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description')
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        if (!Yii::$app->user->can('viewUsers')) {
            throw new ForbiddenHttpException(yii::t('rabint', 'متاسفانه به این صفحه دسترسی ندارید.'));
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
            'roles' => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description')
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can('createUsers')) {
            throw new ForbiddenHttpException(yii::t('rabint', 'متاسفانه به این صفحه دسترسی ندارید.'));
        }
        $model = new AdminUserForm();
        $model->setScenario('create');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'roles' => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description')
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateUser()
    {
        if (!Yii::$app->user->can('createUsers')) {
            throw new ForbiddenHttpException(yii::t('rabint', 'متاسفانه به این صفحه دسترسی ندارید.'));
        }
        $model = new AdminUserForm();
        $sw = 1;
        $request = Yii::$app->request;
        if ($model->load(Yii::$app->request->post())) {
            $userExists = User::findByUsername($model->username);
            if (!$userExists) {
                $model->setScenario('createUser'); # must be defined
                if (!$model->save()) {
                    $sw = 0;
                }
            }
            if ($sw) {
                $modelRelation = new \app\modules\user_group\models\UserGroupRelation();
                $modelRelation->user_id = (!$userExists) ? $model->id : $userExists->id;
                $modelRelation->grade_id = 1;
                $modelRelation->group_id = $model->group;
                if (!$modelRelation->save()) {
                    Yii::$app->session->setFlash('warning', \Yii::t('rabint', 'خطا در بخش دسترسی'));
                    return $this->redirect(['create-user']);
                }
            }
        }
        return $this->render('create_user', [
            'model' => $model
        ]);
    }

    public function actionValidateUser($username)
    {
        $count = User::findByUsername($username);
        if (($count)) {
            return 1;
        } else {
            return 0;
        }

    }

    /**
     * Updates an existing User model.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can('manageَUsers')) {
            throw new ForbiddenHttpException(yii::t('rabint', 'متاسفانه به این صفحه دسترسی ندارید.'));
        }
        $model = new AdminUserForm();
        $model->setModel($this->findModel($id));
        if ($id == 0) {
            Yii::$app->session->setFlash(
                'warning',
                \Yii::t('rabint', 'کاربر گرامی!.')
                . "<br/>"
                . \Yii::t('rabint', 'کاربر با نقش حسابداری قابلیت ویرایش ندارد ')
            );
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(
                'success',
                \Yii::t('rabint', 'کاربر گرامی!.')
                . "<br/>"
                . \Yii::t('rabint', 'عملیات با موفقیت انجام شد  . ')
            );
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'user_id' => $id,
            'model' => $model,
            'roles' => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description')
        ]);
    }

    /**
     * permissions
     * @param type $id
     * @return type
     */
    public function actionUpdatePermissions($id)
    {

        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $userRoles = Yii::$app->authManager->getRolesByUser($model->id);
        $userPerms = Yii::$app->authManager->getPermissionsByUser($model->id);
        $systemPerms = Yii::$app->authManager->getPermissions();

        $rolePerms = [];
        foreach ($userRoles as $role) {
            foreach (Yii::$app->authManager->getPermissionsByRole($role->name) as $perm) {
                $rolePerms[$perm->name] = $perm->description;
            }
        }

        $permsCanRemove = [];
        foreach ($userPerms as $perm) {
            if (!array_key_exists($perm->name, $rolePerms))
                $permsCanRemove[$perm->name] = $perm->description;
        }

        $permsCanAdd = [];
        foreach ($systemPerms as $perm) {
            if (!array_key_exists($perm->name, $rolePerms))
                $permsCanAdd[$perm->name] = $perm->description;
        }
        asort($permsCanRemove);
        asort($permsCanAdd);

        if ($request->isPost) {
            if ($request->post('add_btn') !== null) {
                if (array_key_exists($request->post('add'), $permsCanAdd)) {
                    $permission = Yii::$app->authManager->getPermission($request->post('add'));
                    Yii::$app->authManager->assign($permission, $model->id);
                    return $this->redirect(['update-permissions', 'id' => $model->id]);
                }
            }
            if ($request->post('remove_btn') !== null) {
                if (array_key_exists($request->post('remove'), $permsCanRemove)) {
                    $permission = Yii::$app->authManager->getPermission($request->post('remove'));
                    Yii::$app->authManager->revoke($permission, $model->id);
                    return $this->redirect(['update-permissions', 'id' => $model->id]);
                }
            }
        }
        return $this->render('update-permissions', [
            'model' => $model,
            'userPerms' => $userPerms,
            'userRoles' => $userRoles,
            'rolePerms' => $rolePerms,
            'permsCanRemove' => ($permsCanRemove),
            'permsCanAdd' => ($permsCanAdd)
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->user->can('manageَUsers')) {
            throw new ForbiddenHttpException(yii::t('rabint', 'متاسفانه به این صفحه دسترسی ندارید.'));
        }

        Yii::$app->authManager->revokeAll($id);
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionExport()
    {
        if (!\rabint\helpers\user::can("administrator")) {
            return $this->redirect(['index']);
        }
        $searchModel = new AdminUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        echo \rabint\widgets\ExcelListView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'id',
                    'filterOptions' => ['style' => 'max-width:100px;'],
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'username',
                    'value' => function ($model) {
                        return $model->username;
                    },
                ],
                [
                    'attribute' => 'email',
                ],
                [
                    'attribute' => 'melli_code',
                    'label' => \Yii::t('rabint', 'کد ملی'),
                    'value' => function ($model) {
                        return $model->userProfile->melli_code;
                    },
                ],
                [
                    'attribute' => 'realName',
                    'label' => \Yii::t('rabint', 'نام کاربر'),
                    'value' => function ($model) {
                        return $model->userProfile->firstname . ' ' . $model->userProfile->lastname;
                    },
                ],
                [
                    'attribute' => 'displayName',
                    'label' => \Yii::t('rabint', 'نام نمایشی'),
                    'value' => function ($model) {
                        return $model->userProfile->nickname;
                    },
                ],
                [
                    'attribute' => 'role',
                    'label' => \Yii::t('rabint', 'نقش کاربری'),
                    'class' => \rabint\components\grid\EnumColumn::className(),
                    'enum' => \yii\helpers\ArrayHelper::getColumn(User::globalRoles(), 'title'),
                    'value' => function ($model) {
                        return \rabint\helpers\user::roleTitle($model->id);
                    },
                ],
                [
                    'attribute' => 'gender',
                    'label' => \Yii::t('rabint', 'جنسیت'),
                    'class' => \rabint\components\grid\EnumColumn::className(),
                    'enum' => [
                        1 => Yii::t('rabint', 'مرد'),
                        2 => Yii::t('rabint', 'زن'),
                    ],
                    'value' => function ($model) {
                        return $model->userProfile->gender;
                    },
                ],
                [
                    'class' => \rabint\components\grid\JDateColumn::className(),
                    'attribute' => 'created_at',
                ],
                [
                    'class' => \rabint\components\grid\JDateColumn::className(),
                    'attribute' => 'logged_at',
                ],
//                    [
//                    'attribute' => 'wallet',
//                    'label' => \Yii::t('rabint', 'موجودی حساب'),
//                    'value' => function($model) {
//                        return number_format(\rabint\finance\models\FinanceWallet::credit($model->id)) . ' ' . \Yii::t('rabint', 'تومان');
//                        ;
//                    },
//                    'visible' => class_exists('rabint\finance\models\FinanceWallet')
//                ],
                [
                    'attribute' => 'status',
                    'class' => \rabint\components\grid\EnumColumn::className(),
                    'enum' => \yii\helpers\ArrayHelper::getColumn(User::statuses(), 'title'),
                ],
            ],
        ]);
    }

    public function actionAjaxUserList($q = null, $id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '', 'usage' => '']];
        if (!is_null($q)) {
            $data = User::find()
                ->alias("t")
                ->select(new \yii\db\Expression('id, concat(t.username," - ",p.nickname)  AS text'))
                ->leftJoin('user_profile p', 't.id = p.user_id')
                ->where([
                    'OR',
                    ['like', 't.username', $q],
                    ['like', 'p.nickname', $q],
                ])
//                    ->andWhere([">", 'is_official', 1])
                ->limit(20)
                ->asArray()
                ->all();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Tag::findOne($id)->title, 'usage' => ''];
        }
        return $out;
    }

}
