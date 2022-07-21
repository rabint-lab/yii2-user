<?php


use rabint\user\models\User;
use yii\db\Migration;

class m181201_123000_user_profile extends Migration
{
    static  $a= "aasdasdasd";
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_profile}}', 'phone', $this->string(13));
        $this->addColumn('{{%user_profile}}', 'cell', $this->string(13));
        $this->addColumn('{{%user_profile}}', 'melli_code', $this->string(10));
        $this->addColumn('{{%user_profile}}', 'postal_code', $this->string(10));
        $this->addColumn('{{%user_profile}}', 'brithdate', $this->integer());
        $this->addColumn('{{%user_profile}}', 'education', $this->string(20));
        $this->addColumn('{{%user_profile}}', 'education_field', $this->string(63));
        $this->addColumn('{{%user_profile}}', 'nationality', $this->string(2));
        $this->addColumn('{{%user_profile}}', 'religion', $this->string(31));
        $this->addColumn('{{%user_profile}}', 'channel_visit', $this->integer());
        $this->addColumn('{{%user_profile}}', 'officiality', $this->integer());
        $this->addColumn('{{%user_profile}}', 'reagent_id', $this->integer());
        $this->addColumn('{{%user_profile}}', 'country', $this->string(2));
        $this->addColumn('{{%user_profile}}', 'state', $this->string(30));
        $this->addColumn('{{%user_profile}}', 'city', $this->string(45));
        $this->addColumn('{{%user_profile}}', 'address', $this->text());
        $this->addColumn('{{%user_profile}}', 'description', $this->text());
        $this->addColumn('{{%user_profile}}', 'admin_setting', $this->text());
        $this->addColumn('{{%user_profile}}', 'dashboard_setting', $this->text());
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {

        $this->dropColumn('{{%user_profile}}', 'phone');
        $this->dropColumn('{{%user_profile}}', 'cell');
        $this->dropColumn('{{%user_profile}}', 'melli_code');
        $this->dropColumn('{{%user_profile}}', 'postal_code');
        $this->dropColumn('{{%user_profile}}', 'brithdate');
        $this->dropColumn('{{%user_profile}}', 'education');
        $this->dropColumn('{{%user_profile}}', 'education_field');
        $this->dropColumn('{{%user_profile}}', 'nationality');
        $this->dropColumn('{{%user_profile}}', 'religion');
        $this->dropColumn('{{%user_profile}}', 'channel_visit');
        $this->dropColumn('{{%user_profile}}', 'officiality');
        $this->dropColumn('{{%user_profile}}', 'reagent_id');
        $this->dropColumn('{{%user_profile}}', 'country');
        $this->dropColumn('{{%user_profile}}', 'state');
        $this->dropColumn('{{%user_profile}}', 'city');
        $this->dropColumn('{{%user_profile}}', 'address');
        $this->dropColumn('{{%user_profile}}', 'description');
        $this->dropColumn('{{%user_profile}}', 'admin_setting');
        $this->dropColumn('{{%user_profile}}', 'dashboard_setting');

    }
}