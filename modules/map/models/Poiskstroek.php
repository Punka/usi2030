<?php

namespace app\modules\map\models;

use Yii;
use yii\db\Expression;
use yii\i18n\Formatter;

use app\models\Region;
use app\models\District;
use app\models\City;

class Poiskstroek {

    public $json;

    public function parseRegionData($json)
    {
        $this->json = $json;
        $json_districts = $json['objects']['district170915']['geometries'];
        $json_city = $json['objects']['city']['geometries'];

        $russia = Yii::$app->poiskstroek->createCommand("select sum(construction_objects_count) as construct_count, sum(construction_objects_sum) as construct_sum, sum(design_objects_count) as design_count, sum(design_objects_sum) as design_sum, sum(suppliers_count) as construct_companies, sum(designers_count) as design_companies from region limit 1;")->queryOne();
        $regions = Yii::$app->poiskstroek->createCommand("select id, name, construction_objects_count as construct_count, construction_objects_sum as construct_sum, design_objects_count as design_count, design_objects_sum as design_sum, suppliers_count as construct_companies, designers_count as design_companies from region where actual = true;")->queryAll();

        $russia['name'] = "Российская Федерация";
        $this->addRegion(100, $russia);

        foreach($regions as $region)
        {
            $this->addRegion($region['id'], $region);

            for($i = 0; $i < count($json_districts); $i++)
            {
                if(isset($json_districts[$i]['properties']['KLADR_CODE']) and $json_districts[$i]['properties']['REG_ID'] == $region['id'])
                {
                    $this->addDistrict($json_districts[$i]['properties']);
                }
            }

            for($i = 0; $i < count($json_city); $i++)
            {
                if(isset($json_city[$i]['properties']['KLADR_CODE']) and $json_city[$i]['properties']['REG_ID'] == $region['id'])
                {
                    $this->addCity($json_city[$i]['properties']);
                }
            }
        }
    }

    private function addRegion($id, $data)
    {
        if($r_model = Region::findOne($id))
        {
            $r_model->name = $data['name'];
            $r_model->construct_sum = $data['construct_sum'];
            $r_model->design_sum = $data['design_sum'];
            $r_model->construct_count = $data['construct_count'];
            $r_model->design_count = $data['design_count'];
            $r_model->construct_companies = $data['construct_companies'];
            $r_model->img = "/images/map/flag_" . $id . ".png";
            $r_model->design_companies = $data['design_companies'];
            $r_model->updated = new Expression('NOW()');

            if($r_model->save())
            {
                echo "Обновлен регион " . $id . "\n";
            }
            else
            {
                echo "Error! Обновлен регион " . $id . "\n";
            }
        }
        else
        {
            $r_model = new Region();

            $r_model->id = $id;
            $r_model->name = $data['name'];
            $r_model->construct_sum = $data['construct_sum'];
            $r_model->design_sum = $data['design_sum'];
            $r_model->construct_count = $data['construct_count'];
            $r_model->design_count = $data['design_count'];
            $r_model->construct_companies = $data['construct_companies'];
            $r_model->design_companies = $data['design_companies'];
            $r_model->img = "/images/map/flag_" . $id . ".png";
            $r_model->created = new Expression('NOW()');
            $r_model->updated = new Expression('NOW()');

            if($r_model->save())
            {
                echo "Добавлен регион " . $id . "\n";
            }
            else
            {
                echo "Error! Добавлен регион " . $id . "\n";
            }
        }
    }

    private function addDistrict($data)
    {
        $region_id = $data['REG_ID'];
        $kladr_code = $data['KLADR_CODE'];
        $name = $data['OKTMO_NAME'];
        $construct_sum = Yii::$app->poiskstroek->createCommand("SELECT COALESCE(SUM(contract_price), 0.00) as sum FROM object WHERE actual = TRUE AND checked = TRUE AND type = '1' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $construct_sum = Yii::$app->formatter->asDecimal($construct_sum['sum'], 2);
        $design_sum = Yii::$app->poiskstroek->createCommand("SELECT COALESCE(SUM(contract_price), 0.00) as sum FROM object WHERE actual = TRUE AND checked = TRUE AND type = '2' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $design_sum = Yii::$app->formatter->asDecimal($design_sum['sum'], 2);
        $construct_count = Yii::$app->poiskstroek->createCommand("SELECT COUNT(id) FROM object WHERE actual = TRUE AND checked = TRUE AND type = '1' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $construct_count = $construct_count['count'];
        $design_count = Yii::$app->poiskstroek->createCommand("SELECT COUNT(id) FROM object WHERE actual = TRUE AND checked = TRUE AND type = '2' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $design_count = $design_count['count'];
        $construct_companies = Yii::$app->poiskstroek->createCommand("SELECT COUNT(DISTINCT(contract_supplier_id)) FROM object WHERE actual = '1' AND checked = '1' AND contract_stage = '3' AND contract_supplier_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $construct_companies = $construct_companies['count'];
        $design_companies = Yii::$app->poiskstroek->createCommand("SELECT COUNT(DISTINCT(contract_designer_id )) FROM object WHERE actual = '1' AND checked = '1' AND contract_stage = '3' AND contract_designer_id  IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $design_companies = $design_companies['count'];

        if($d_model = District::find()->where(['kladr_code' => $kladr_code])->one())
        {
            $d_model->region_id = $region_id;
            $d_model->kladr_code = $kladr_code;
            $d_model->name = $name;
            $d_model->construct_sum = $construct_sum;
            $d_model->design_sum = $design_sum;
            $d_model->construct_count = $construct_count;
            $d_model->design_count = $design_count;
            $d_model->construct_companies = $construct_companies;
            $d_model->design_companies = $design_companies;
            $d_model->img = "/images/map/gerb_" . $kladr_code . ".png";
            $d_model->created = new Expression('NOW()');
            $d_model->updated = new Expression('NOW()');

            if($d_model->save())
            {
                echo "Обновлен район: " . $name . "\n";
            }
            else
            {
                echo "Error! Обновлен район: " . $name . "\n";
            }
        }
        else
        {
            $d_model = new District();

            $d_model->region_id = $region_id;
            $d_model->kladr_code = $kladr_code;
            $d_model->name = $name;
            $d_model->construct_sum = $construct_sum;
            $d_model->design_sum = $design_sum;
            $d_model->construct_count = $construct_count;
            $d_model->design_count = $design_count;
            $d_model->construct_companies = $construct_companies;
            $d_model->design_companies = $design_companies;
            $d_model->img = "/images/map/gerb_" . $kladr_code . ".png";
            $d_model->created = new Expression('NOW()');
            $d_model->updated = new Expression('NOW()');

            if($d_model->save())
            {
                echo "Добавлен район: " . $name . "\n";
            }
            else
            {
                echo "Error! Добавлен район: " . $name . "\n";
            }
        }
    }

    private function addCity($data)
    {
        $region_id = $data['REG_ID'];
        $kladr_code = $data['KLADR_CODE'];
        $name = $data['NAME'];
        $construct_sum = Yii::$app->poiskstroek->createCommand("SELECT COALESCE(SUM(contract_price), 0.00) as sum FROM object WHERE actual = TRUE AND checked = TRUE AND type = '1' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $construct_sum = Yii::$app->formatter->asDecimal($construct_sum['sum'], 2);
        $design_sum = Yii::$app->poiskstroek->createCommand("SELECT COALESCE(SUM(contract_price), 0.00) as sum FROM object WHERE actual = TRUE AND checked = TRUE AND type = '2' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $design_sum = Yii::$app->formatter->asDecimal($design_sum['sum'], 2);
        $construct_count = Yii::$app->poiskstroek->createCommand("SELECT COUNT(id) FROM object WHERE actual = TRUE AND checked = TRUE AND type = '1' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $construct_count = $construct_count['count'];
        $design_count = Yii::$app->poiskstroek->createCommand("SELECT COUNT(id) FROM object WHERE actual = TRUE AND checked = TRUE AND type = '2' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $design_count = $design_count['count'];
        $construct_companies = Yii::$app->poiskstroek->createCommand("SELECT COUNT(DISTINCT(contract_supplier_id)) FROM object WHERE actual = '1' AND checked = '1' AND contract_stage = '3' AND contract_supplier_id IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $construct_companies = $construct_companies['count'];
        $design_companies = Yii::$app->poiskstroek->createCommand("SELECT COUNT(DISTINCT(contract_designer_id )) FROM object WHERE actual = '1' AND checked = '1' AND contract_stage = '3' AND contract_designer_id  IS NOT NULL AND kladr_code LIKE '{$kladr_code}%'")->queryOne();
        $design_companies = $design_companies['count'];

        if($c_model = City::find()->where(['kladr_code' => $kladr_code])->one())
        {
            $c_model->region_id = $region_id;
            $c_model->kladr_code = $kladr_code;
            $c_model->name = $name;
            $c_model->construct_sum = $construct_sum;
            $c_model->design_sum = $design_sum;
            $c_model->construct_count = $construct_count;
            $c_model->design_count = $design_count;
            $c_model->construct_companies = $construct_companies;
            $c_model->design_companies = $design_companies;
            $c_model->img = "/images/map/gerb_" . $kladr_code . ".png";
            $c_model->created = new Expression('NOW()');
            $c_model->updated = new Expression('NOW()');

            if($c_model->save())
            {
                echo "Обновлен город: " . $name . "\n";
            }
            else
            {
                echo "Error! Обновлен город: " . $name . "\n";
            }
        }
        else
        {
            $c_model = new City();

            $c_model->region_id = $region_id;
            $c_model->kladr_code = $kladr_code;
            $c_model->name = $name;
            $c_model->construct_sum = $construct_sum;
            $c_model->design_sum = $design_sum;
            $c_model->construct_count = $construct_count;
            $c_model->design_count = $design_count;
            $c_model->construct_companies = $construct_companies;
            $c_model->design_companies = $design_companies;
            $c_model->img = "/images/map/gerb_" . $kladr_code . ".png";
            $c_model->created = new Expression('NOW()');
            $c_model->updated = new Expression('NOW()');

            if($c_model->save())
            {
                echo "Добавлен город: " . $name . "\n";
            }
            else
            {
                echo "Error! Добавлен город: " . $name . "\n";
            }
        }
    }

    public function dataObject($json, $json2)
    {
        $json_regions = $json['objects']['region']['geometries'];
        $json_districts = $json['objects']['district170915']['geometries'];
        $json_city = $json['objects']['city']['geometries'];

        $regions = Region::find()->all();
        $districts = District::find()->all();
        $cities = City::find()->all();

        $reg = [];
        $dist = [];
        $town = [];

        foreach($regions as $region)
        {
            $reg[$region['id']]['name'] = $region['name'];
            $reg[$region['id']]['construct_sum'] = $this->getCompactSum($region['construct_sum']);
            $reg[$region['id']]['design_sum'] = $this->getCompactSum($region['design_sum']);
            $reg[$region['id']]['construct_count'] = $this->getFormatObjects($region['construct_count']);
            $reg[$region['id']]['design_count'] = $this->getFormatObjects($region['design_count']);
            $reg[$region['id']]['construct_companies'] = $this->getFormatCompany($region['construct_companies']);
            $reg[$region['id']]['design_companies'] = $this->getFormatCompany($region['design_companies']);
            $reg[$region['id']]['img'] = $region['img'];
        }

        foreach($districts as $district)
        {
            $dist[$district['kladr_code']]['name'] = $district['name'];
            $dist[$district['kladr_code']]['construct_sum'] = $this->getCompactSum($district['construct_sum']);
            $dist[$district['kladr_code']]['design_sum'] = $this->getCompactSum($district['design_sum']);
            $dist[$district['kladr_code']]['construct_count'] = $this->getFormatObjects($district['construct_count']);
            $dist[$district['kladr_code']]['design_count'] = $this->getFormatObjects($district['design_count']);
            $dist[$district['kladr_code']]['construct_companies'] = $this->getFormatCompany($district['construct_companies']);
            $dist[$district['kladr_code']]['design_companies'] = $this->getFormatCompany($district['design_companies']);
            $dist[$district['kladr_code']]['img'] = $district['img'];
        }

        foreach($cities as $city)
        {
            $town[$city['kladr_code']]['name'] = $city['name'];
            $town[$city['kladr_code']]['construct_sum'] = $this->getCompactSum($city['construct_sum']);
            $town[$city['kladr_code']]['design_sum'] = $this->getCompactSum($city['design_sum']);
            $town[$city['kladr_code']]['construct_count'] = $this->getFormatObjects($city['construct_count']);
            $town[$city['kladr_code']]['design_count'] = $this->getFormatObjects($city['design_count']);
            $town[$city['kladr_code']]['construct_companies'] = $this->getFormatCompany($city['construct_companies']);
            $town[$city['kladr_code']]['design_companies'] = $this->getFormatCompany($city['design_companies']);
            $town[$city['kladr_code']]['img'] = $city['img'];
        }

        $json['objects']['russia']['properties']['name'] = $reg[100]['name'];
        $json['objects']['russia']['properties']['construct_sum'] = $reg[100]['construct_sum'];
        $json['objects']['russia']['properties']['design_sum'] = $reg[100]['design_sum'];
        $json['objects']['russia']['properties']['construct_count'] = $reg[100]['construct_count'];
        $json['objects']['russia']['properties']['design_count'] = $reg[100]['design_count'];
        $json['objects']['russia']['properties']['construct_companies'] = $reg[100]['construct_companies'];
        $json['objects']['russia']['properties']['design_companies'] = $reg[100]['design_companies'];
        $json['objects']['russia']['properties']['img'] = "/images/map/flag_100.png";

        for($i = 0; $i < count($json_regions); $i++)
        {
            if(isset($json_regions[$i]['properties']['REG_ID']))
            {
                $region_id = $json_regions[$i]['properties']['REG_ID'];

                $json['objects']['region']['geometries'][$i]['properties']['kladr_code'] = $region_id;
                $json['objects']['region']['geometries'][$i]['properties']['name'] = $reg[$region_id]['name'];
                $json['objects']['region']['geometries'][$i]['properties']['construct_sum'] = $reg[$region_id]['construct_sum'];
                $json['objects']['region']['geometries'][$i]['properties']['design_sum'] = $reg[$region_id]['design_sum'];
                $json['objects']['region']['geometries'][$i]['properties']['construct_count'] = $reg[$region_id]['construct_count'];
                $json['objects']['region']['geometries'][$i]['properties']['design_count'] = $reg[$region_id]['design_count'];
                $json['objects']['region']['geometries'][$i]['properties']['construct_companies'] = $reg[$region_id]['construct_companies'];
                $json['objects']['region']['geometries'][$i]['properties']['design_companies'] = $reg[$region_id]['design_companies'];
                $json['objects']['region']['geometries'][$i]['properties']['img'] = $reg[$region_id]['img'];
            }
        }

        for($i = 0; $i < count($json_districts); $i++)
        {
            if(isset($json_districts[$i]['properties']['REG_ID']) and isset($json_districts[$i]['properties']['KLADR_CODE']))
            {
                $kladr_code = $json_districts[$i]['properties']['KLADR_CODE'];

                $json['objects']['district170915']['geometries'][$i]['properties']['kladr_code'] = $kladr_code;
                $json['objects']['district170915']['geometries'][$i]['properties']['name'] = $dist[$kladr_code]['name'];
                $json['objects']['district170915']['geometries'][$i]['properties']['construct_sum'] = $dist[$kladr_code]['construct_sum'];
                $json['objects']['district170915']['geometries'][$i]['properties']['design_sum'] = $dist[$kladr_code]['design_sum'];
                $json['objects']['district170915']['geometries'][$i]['properties']['construct_count'] = $dist[$kladr_code]['construct_count'];
                $json['objects']['district170915']['geometries'][$i]['properties']['design_count'] = $dist[$kladr_code]['design_count'];
                $json['objects']['district170915']['geometries'][$i]['properties']['construct_companies'] = $dist[$kladr_code]['construct_companies'];
                $json['objects']['district170915']['geometries'][$i]['properties']['design_companies'] = $dist[$kladr_code]['design_companies'];
                $json['objects']['district170915']['geometries'][$i]['properties']['img'] = $dist[$kladr_code]['img'];
            }
        }

        for($i = 0; $i < count($json_city); $i++)
        {
            if(isset($json_city[$i]['properties']['REG_ID']) and isset($json_city[$i]['properties']['KLADR_CODE']))
            {
                $kladr_code = $json_city[$i]['properties']['KLADR_CODE'];

                $json['objects']['city']['geometries'][$i]['properties']['kladr_code'] = $kladr_code;
                $json['objects']['city']['geometries'][$i]['properties']['name'] = $town[$kladr_code]['name'];
                $json['objects']['city']['geometries'][$i]['properties']['construct_sum'] = $town[$kladr_code]['construct_sum'];
                $json['objects']['city']['geometries'][$i]['properties']['design_sum'] = $town[$kladr_code]['design_sum'];
                $json['objects']['city']['geometries'][$i]['properties']['construct_count'] = $town[$kladr_code]['construct_count'];
                $json['objects']['city']['geometries'][$i]['properties']['design_count'] = $town[$kladr_code]['design_count'];
                $json['objects']['city']['geometries'][$i]['properties']['construct_companies'] = $town[$kladr_code]['construct_companies'];
                $json['objects']['city']['geometries'][$i]['properties']['design_companies'] = $town[$kladr_code]['design_companies'];
                $json['objects']['city']['geometries'][$i]['properties']['img'] = $town[$kladr_code]['img'];

                if($town[$kladr_code]['name'] == 'Сургут')
                    $json['objects']['city']['geometries'][$i]['properties']['link'] = "http://surgut2030.usirf.ru";
            }
        }

        $json[] = $json2;

        return $json;
    }

    public function getCompactSum($value)
    {
        $format = new Formatter();

        if (empty($value)) {
            return Yii::t('app', '{count} руб.', ['count' => $value]);
        }
        if ($value > 1000000000000) {
            $number = $format->asDecimal($value/1000000000000, 1);
            return $number . " " . $this->getDeclensionWords($number, "триллион", "триллиона", "триллионов") . " руб.";

        }
        if ($value > 1000000000) {
            $number = $format->asDecimal($value/1000000000, 1);
            return $number . " " . $this->getDeclensionWords($number, "миллиард", "миллиарда", "миллиардов") . " руб.";

        }
        if ($value > 1000000) {
            $number = $format->asDecimal($value/1000000, 1);
            return $number . " " . $this->getDeclensionWords($number, "миллион", "миллиона", "миллионов") . " руб.";

        }

        return Yii::t('app', '{count} руб.', ['count' => $value]);
    }

    public function getFormatObjects($value)
    {
        return $value . $this->getDeclensionWords($value, " объект", " объекта", " объектов");
    }

    public function getFormatCompany($value)
    {
        return $value . $this->getDeclensionWords($value, " компания", " компании", " компаний");
    }

    public function getDeclensionWords($value, $one, $few, $many, $language = "ru-RU")
    {
        $language = $language;
        $pattern = '{0, plural, =0{' . $many . '} =1{' . $one . '} one{' . $one . '} few{' . $few . '} many{' . $many . '} other{' . $many . '}}';
        $params = ['0' => $value];
        $formatter = new \MessageFormatter($language, $pattern);

        return $formatter->format($params);
    }
}
