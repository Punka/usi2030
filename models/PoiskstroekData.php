<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%poiskstroek_data}}".
 *
 * @property integer $id
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
 */
class PoiskstroekData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%map.poiskstroek_data}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['construct_count', 'design_count', 'construct_companies', 'design_companies'], 'integer'],
            [['name', 'created', 'updated'], 'required'],
            [['construct_sum', 'design_sum'], 'number'],
            [['created', 'updated'], 'safe'],
			[['kladr_code'], 'string', 'max' => 11],
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
}
