<?php

namespace rabint\user\models\query;

use rabint\models\MysqlToMongoActiveQuery;
use rabint\user\models\User;
use common\models\base\ActiveQuery;

/**
 * Class UserQuery
 * @package rabint\user\models\query
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UserQuery extends ActiveQuery {

    /**
     * @return $this
     */
    public function notDeleted() {
        $this->andWhere(['!=', 'status', User::STATUS_DELETED]);
        return $this;
    }

    /**
     * @return $this
     */
    public function active() {
        $this->andWhere(['status' => User::STATUS_ACTIVE]);
        return $this;
    }

    public function cell($cell) {
        $condition = "id in (SELECT user_id from user_profile  WHERE "
                . "cell like :keyword )";
        $this->andWhere(new \yii\db\Expression($condition, ['keyword' => "%" . $cell . "%"]));
        return $this;
    }

    public function role($role = 'user') {
        $condition = "id in (SELECT user_id from user_rbac_auth_assignment  WHERE item_name = :role )";
        $this->andWhere(new \yii\db\Expression($condition, ['role' => $role]));
        return $this;
    }

}
