<?php

use yii\db\Migration;

class m210806_095604_profile_sejel extends Migration
{
    /**
     * @return bool|void
     */
    public function up()
    {
        $this->addColumn('{{%user_profile}}', 'sejel_id', $this->integer()->comment('شماره شناسنامه'));
        $this->addColumn('{{%user_profile}}', 'sejel_serial', $this->string(190)->comment('سریال شناسنامه'));
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->dropColumn('{{%user_profile}}', 'sejel_id');
        $this->dropColumn('{{%user_profile}}', 'sejel_serial');
    }
}
