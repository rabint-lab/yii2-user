<?php

namespace rabint\user\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use rabint\user\models\User;
use yii\db\ActiveQuery;

/**
 * UserSearch represents the model behind the search form about `rabint\user\models\User`.
 */
class AdminUserSearch extends User
{

    public $role = '';
    public $melli_code = '';
    public $cell = '';
    public $displayName = '';
    public $realName = '';
    public $gender = '';
    public $parent_id = null;
    public $group = '';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at', 'logged_at', 'gender', 'parent_id', 'group'], 'integer'],
            [['displayName', 'realName'], 'string'],
            [['role', 'username', 'auth_key', 'password_hash', 'email', 'melli_code', 'cell'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider | ActiveQuery
     */
    public function search($params, $returnQuery = false)
    {
        $query = User::find()->alias('u');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ($this->group != '') {
            $condition = "u.id in (SELECT user_id from grp_group_user  WHERE "
                . "group_id in (:keyword) "
                . "group by user_id )";
            $query->andWhere(new \yii\db\Expression($condition, ['keyword' => $this->group]));
        }

        if (!($this->load($params) && $this->validate())) {

            return ($returnQuery) ? $query : $dataProvider;
        }

        $query->andFilterWhere([
            'u.id' => $this->id,
            'u.status' => $this->status,
            'u.created_at' => $this->created_at,
            'u.updated_at' => $this->updated_at,
            'u.logged_at' => $this->logged_at,
            'u.parent_id' => $this->parent_id
        ]);

        $query->andFilterWhere(['like', 'u.username', $this->username])
            ->andFilterWhere(['like', 'u.auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'u.password_hash', $this->password_hash])
            // ->andFilterWhere(['like', 'u.password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'u.email', $this->email]);

        if (!empty($this->role)) {
            if (!is_array($this->role)) {
                $condition = "u.id in (SELECT user_id from user_rbac_auth_assignment  WHERE item_name like :keyword )";
                $query->andWhere(new \yii\db\Expression($condition, ['keyword' => $this->role]));
            } else {
                $ky1 = $this->role[0] ?? '';
                $ky2 = $this->role[1] ?? '';
                $ky3 = $this->role[2] ?? '';
                $condition = "u.id in (SELECT user_id from user_rbac_auth_assignment  WHERE item_name in ( :key1 , :key2 , :key3 ) )";
                $query->andWhere(new \yii\db\Expression($condition, ['key1' => $ky1, 'key2' => $ky2, 'key3' => $ky3]));
            }
        }
        if (!empty($this->displayName)) {
            $condition = "u.id in (SELECT user_id from user_profile  WHERE "
                . "user_profile.firstname like :keyword or  "
                . "user_profile.lastname like :keyword or  "
                . "user_profile.nickname like :keyword  "
                . " )";
            $query->andWhere(new \yii\db\Expression($condition, ['keyword' => "%" . $this->displayName . "%"]));
        }
        if (!empty($this->realName)) {
            $condition = "u.id in (SELECT user_id from user_profile  WHERE "
                . "firstname like :keyword or  "
                . "lastname like :keyword "
                . " )";
            $query->andWhere(new \yii\db\Expression($condition, ['keyword' => "%" . $this->realName . "%"]));
        }
        if (!empty($this->melli_code)) {
            $condition = "u.id in (SELECT user_id from user_profile  WHERE "
                . "melli_code like :keyword )";
            $query->andWhere(new \yii\db\Expression($condition, ['keyword' => "%" . $this->melli_code . "%"]));
        }
        if (!empty($this->cell)) {
            $condition = "u.id in (SELECT user_id from user_profile  WHERE "
                . "cell like :keyword )";
            $query->andWhere(new \yii\db\Expression($condition, ['keyword' => "%" . $this->cell . "%"]));
        }
        if (!empty($this->gender)) {
            $condition = "u.id in (SELECT user_id from user_profile  WHERE "
                . "gender = :keyword )";
            $query->andWhere(new \yii\db\Expression($condition, ['keyword' => $this->gender]));
        }

        return ($returnQuery) ? $query : $dataProvider;
    }

}
