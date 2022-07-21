<?php

namespace rabint\user\models;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;

/**
 * Account form
 */
class

AccountForm extends Model {

    public $redirect="";
    public $username;
    public $email;
    public $password;
    public $password_confirm;
    private $user;

    public function setUser($user) {
        $this->user = $user;
        $this->email = $user->email;
        $this->username = $user->username;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                ['redirect', 'safe'],
                ['username', 'filter', 'filter' => 'trim'],
                ['username', 'required'],
                ['username', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'This username has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ],
                ['username', 'match', 'pattern' => '/^[a-zA-z0-9_]\w*$/i', 'message' => Yii::t('rabint', 'فقط حروف لاتین ، اعداد و خط زیر مجاز است')],
                ['username', 'string', 'min' => 5, 'max' => 255],
                ['email', 'filter', 'filter' => 'trim'],
                ['email', 'required'],
                ['email', 'email'],
                ['email', 'unique',
                'targetClass' => '\rabint\user\models\User',
                'message' => Yii::t('rabint', 'This email has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ],
                ['password', 'string'],
                [
                'password_confirm',
                'required',
                'when' => function($model) {
                    return !empty($model->password);
                },
                'whenClient' => new JsExpression("function (attribute, value) {
                    return $('#caccountform-password').val().length > 0;
                }")
            ],
                ['password_confirm', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false],
        ];
    }

    public function attributeLabels() {
        return [
            'username' => Yii::t('rabint', 'Username'),
            'email' => Yii::t('rabint', 'Email'),
            'password' => Yii::t('rabint', 'Password'),
            'password_confirm' => Yii::t('rabint', 'Confirm Password')
        ];
    }

    public function save() {
        $this->user->username = $this->username;
        $this->user->email = $this->email;
        if ($this->password) {
            $this->user->setPassword($this->password);
        }
        return $this->user->save();
    }

}
