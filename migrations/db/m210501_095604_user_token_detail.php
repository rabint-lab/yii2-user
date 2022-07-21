<?php

use yii\db\Migration;

class m210501_095604_user_token_detail extends Migration
{
    /**
     * @return bool|void
     */
    public function up()
    {
        $this->addColumn('{{%user_token}}', 'agent', $this->string(190));
        $this->addColumn('{{%user_token}}', 'ip', $this->string(48));
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->dropColumn('{{%user_token}}', 'agent');
        $this->dropColumn('{{%user_token}}', 'ip');
    }
}
