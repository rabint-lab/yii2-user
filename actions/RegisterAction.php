<?php


namespace rabint\user\actions;

use rabint\user\models\form\RegisterForm;
use rabint\user\Module;
use yii\base\Action;

class RegisterAction extends Action
{

    public function run($identity, $token, $redirect = null)
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
        return $this->render('@vendor/rabint/user/views/sign-in/register.php', [
            'model' => $model
        ]);
    }
}
