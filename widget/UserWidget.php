<?php

/**
 * news widget
 * @author Mojtaba Akbarzadeh <ingenious@chmail.com>
 * @copyright (c) rabint, sahife data producers
 */

namespace rabint\user\widget;

/**
 * Class login
 */
class UserWidget extends \yii\bootstrap\Widget {

    var $style = 'default';
    var $redirect = \rabint\helpers\uri::RD_REFERRER; //remember,dashboard, url...
    var $title = null;
    var $model = null;

    public function init() {
        if (empty($this->model)) {
            $this->model = new \rabint\user\models\LoginForm();
        }
        if ($this->title === null) {
            $this->title = \Yii::t('rabint', 'ورود کاربران');
        }
//        if ($this->redirect == 'referer') {
//            \yii\helpers\Url::remember();
//            $this->redirect = 'remember';
//        }
        parent::init();
    }

    public function run() {
        return $this->render('UserWidget/' . $this->style, [
                    'title' => $this->title,
                    'redirect' => $this->redirect,
                    'model' => $this->model,
        ]);
    }

}
