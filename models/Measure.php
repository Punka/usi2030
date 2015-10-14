<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%measure}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $created
 * @property string $updated
 * @property boolean $status
 *
 * @property Attribute[] $attributes
 */
class Measure extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%map.measure}}';
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
            [['name', 'created', 'updated'], 'required'],
            [['created', 'updated'], 'safe'],
            [['status'], 'boolean'],
            [['name'], 'string', 'max' => 64]
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
        return $this->hasMany(Attribute::className(), ['measure_id' => 'id']);
    }
}
