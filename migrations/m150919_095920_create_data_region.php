<?php

use yii\db\Schema;
use yii\db\Migration;

class m150919_095920_create_data_region extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%locality}}', [
            'id' => $this->primaryKey(),
            'level' => $this->integer(1),
            'kld_subjcode' => $this->integer(2),
            'kld_regcode' => $this->integer(3)->defaultValue(0),
            'kld_citycode' => $this->integer(3)->defaultValue(0),
            'kladr_code' => $this->string(11)->defaultValue(NULL),
            'name' => $this->string(64)->notNull(),
            'status' => $this->boolean()->defaultValue(TRUE),
        ]);
        
        $this->createTable('{{%poiskstroek_data}}', [
            'id' => $this->primaryKey(),
            'kladr_code' => $this->string(11),
            'name' => $this->string(64)->notNull(),
            'construct_sum' => $this->decimal(15, 2)->notNull()->defaultValue('0.00'),
            'design_sum' => $this->decimal(15, 2)->notNull()->defaultValue('0.00'),
            'construct_count' => $this->integer(11)->defaultValue(0),
            'design_count' => $this->integer(11)->defaultValue(0),
            'construct_companies' => $this->integer(11)->defaultValue(0),
            'design_companies' => $this->integer(11)->defaultValue(0),
            'img' => $this->string(64),
            'created' => $this->timestamp()->notNull(),
            'updated' => $this->timestamp()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%locality}}');
        $this->dropTable('{{%poiskstroek_data}}');
    }
}
