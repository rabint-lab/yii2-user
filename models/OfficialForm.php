<?php

namespace rabint\user\models;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;

/**
 * Account form
 */
class

OfficialForm extends Model {

    public $redirect="";
    public $isNewRecord = false;
    private $model;
    public $firstname;
    public $lastname;
    public $phone;
    public $cell;
    public $address;
    public $melli_code;
    public $melli_cart_url;
    public $dataisvalid;

    public function setModel($model) {
        $this->model = $model;
        $this->firstname = $model->firstname;
        $this->lastname = $model->lastname;
        $this->phone = $model->phone;
        $this->cell = $model->cell;
        $this->address = $model->address;
        $this->melli_code = $model->melli_code;
        $this->melli_cart_url = $model->melli_cart_url;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['redirect', 'safe'],
            [['melli_code', 'melli_cart_url', 'firstname', 'lastname', 'cell', 'address'], 'required'],
            [['firstname', 'nickname', 'lastname'], 'string', 'max' => 255],
            [['phone', 'cell'], 'string', 'max' => 11],
            ['dataisvalid', 'integer', 'min' => 1, 'max' => 1, 'tooBig' => \Yii::t('rabint', 'شما باید اطلاعات فوق را مطالعه نموده و صحت آن را تایید نمایید.'), 'tooSmall' => \Yii::t('rabint', 'شما باید اطلاعات فوق را مطالعه نموده و صحت آن را تایید نمایید.')],
        ];
    }

    public function attributeLabels() {
        return [
            'firstname' => Yii::t('rabint', 'نام'),
            'lastname' => Yii::t('rabint', 'نام خانوادگی'),
            'phone' => Yii::t('rabint', 'تلفن ثابت'),
            'cell' => Yii::t('rabint', 'تلفن همراه'),
            'address' => Yii::t('rabint', 'آدرس'),
            'melli_code' => Yii::t('rabint', 'شماره ملی/شماره نامه/ کد ثبت'),
            'melli_cart_url' => Yii::t('rabint', 'تصویر کارت ملی یا نامه معرفی'),
            'dataisvalid' => Yii::t('rabint', 'صحت اطلاعات فوق را تایید می کنم.'),
        ];
    }

    public function save() {
        $this->model->firstname = $this->firstname;
        $this->model->lastname = $this->lastname;
        $this->model->phone = $this->phone;
        $this->model->cell = $this->cell;
        $this->model->address = $this->address;
        $this->model->melli_code = $this->melli_code;
        $this->model->melli_cart_url = $this->melli_cart_url;

        return $this->model->save();
    }

}
