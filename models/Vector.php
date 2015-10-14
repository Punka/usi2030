<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%vector}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 * @property string $created
 * @property string $updated
 * @property boolean $status
 *
 * @property Attribute[] $attributes
 * @property AttributeType[] $attributeTypes
 */
class Vector extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%map.vector}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('map');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'alias', 'created', 'updated'], 'required'],
            [['created', 'updated'], 'safe'],
            [['status'], 'boolean'],
            [['name', 'alias'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'alias' => Yii::t('app', 'Alias'),
            'created' => Yii::t('app', 'Created'),
            'updated' => Yii::t('app', 'Updated'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributes()
    {
        return $this->hasMany(Attribute::className(), ['vector_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributeTypes()
    {
        return $this->hasMany(AttributeType::className(), ['vector_id' => 'id']);
    }
}
