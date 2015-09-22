<?php

use yii\db\Schema;
use yii\db\Migration;

class m150919_095920_create_data_region extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%region}}', [
            'id' => $this->primaryKey(),
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

        $this->createTable('{{%district}}', [
            'id' => $this->primaryKey() . ' AUTO_INCREMENT',
            'region_id' => $this->integer(11),
            'kladr_code' => $this->integer(11),
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

        $this->addForeignKey('dist_region_fk', '{{%district}}', 'region_id', '{{%region}}', 'id');

        $this->createTable('{{%city}}', [
            'id' => $this->primaryKey() . ' AUTO_INCREMENT',
            'region_id' => $this->integer(11),
            'kladr_code' => $this->integer(11),
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

        $this->addForeignKey('city_region_fk', '{{%city}}', 'region_id', '{{%region}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('dist_region_fk', '{{%district}}');
        $this->dropForeignKey('city_region_fk', '{{%city}}');
        $this->dropTable('{{%region}}');
        $this->dropTable('{{%district}}');
        $this->dropTable('{{%city}}');
    }
}
