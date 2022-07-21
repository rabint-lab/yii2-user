<?php


use rabint\user\models\User;
use yii\db\Migration;

class m210424_123000_user_profile_other extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_profile}}', 'others', $this->text());
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user_profile}}', 'others');
    }
}