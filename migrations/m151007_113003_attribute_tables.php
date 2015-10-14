<?php

use yii\db\Schema;
use yii\db\Migration;
use yii\db\Expression;

class m151007_113003_attribute_tables extends Migration
{
    public function safeUp()
    {
		$this->execute("SET search_path TO map");
		
		$this->createTable('{{%attribute_type}}', [
			'id' => $this->primaryKey(),
			'parent_id' => $this->integer(11)->defaultValue(NULL),
			'name' => $this->string(64)->notNull(),
			'alias' => $this->string(64)->notNull(),
			'created' => $this->timestamp()->notNull(),
			'updated' => $this->timestamp()->notNull(),
			'status' => $this->boolean()->defaultValue(TRUE),
		]);
		
		$this->createTable('{{%vector}}', [
			'id' => $this->primaryKey(),
			'name' => $this->string(64)->notNull(),
			'alias' => $this->string(64)->notNull(),
			'created' => $this->timestamp()->notNull(),
			'updated' => $this->timestamp()->notNull(),
			'status' => $this->boolean()->defaultValue(TRUE),
		]);
		
		$this->insert('{{%vector}}', ['name' => "инновации", 'alias' => "innovacii", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "промышленность", 'alias' => "promishlennost", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "бизнес", 'alias' => "biznes", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "здравоохранение", 'alias' => "zdravoohranenie", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "образование", 'alias' => "obrazovanie", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "культура молодежная политика и спорт", 'alias' => "kultura-molodejnaya-politika-i-sport", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "коммуникации", 'alias' => "kommunikacii", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "безопасность", 'alias' => "bezopasnost", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "местное самоуправление", 'alias' => "mestnoe-samoupravlenie", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "градостроительство", 'alias' => "gradostroitelstvo", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "жкх", 'alias' => "jkh", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		$this->insert('{{%vector}}', ['name' => "экология", 'alias' => "ekologiya", 'created' => new Expression('NOW()'), 'updated' => new Expression('NOW()')]);
		
		$this->createTable('{{%link_vector_to_attr_type}}', [
			'vector_id' => $this->integer(11)->notNull(),
			'attr_type_id' => $this->integer(11)->notNull(),
		]);
		
		$this->createTable('{{%measure}}', [
			'id' => $this->primaryKey(),
			'name' => $this->string(64)->notNull(),
			'created' => $this->timestamp()->notNull(),
			'updated' => $this->timestamp()->notNull(),
			'status' => $this->boolean()->defaultValue(TRUE),
		]);
		
		$this->createTable('{{%attribute}}', [
			'id' => $this->primaryKey(),
			'attr_type_id' => $this->integer(11)->notNull(),
			'kladr_code' => $this->string(11)->notNull(),
			'value' => $this->string(32)->defaultValue(NULL),
			'measure_id' => $this->integer(11)->defaultValue(NULL),
			'date' => $this->date()->defaultValue(NULL),
			'created' => $this->timestamp()->notNull(),
			'updated' => $this->timestamp()->notNull(),
			'progress' => $this->string(1)->defaultValue(NULL),
			'status' => $this->boolean()->defaultValue(TRUE),
		]);
		
		$this->addForeignKey('fk_attr_attr_type', '{{%attribute}}', 'attr_type_id', '{{%attribute_type}}', 'id', 'CASCADE');
		$this->addForeignKey('fk_attr_measure', '{{%attribute}}', 'measure_id', '{{%measure}}', 'id', 'CASCADE');
		$this->addForeignKey('fk_link_vector', '{{%link_vector_to_attr_type}}', 'vector_id', '{{%vector}}', 'id', 'CASCADE');
		$this->addForeignKey('fk_link_attr_type', '{{%link_vector_to_attr_type}}', 'attr_type_id', '{{%attribute_type}}', 'id', 'CASCADE');
		
		$this->execute("SET search_path TO public");
    }

    public function safeDown()
    {
        $this->execute("SET search_path TO map");
		
		$this->dropForeignKey('fk_link_attr_type', '{{%link_vector_to_attr_type}}');
		$this->dropForeignKey('fk_link_vector', '{{%link_vector_to_attr_type}}');
		$this->dropForeignKey('fk_attr_measure', '{{%attribute}}');
		$this->dropForeignKey('fk_attr_attr_type', '{{%attribute}}');
		
		$this->dropTable('{{%attribute}}');
		$this->dropTable('{{%measure}}');
		$this->dropTable('{{%link_vector_to_attr_type}}');
		$this->dropTable('{{%vector}}');
		$this->dropTable('{{%attribute_type}}');
		
		$this->execute("SET search_path TO public");
    }
}
