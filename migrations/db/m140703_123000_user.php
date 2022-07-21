<?php


use rabint\user\models\User;
use yii\db\Migration;

class m140703_123000_user extends Migration
{
    static  $a= "aasdasdasd";
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(32)->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'access_token' => $this->string(40)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'oauth_client' => $this->string(),
            'oauth_client_user_id' => $this->string(),
            'email' => $this->string(),
            'mobile' => $this->bigInteger()->unsigned(),
            'status' => $this->smallInteger()->notNull()->defaultValue(User::STATUS_ACTIVE),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'logged_at' => $this->integer()
        ],$tableOptions);

        $this->createTable('{{%user_profile}}', [
            'user_id' => $this->primaryKey(),
            'nickname' => $this->string(),
            'firstname' => $this->string(),
            'lastname' => $this->string(),
            'avatar_url' => $this->string(),
            'locale' => $this->char(5)->defaultValue('fa_IR'),
            //'locale' => $this->char(5)->notNull()->defaultValue('fa_IR'),
            'gender' => $this->smallInteger(1)
        ],$tableOptions);

        $this->addForeignKey('fk_user', '{{%user_profile}}', 'user_id', '{{%user}}', 'id', 'cascade', 'cascade');

    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user', '{{%user_profile}}');
        $this->dropTable('{{%user_profile}}');
        $this->dropTable('{{%user}}');

    }
}
