<?php

namespace rabint\user\models;


use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class AdminRoleForm extends Model
{

    public $name="";
    public $description="";
    public $permisions=[];

    public $isNewRecord=true;

    public static function systemRolse(){
        return [
            'administrator','manager','author','contributor','user'
        ];

    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            ['name', 'match', 'pattern' => '/^[a-z]\w*$/i','message' => 'فقط حروف کوچک لاتین و بدون فاصله مجاز است'],
            ['name', 'unique',
                'targetClass'=>AuthItems::class,
                'message' => Yii::t('rabint', 'این نام برای نقش کاربری تکراری است'),
            ],
            ['description', 'string'],
            ['permisions', 'each','rule'=>['string']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rabint', 'نام'),
            'description' => Yii::t('rabint', 'عنوان یا توضیحات'),
            'permisions' => Yii::t('rabint', 'دسترسی ها'),
        ];
    }

    public static function isSystemRole($name){
        return in_array($name,static::systemRolse());
    }

    public function delete(){
        if($this->isSystemRole($this->name)){
            return false;
        }
        $role = \Yii::$app->authManager->getRole($this->name);
        return \Yii::$app->authManager->remove($role);
    }
    public function save(){


        if($this->isSystemRole($this->name)){
            $role = \Yii::$app->authManager->getRole($this->name);
            $role->description = $this->description;
            $res = \Yii::$app->authManager->update($this->name,$role);
            //save only description
            if($res){
                return true;
            }
            return false;
        }
        //new
        if($this->isNewRecord){
            $role = \Yii::$app->authManager->createRole($this->name);
            $role->description = $this->description;
            \Yii::$app->authManager->add($role);
        }else{
            //update
            $role = \Yii::$app->authManager->getRole($this->name);
            $role->description = $this->description;
            \Yii::$app->authManager->update($this->name,$role);
        }
        //remove premisions:
        $oldPerms =ArrayHelper::getColumn(\Yii::$app->authManager->getPermissionsByRole($role->name),'name');

        $newPerms = (is_array($this->permisions)?$this->permisions:[]) ;
        
        $unchangePerms = array_intersect($oldPerms,$newPerms);
        $mustRemovePerms = array_diff($oldPerms,$unchangePerms);
        $mustAddPerms = array_diff($newPerms,$unchangePerms);
        foreach ($mustRemovePerms as $rmPerm){
            $perm =\Yii::$app->authManager->getPermission($rmPerm);
            \Yii::$app->authManager->removeChild($role,$perm);
        }
        foreach ($mustAddPerms as $addPerm){
            $perm =\Yii::$app->authManager->getPermission($addPerm);
            \Yii::$app->authManager->addChild($role,$perm);
        }
        return true;
    }

    public function loadRole($name){
        $role = \Yii::$app->authManager->getRole($name);
        if(empty($role)){
            return false;
        }
        $this->name = $role->name;
        $this->description = $role->description;

        $this->isNewRecord =false;

        //$this->permisions = ArrayHelper::map(\Yii::$app->authManager->getPermissionsByRole($name),'name','description');
        $this->permisions = ArrayHelper::getColumn(\Yii::$app->authManager->getPermissionsByRole($name),'name');

        return true;
        //todo: load premisions
    }
}
