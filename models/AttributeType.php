<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%attribute_type}}".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $vector_id
 * @property string $name
 * @property string $alias
 * @property string $created
 * @property string $updated
 * @property boolean $status
 *
 * @property Attribute[] $attributes
 * @property Vector $vector
 */
class AttributeType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%map.attribute_type}}';
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
            [['parent_id'], 'integer'],
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
            'parent_id' => Yii::t('app', 'Parent ID'),
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
        return $this->hasMany(Attribute::className(), ['attr_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVector()
    {
        return $this->hasOne(Vector::className(), ['id' => 'vector_id']);
    }
}
