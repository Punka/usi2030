<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%city}}".
 *
 * @property integer $id
 * @property integer $region_id
 * @property integer $kladr_code
 * @property string $name
 * @property string $construct_sum
 * @property string $design_sum
 * @property integer $construct_count
 * @property integer $design_count
 * @property integer $construct_companies
 * @property integer $design_companies
 * @property string $img
 * @property string $created
 * @property string $updated
 *
 * @property Region $region
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['region_id', 'kladr_code', 'construct_count', 'design_count', 'construct_companies', 'design_companies'], 'integer'],
            [['name'], 'required'],
            [['construct_sum', 'design_sum'], 'number'],
            [['created', 'updated'], 'safe'],
            [['name', 'img'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'region_id' => Yii::t('app', 'Region ID'),
            'kladr_code' => Yii::t('app', 'Kladr Code'),
            'name' => Yii::t('app', 'Name'),
            'construct_sum' => Yii::t('app', 'Construct Sum'),
            'design_sum' => Yii::t('app', 'Design Sum'),
            'construct_count' => Yii::t('app', 'Construct Count'),
            'design_count' => Yii::t('app', 'Design Count'),
            'construct_companies' => Yii::t('app', 'Construct Companies'),
            'design_companies' => Yii::t('app', 'Design Companies'),
            'img' => Yii::t('app', 'Img'),
            'created' => Yii::t('app', 'Created'),
            'updated' => Yii::t('app', 'Updated'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * @inheritdoc
     * @return CityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CityQuery(get_called_class());
    }
}
