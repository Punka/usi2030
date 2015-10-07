<?php

namespace app\modules\map\models;

use Yii;
use yii\db\Expression;
use yii\i18n\Formatter;

use app\models\Locality;
use app\models\PoiskstroekData;

class Poiskstroek {

	protected $russia, $data, $json_data, $name, $kladr_code, $construct_sum, $design_sum, $construct_count, $design_count, $construct_companies, $design_companies, $format, $value, $pattern;
	
    private function getConstructSum()
    {
        $this->construct_sum = Yii::$app->poiskstroek->createCommand("SELECT COALESCE(SUM(contract_price), 0.00) as sum FROM object WHERE actual = TRUE AND checked = TRUE AND type = '1' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$this->kladr_code}%'")->queryOne();
        $this->construct_sum = Yii::$app->formatter->asDecimal($this->construct_sum['sum'], 2);
        
        return $this->construct_sum;
    }
    
    private function getDesignSum()
    {
        $this->design_sum = Yii::$app->poiskstroek->createCommand("SELECT COALESCE(SUM(contract_price), 0.00) as sum FROM object WHERE actual = TRUE AND checked = TRUE AND type = '2' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$this->kladr_code}%'")->queryOne();
        $this->design_sum = Yii::$app->formatter->asDecimal($this->design_sum['sum'], 2);
        
        return $this->design_sum;
    }
    
    private function getConstructCount()
    {
        $this->construct_count = Yii::$app->poiskstroek->createCommand("SELECT COUNT(id) FROM object WHERE actual = TRUE AND checked = TRUE AND type = '1' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$this->kladr_code}%'")->queryOne();
        $this->construct_count = $this->construct_count['count'];
        
        return $this->construct_count;
    }
    
    private function getDesignCount()
    {
        $this->design_count = Yii::$app->poiskstroek->createCommand("SELECT COUNT(id) FROM object WHERE actual = TRUE AND checked = TRUE AND type = '2' AND contract_stage = '3' AND contract_id IS NOT NULL AND kladr_code LIKE '{$this->kladr_code}%'")->queryOne();
        $this->design_count = $this->design_count['count'];
        
        return $this->design_count;
    }
    
    private function getConstructCompanies()
    {
        $this->construct_companies = Yii::$app->poiskstroek->createCommand("SELECT COUNT(DISTINCT(contract_supplier_id)) FROM object WHERE actual = '1' AND checked = '1' AND contract_stage = '3' AND contract_supplier_id IS NOT NULL AND kladr_code LIKE '{$this->kladr_code}%'")->queryOne();
        $this->construct_companies = $this->construct_companies['count'];
        
        return $this->construct_companies;
    }
    
    private function getDesignCompanies()
    {
        $this->design_companies = Yii::$app->poiskstroek->createCommand("SELECT COUNT(DISTINCT(contract_designer_id )) FROM object WHERE actual = '1' AND checked = '1' AND contract_stage = '3' AND contract_designer_id  IS NOT NULL AND kladr_code LIKE '{$this->kladr_code}%'")->queryOne();
        $this->design_companies = $this->design_companies['count'];
        
        return $this->design_companies;
    }
	
	private function getImg($path, $kladr_code)
	{
		if(file_exists("web" . $path))
		{
			return $path;
		}
		else
		{
			return "/images/map/" . substr($kladr_code, 0, 2) . ".png";
		}
	}
	
	public function cachePoiskstroekData()
	{
		$cache = Yii::$app->cache;
		
		$rows = PoiskstroekData::find()->asArray()->all();
		
		if(count($rows) > 0)
		{
			foreach($rows as $row)
			{
				$this->data[$row['kladr_code']]['kladr_code'] = $row['kladr_code'];
				$this->data[$row['kladr_code']]['name'] = $row['name'];
				$this->data[$row['kladr_code']]['construct_sum'] = $this->getCompactSum($row['construct_sum']);
				$this->data[$row['kladr_code']]['design_sum'] = $this->getCompactSum($row['design_sum']);
				$this->data[$row['kladr_code']]['construct_count'] = $this->getFormatObjects($row['construct_count']);
				$this->data[$row['kladr_code']]['design_count'] = $this->getFormatObjects($row['design_count']);
				$this->data[$row['kladr_code']]['construct_companies'] = $this->getFormatCompany($row['construct_companies']);
				$this->data[$row['kladr_code']]['design_companies'] = $this->getFormatCompany($row['design_companies']);
				$this->data[$row['kladr_code']]['img'] = $this->getImg($row['img'], $row['kladr_code']);
				
				if($row['name'] == 'Сургут')
					$this->data[$row['kladr_code']]['link'] = "http://surgut2030.usirf.ru";
				
				
				if($cache->set("poiskstroekData:" . $row['kladr_code'], $this->data))
				{
					echo "Информация закеширована по: " . $row['name'] . "\n";
				}
				else
				{
					echo "Ошибка кеширования по: " . $row['name'] . "\n";
				}
				
				unset($row);
			}
			
			if($cache->set("poiskstroekData", $this->data))
			{
				echo "Информация закеширована\n";
			}
			else
			{
				echo "Ошибка кеширования по: " . $row['name'] . "\n";
			}
		}
	}
	
	public function parseLocality($json)
    {
        $this->district_data = $json['objects']['district']['geometries'];
        $this->city_data = $json['objects']['city']['geometries'];
        
        $this->region_data = Yii::$app->poiskstroek->createCommand("select id, name from region where actual = true;")->queryAll();
        
        foreach($this->region_data as $region)
        {
			$this->addRegion($region);
        }
        unset($this->region_data);
        
        foreach($this->district_data as $district)
        {
            if(isset($district['properties']))
            {
                $this->addLocality($district['properties']);
            }
        }
        unset($this->district_data);
        
        foreach($this->city_data as $city)
        {
            if(isset($city['properties']))
            {
                $this->addLocality($city['properties']);
            }
        }
        unset($this->city_data);
    }
    
    public function parseData()
    {
        $this->addRussiaData();
        
        $this->data = Locality::find()->where("kladr_code is not null")->asArray()->all();
       
        if(count($this->data) > 0)
        {
            foreach($this->data as $value)
            {
                $this->addLocalityData($value);
            }
        }
        
        unset($this->data);
    }
    
    private function getData($kladr_code)
    {
        $array = array();
          
        if($model = PoiskstroekData::find()->where(['kladr_code' => $kladr_code])->one())
        {
            $array['name'] = $model->name;
            $array['construct_sum'] = $this->getCompactSum($model->construct_sum);
            $array['design_sum'] = $this->getCompactSum($model->design_sum);
            $array['construct_count'] = $this->getFormatObjects($model->construct_count);
            $array['design_count'] = $this->getFormatObjects($model->design_count);
            $array['construct_companies'] = $this->getFormatCompany($model->construct_companies);
            $array['design_companies'] = $this->getFormatCompany($model->design_companies);
            $array['img'] = $this->getImg($model->img);
			
			if($kladr_code != 100) $array['kladr_code'] = $kladr_code;
            
            if($model->name == 'Сургут')
                        $array['link'] = "http://surgut2030.usirf.ru";
        }
                
        unset($model, $kladr_code);
            
        return $array;
    }
    
    
    private function getLocalityData($json_data)
    {
        $this->json_data = $json_data;
        
        $array = array();
        
        for($i = 0; $i <= count($this->json_data); $i++)
        {
			if(isset($this->json_data[$i]['properties']['kladr_code']))
            {
                $this->kladr_code = $this->json_data[$i]['properties']['kladr_code'];
				
				if($value = $this->getData($this->kladr_code)) $array[$this->kladr_code] = $value;
				
				unset($this->kladr_code, $value);
            }
        }
        
        unset($this->json_data);
        
        return $array;
    }
    
    public function getPoiskstroekData($json)
    {
        $this->data = array();
		
        $this->data['russia'] = $this->getData(100);
		
        $this->data += $this->getLocalityData($json['objects']['region']['geometries']);
        $this->data += $this->getLocalityData($json['objects']['district']['geometries']);
        $this->data += $this->getLocalityData($json['objects']['city']['geometries']);
		
        unset($json);
        
        return $this->data;
    }
	
	public function getPoiskstroekDataById($id)
	{
		$this->data = array();
		
		$this->data = $this->getData($id);
		
		return $this->data;
	}
	
	private function addRegion($data)
    {
        if(isset($data['id']))
        {
            $this->kladr_code = str_pad($data['id'], 2, "0", STR_PAD_LEFT);
            
            if($model = Locality::find()->where(['kladr_code' => $this->kladr_code])->one())
			{
				$model->kld_subjcode = $data['id'];
                $model->kladr_code = $this->kladr_code;
                $model->name = $data['name'];
                $model->status = true;
				
				if($model->save())
                {
                    echo "Обновлен регион: " . $data['name'] . "\n";
                }
                else
                {
                    echo "Error! Обновлен регион: " . $data['name'] . "\n";
                }
			}
			else
            {
                $model = new Locality();

                $model->level = 1;
                $model->kld_subjcode = $data['id'];
                $model->kladr_code = $this->kladr_code;
                $model->name = $data['name'];
                $model->status = true;

                if($model->save())
                {
                    echo "Добавлен регион: " . $data['name'] . "\n";
                }
                else
                {
                    echo "Error! Добавлен регион: " . $data['name'] . "\n";
                } 
            }
        }
        
        unset($this->kladr_code, $data, $model);
    }
    
    private function addLocality($data)
    {
        if(!isset($data['name'])) return false;
            
        if(isset($data['kladr_code']))
        {
            if($model = Locality::find()->where(['kladr_code' => $data['kladr_code']])->one())
            {
				$this->kld_subjcode = $data['kld_subjcode'];
                $this->kld_regcode = $data['kld_regcode'];
                $this->kld_citycode = $data['kld_citycode'];
				
				$model->level = 2;
                $model->kld_subjcode = $this->kld_subjcode;
                $model->kld_regcode = $this->kld_regcode;
                $model->kld_citycode = $this->kld_citycode;
                $model->kladr_code = $data['kladr_code'];
                $model->name = $data['name'];
                $model->status = true;

                if($model->save())
                {
                    echo "Обновлен район: " . $data['name'] . "\n";
                }
                else
                {
                    echo "Error! Обновлен район: " . $data['name'] . "\n";
                }
				
				unset($this->kld_subjcode, $this->kld_regcode, $this->kld_citycode, $data, $model);
			}
			else
			{
                $this->kld_subjcode = $data['kld_subjcode'];
                $this->kld_regcode = $data['kld_regcode'];
                $this->kld_citycode = $data['kld_citycode'];
                    
                $model = new Locality();
               
                $model->level = 2;
                $model->kld_subjcode = $this->kld_subjcode;
                $model->kld_regcode = $this->kld_regcode;
                $model->kld_citycode = $this->kld_citycode;
                $model->kladr_code = $data['kladr_code'];
                $model->name = $data['name'];
                $model->status = true;

                if($model->save())
                {
                    echo "Добавлен район: " . $data['name'] . "\n";
                }
                else
                {
                    echo "Error! Добавлен район: " . $data['name'] . "\n";
                }
                
                unset($this->kld_subjcode, $this->kld_regcode, $this->kld_citycode, $data, $model);
            }
        }
        else
        {
            if($model = Locality::find()->where(['name' => $data['name'], 'kld_subjcode' => $data['kld_subjcode']])->one())
			{
				$model->level = 2;
                $model->kld_subjcode = $data['kld_subjcode'];
                $model->name = $data['name'];
                $model->status = false;

                if($model->save())
                {
                    echo "Обновлен район: " . $data['name'] . "\n";
                }
                else
                {
                    echo "Error! Обновлен район: " . $data['name'] . "\n";
                }
                
                unset($data, $model);
			}
			else
            {
                $model = new Locality();
               
                $model->level = 2;
                $model->kld_subjcode = $data['kld_subjcode'];
                $model->name = $data['name'];
                $model->status = false;

                if($model->save())
                {
                    echo "Добавлен район: " . $data['name'] . "\n";
                }
                else
                {
                    echo "Error! Добавлен район: " . $data['name'] . "\n";
                }
                
                unset($data, $model);
            }
        }
    }
    
    private function addData()
    {
		if($model = PoiskstroekData::find()->where(['kladr_code' => $this->kladr_code])->andWhere('updated < :date', [':date' => date('Y-m-d')])->one())
		{
			$model->kladr_code = $this->kladr_code;
			$model->name = $this->name;
			$model->construct_sum = $this->construct_sum;
			$model->design_sum = $this->design_sum;
			$model->construct_count = $this->construct_count;
			$model->design_count = $this->design_count;
			$model->construct_companies = $this->construct_companies;
			$model->design_companies = $this->design_companies;
			$model->img = "/images/map/" . $this->kladr_code . ".png";
			$model->updated = new Expression('NOW()');
			
			if($model->save())
			{
				echo "Обновлена информация по: " . $this->name . "\n";
			}
			else
			{
				echo "Error! Обновлена информация по: " . $this->name . "\n";
			}
		}
		elseif(!$model = PoiskstroekData::find()->where(['kladr_code' => $this->kladr_code])->one())
		{
			$model = new PoiskstroekData();

			$model->kladr_code = $this->kladr_code;
			$model->name = $this->name;
			$model->construct_sum = $this->construct_sum;
			$model->design_sum = $this->design_sum;
			$model->construct_count = $this->construct_count;
			$model->design_count = $this->design_count;
			$model->construct_companies = $this->construct_companies;
			$model->design_companies = $this->design_companies;
			$model->img = "/images/map/" . $this->kladr_code . ".png";
			$model->created = new Expression('NOW()');
			$model->updated = new Expression('NOW()');

			if($model->save())
			{
				echo "Добавлена информация по: " . $this->name . "\n";
			}
			else
			{
				echo "Error! Добавлена информация по: " . $this->name . "\n";
			}
		}
		
		unset($this->construct_sum, $this->design_sum, $this->construct_count, $this->design_count, $this->construct_companies, $this->design_companies, $model);
    }
    
    private function addRussiaData()
    {
        $this->russia = Yii::$app->poiskstroek->createCommand("select sum(construction_objects_count) as construct_count, sum(construction_objects_sum) as construct_sum, sum(design_objects_count) as design_count, sum(design_objects_sum) as design_sum, sum(suppliers_count) as construct_companies, sum(designers_count) as design_companies from region limit 1;")->queryOne();
        
        if($this->russia)
        {
            $this->name = "Российская Федерация";
            $this->kladr_code = "100";
			
			$this->construct_sum = $this->russia['construct_sum'];
            $this->design_sum = $this->russia['design_sum'];
            $this->construct_count = $this->russia['construct_count'];
            $this->design_count = $this->russia['design_count'];
            $this->construct_companies = $this->russia['construct_companies'];
            $this->design_companies = $this->russia['design_companies'];
			
			$this->addData();
        }
        
        unset($this->russia, $this->name, $this->kladr_code);
    }
    
    private function addLocalityData($value)
    {
        $this->kladr_code = $value['kladr_code'];
        $this->name = $value['name'];
		
		$this->construct_sum = $this->getConstructSum();
		$this->design_sum = $this->getDesignSum();
		$this->construct_count = $this->getConstructCount();
		$this->design_count = $this->getDesignCount();
		$this->construct_companies = $this->getConstructCompanies();
		$this->design_companies = $this->getDesignCompanies();
		
        $this->addData();
    }

    public function getCompactSum($value)
    {
        $this->value = $value;
		$this->format = new Formatter();
        
        if ($this->value >= 1000000000000) {
            $this->value = $this->format->asDecimal($this->value/1000000000000, 1);
            return $this->value . " " . $this->getDeclensionWords($this->value, "триллион", "триллиона", "триллионов") . " руб.";
        }
        else if ($this->value >= 1000000000) {
            $this->value = $this->format->asDecimal($this->value/1000000000, 1);
            return $this->value . " " . $this->getDeclensionWords($this->value, "миллиард", "миллиарда", "миллиардов") . " руб.";
        }
        else if ($this->value >= 1000000) {
            $this->value = $this->format->asDecimal($this->value/1000000, 1);
            return $this->value . " " . $this->getDeclensionWords($this->value, "миллион", "миллиона", "миллионов") . " руб.";
        }
        else if ($this->value >= 1000) {
            $this->value = $this->format->asDecimal($this->value/1000, 1);
            return $this->value . " " . $this->getDeclensionWords($this->value, "тысяча", "тысячи", "тысяч") . " руб.";
        }
        else {
            return Yii::t('app', '{count} руб.', ['count' => $this->value]);
        }
    }

    public function getFormatObjects($value)
    {
        return $value . " " . $this->getDeclensionWords($value, "объект", "объекта", "объектов");
    }

    public function getFormatCompany($value)
    {
        return $value . " " . $this->getDeclensionWords($value, "компания", "компании", "компаний");
    }

    public function getDeclensionWords($value, $one, $few, $many, $language = "ru-RU")
    {
		$this->pattern = "{0, plural, =0{" . $many . "} =1{" . $one . "} one{" . $one . "} few{" . $few . "} many{" . $many . "} other{" . $many . "}}";
		$format = new \MessageFormatter($language, $this->pattern);
        
        unset($this->pattern);
        
        return $format->format(['0' => $value]);
    }
}
