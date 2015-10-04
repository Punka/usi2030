<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%locality}}".
 *
 * @property integer $id
 * @property integer $level
 * @property integer $kladr_code
 * @property string $name
 * @property boolean $status
 */
class Locality extends \yii\db\ActiveRecord
{
    private $district_data, $city_data, $region_data, $kladr_code, $kld_subjcode, $kld_regcode, $kld_citycode;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%locality}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['name'], 'required'],
            [['status'], 'boolean'],
            [['kladr_code'], 'string', 'max' => 11],
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
            'level' => Yii::t('app', 'Level'),
            'kladr_code' => Yii::t('app', 'Kladr Code'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
        ];
    }
}
