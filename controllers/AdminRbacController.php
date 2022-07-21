<?php

namespace rabint\user\controllers;

use backend\models\AccountForm;
use rabint\user\models\AdminRoleForm;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
use Yii;
use yii\imagine\Image;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class AdminRbacController extends \rabint\controllers\AdminController {

    public function behaviors() {
        $ret = parent::behaviors();
        return $ret + [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                        [
                        'allow' => true,
                        'roles' => ['administrator'],
                    ],
                // everything else is denied
                ],
            ],
        ];
    }

    /**
     * Lists all User rolse.
     * @return mixed
     */
    public function actionIndex() {
        if(!Yii::$app->user->can('administrator')){
            throw new ForbiddenHttpException(Yii::t('app', 'شما اجازه دسترسی به این صفحه را ندارید'));
        }

        return $this->render('index', [
                    'roles' => Yii::$app->authManager->getRoles(),
        ]);
    }

    /**
     * update  a User Role.
     * @param string $name
     * @return mixed
     */
    public function actionDetail($name="") {
        if(!Yii::$app->user->can('administrator')){
            throw new ForbiddenHttpException(Yii::t('app', 'شما اجازه دسترسی به این صفحه را ندارید'));
        }
        $model = new AdminRoleForm();
        if(!empty($name) && !$model->loadRole($name)){
            throw new NotFoundHttpException(Yii::t('app', 'این نقش کاربری یافت نشد'));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }


        return $this->render('detail', [
                    'role' => Yii::$app->authManager->getRole($name),
                    'model' => $model,
                    'readonly' => $model->isSystemRole($name),
        ]);
    }

    /**
     * update  a User Role.
     * @param string $name
     * @return mixed
     */
    public function actionDelete($name="") {
        if(!Yii::$app->user->can('administrator')){
            throw new ForbiddenHttpException(Yii::t('app', 'شما اجازه دسترسی به این صفحه را ندارید'));
        }
        $model = new AdminRoleForm();
        if(!empty($name) && !$model->loadRole($name)){
            throw new NotFoundHttpException(Yii::t('app', 'این نقش کاربری یافت نشد'));
        }
        if($model->delete()){
            Yii::$app->session->setFlash('success', \Yii::t('rabint', 'دسترسی مورد نظر حذف شد'));
        }else{
            Yii::$app->session->setFlash('warning', \Yii::t('rabint', 'خطا در حذف دسترسی'));
        }
        return $this->redirect(['index']);
    }


}
