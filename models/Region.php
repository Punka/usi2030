<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%region}}".
 *
 * @property integer $id
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
 * @property City[] $cities
 * @property District[] $districts
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%region}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['construct_sum', 'design_sum'], 'number'],
            [['construct_count', 'design_count', 'construct_companies', 'design_companies'], 'integer'],
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
    public function getCities()
    {
        return $this->hasMany(City::className(), ['region_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDistricts()
    {
        return $this->hasMany(District::className(), ['region_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return RegionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RegionQuery(get_called_class());
    }
}
