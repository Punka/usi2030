<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%attribute}}".
 *
 * @property integer $id
 * @property integer $attr_type_id
 * @property integer $vector_id
 * @property string $kladr_code
 * @property string $value
 * @property integer $measure_id
 * @property string $date
 * @property string $created
 * @property string $updated
 * @property boolean $status
 *
 * @property AttributeType $attrType
 * @property Measure $measure
 * @property Vector $vector
 */
class Attribute extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%map.attribute}}';
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
            [['attr_type_id', 'kladr_code', 'value', 'created', 'updated'], 'required'],
            [['attr_type_id', 'measure_id'], 'integer'],
            [['date', 'created', 'updated'], 'safe'],
            [['status'], 'boolean'],
            [['kladr_code'], 'string', 'max' => 11],
            [['value'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'attr_type_id' => Yii::t('app', 'Attr Type ID'),
            'kladr_code' => Yii::t('app', 'Kladr Code'),
            'value' => Yii::t('app', 'Value'),
            'measure_id' => Yii::t('app', 'Measure ID'),
            'date' => Yii::t('app', 'Date'),
            'created' => Yii::t('app', 'Created'),
            'updated' => Yii::t('app', 'Updated'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttrType()
    {
        return $this->hasOne(AttributeType::className(), ['id' => 'attr_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::className(), ['id' => 'measure_id']);
    }
}
