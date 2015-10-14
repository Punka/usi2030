<?php

namespace app\modules\map\models;

use Yii;
use yii\log\Logger;
use yii\db\Expression;
use yii\base\ErrorException;
use \moonland\phpexcel\Excel;
use app\models\Attribute;
use app\models\AttributeType;
use app\models\Vector;
use app\models\Measure;
use app\models\LinkVectorToAttrType;

class ImportExcel
{
	private $_excel, $_sheet, $_column, $_parent, $_date, $_measure, $_vector, $_vector_list, $_measure_list, $_attr_type_list = array();
	
	public function __construct($file, $config = null)
	{
		
		$this->_excel = Excel::import($file, $config);
	}
	
	public function getData($list)
	{
		print_r($this->_excel[$list]);
	}
	
	public function importExcelData($list)
	{
		$data = $this->_excel[$list];
		
		$arr = array();
		
		$data = array_values($data);
		
		/* Ловим все атрибуты */
		for($a = 0; $a < count($data); $a++)
		{
			$data_2 = array_values($data[$a]);
			
			if(!$data_2[0])
			{
				for($b = 2; $b < count($data_2); $b++)
				{
					$this->setAttributeType($data_2[$b], $arr, $a, $b);
				}
			}
			elseif($data_2[0] == "дата")
			{
				for($b = 2; $b < count($data_2); $b++)
				{
					if($data_2[$b]) $arr[$a][$b] = $data_2[$b];
					elseif($b>2) $arr[$a][$b] = $arr[$a][$b-1];
					else $arr[$a][$b] = "";
					
					$this->_date[$b] = $arr[$a][$b];
				}
			}
			elseif($data_2[0] == "мера")
			{
				for($b = 2; $b < count($data_2); $b++)
				{
					$this->setMeasure($data_2[$b], $arr, $a, $b);
				}
			}
			elseif($data_2[0] == "вектор")
			{
				for($b = 2; $b < count($data_2); $b++)
				{
					$this->setVector($data_2[$b], $arr, $a, $b);
				}
			}
			elseif($data_2[1])
			{
				for($b = 2; $b < count($data_2); $b++)
				{
					$this->addAttribute($data_2, $b);
				}
			}
			else
			{
				echo "Ошибка! Поле: " . $a . "\n";
				Yii::error("Ошибка! Поле: " . $a . "\n");
			}
		}
	}
	
	private function setAttributeType($name, &$arr, $a, $b)
	{
		if(!$name and isset($arr[$a-1][$b-1]) and isset($arr[$a-1][$b]) and $arr[$a-1][$b-1] != $arr[$a-1][$b])
		{
			$arr[$a][$b] = "";
		}
		elseif($name)
		{
			$parent_id = (isset($arr[$a-1][$b])) ? $arr[$a-1][$b] : 0;
			$arr[$a][$b] = $this->addAttributeType($name, $parent_id);
		}
		elseif($b>2 and isset($arr[$a][$b-1]))
		{
			$arr[$a][$b] = $arr[$a][$b-1];
		}
		else
		{
			$arr[$a][$b] = "";
		}
	
		$this->_column[$b] = ($arr[$a][$b]) ? $arr[$a][$b] : $arr[$a-1][$b];
	}
	
	private function addAttributeType($name, $parent_id)
	{
		$name = mb_strtolower($name, "UTF-8");
		
		if($model = AttributeType::find()->where(['name' => $name, 'parent_id' => $parent_id])->one())
		{
			echo "Тип атрибута: " . $name . ", уже добавлен\n";
			Yii::warning("Тип атрибута: " . $name . ", уже добавлен\n");
			
			return $model->id;
		}
		else
		{
			$model = new AttributeType();
			$model->parent_id = $parent_id;
			$model->name = $name;
			$model->alias = $this->transliteration($name);
			$model->created = new Expression('NOW()');
			$model->updated = new Expression('NOW()');
			
			if($model->save())
			{
				echo "Добавлен новый тип атрибута: " . $name . "\n";
				Yii::info("Добавлен новый тип атрибута: " . $name . "\n");
				
				return $model->id;
			}
			else
			{
				echo "Ошибка! Тип атрибута: " . $name . "\n";
				Yii::error("Ошибка! Тип атрибута: " . $name . "\n");
				die();
			}
		}
		
		return $name;
	}
	
	private function setMeasure($name, &$arr, $a, $b)
	{
		if($name)
		{
			$parent_id = (isset($arr[$a-1][$b])) ? $arr[$a-1][$b] : 0;
			$arr[$a][$b] = $this->addMeasure($name);
		}
		elseif($b>2 and isset($arr[$a][$b-1]))
		{
			$arr[$a][$b] = $arr[$a][$b-1];
		}
		else
		{
			$arr[$a][$b] = "";
		}
	
		$this->_measure[$b] = $arr[$a][$b];
	}
	
	private function addMeasure($name)
	{
		$name = mb_strtolower($name, "UTF-8");
		
		if($model = Measure::find()->where(['name' => $name])->one())
		{
			echo "Мера: " . $name . ", уже добавлена\n";
			Yii::warning("Мера: " . $name . ", уже добавлена\n");
			
			return $model->id;
		}
		else
		{
			$model = new Measure();
			$model->name = $name;
			$model->created = new Expression('NOW()');
			$model->updated = new Expression('NOW()');
			
			if($model->save())
			{
				echo "Добалена новый тип мары: " . $name . "\n";
				Yii::info("Добалена новый тип мары: " . $name . "\n");
				
				return $model->id;
			}
			else
			{
				echo "Ошибка! Мера: " . $name . "\n";
				Yii::error("Ошибка! Мера: " . $name . "\n");
				die();
			}
		}
	}
	
	private function setVector($name, &$arr, $a, $b)
	{
		if($name)
		{
			$parent_id = (isset($arr[$a-1][$b])) ? $arr[$a-1][$b] : 0;
			$arr[$a][$b] = $this->addVector($name, $this->_column[$b]);
		}
		elseif($b>2 and isset($arr[$a][$b-1]))
		{
			$arr[$a][$b] = $arr[$a][$b-1];
		}
		else
		{
			$arr[$a][$b] = "";
		}
	
		$this->_vector[$b] = $arr[$a][$b];
	}
	
	private function addVector($name, $attr_type_id)
	{
		$name = mb_strtolower($name, "UTF-8");
		
		if($model = Vector::find()->where(['name' => $name])->one())
		{
			$this->addLinkVectorToAttributeType($model->id, $attr_type_id);
			
			echo "Вектор: " . $name . ", уже добавлен\n";
			Yii::warning("Вектор: " . $name . ", уже добавлен\n");
			
			return $model->id;
		}
		else
		{
			$model = new Vector();
			$model->name = $name;
			$model->alias = $this->transliteration($name);
			$model->created = new Expression('NOW()');
			$model->updated = new Expression('NOW()');
			
			if($model->save())
			{
				$this->addLinkVectorToAttributeType($model->id, $attr_type_id);
				
				echo "Добавлен вектор: " . $name . "\n";
				Yii::info("Добавлен вектор: " . $name . "\n");
				
				return $model->id;
			}
			else
			{
				echo "Ошибка! Вектор: " . $name . "\n";
				Yii::error("Ошибка! Вектор: " . $name . "\n");
				die();
			}
		}
	}
	
	private function addLinkVectorToAttributeType($vector_id, $attr_type_id)
	{
		if(!LinkVectorToAttrType::find(['vector_id' => $vector_id, 'attr_type_id' => $attr_type_id])->exists())
		{
			$model = new LinkVectorToAttrType();
			$model->vector_id = $vector_id;
			$model->attr_type_id = $attr_type_id;
			
			if($model->save())
			{
				echo "Добавлен новый тип связи: " . $vector_id . " c " . $attr_type_id . "\n";
				Yii::info("Добавлен новый тип связи: " . $vector_id . " c " . $attr_type_id . "\n");
			}
			else
			{
				echo "Ошибка! Связь: " . $vector_id . " c " . $attr_type_id . ", не добавлена\n";
				Yii::error("Ошибка! Связь: " . $vector_id . " c " . $attr_type_id . ", не добавлена\n");
				die();
			}
		}
	}
	
	private function addAttribute($data, $b)
	{
		if($data[1] > 0 and $this->_column[$b] > 0)
		{
			$attr_type_id = $this->_column[$b];
			$kladr_code = ($data[1] == 100) ? 100 : str_pad($data[1], 2, "0", STR_PAD_LEFT);
			$value = ($data[$b]) ? strval($this->correct_value($data[$b])) : "0";
			$measure_id = ($this->_measure[$b]) ? $this->_measure[$b] : "";
			$date = ($this->_date[$b]) ? $this->_date[$b] : "";
			$progress = "";
			
			if(!Attribute::find()->where(['attr_type_id' => $attr_type_id, 'kladr_code' => $kladr_code, 'date' => $this->correct_date($date)])->exists())
			{
				if($sql = Attribute::find()->where(['attr_type_id' => $attr_type_id, 'kladr_code' => $kladr_code])->orderBy('date DESC')->one())
				{
					if(strtotime($sql->date) > strtotime($this->correct_date($date)))
					{
						if($value > $sql->value) $progress = "d";
						elseif($value < $sql->value) $progress = "u";
						else $progress = "n";
						
						$sql->progress = $progress;
						if($sql->save())
						{
							$progress = "";
						}
					}
					else
					{
						if($value > $sql->value) $progress = "u";
						elseif($value < $sql->value) $progress = "d";
						else $progress = "n";
					}
				}
				
				$model = new Attribute();
				$model->attr_type_id = $attr_type_id;
				$model->kladr_code = strval($kladr_code);
				$model->value = $value;
				$model->measure_id = $measure_id;
				$model->date = $this->correct_date($date);
				$model->created = new Expression('NOW()');
				$model->updated = new Expression('NOW()');
				$model->progress = $progress;
				
				if($model->save())
				{
					echo "Добавлен новый атрибут: " . $attr_type_id . "\n";
					Yii::info("Добавлен новый атрибут: " . $attr_type_id . "\n");
				}
				else
				{
					echo "Ошибка! Атрибут: " . $attr_type_id. ", не добавлен\n";
					Yii::error("Ошибка! Атрибут: " . $attr_type_id. ", не добавлен\n");
					die();
				}
			}
		}
		
		
	}
	
	function transliteration($text){
		$trans_arr = array ( 
			"а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
			"е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i",
			"й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n",
			"о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t",
			"у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch",
			"ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u",
			"я"=>"ya",
			"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
			"Е"=>"E","Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I",
			"Й"=>"I","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
			"О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
			"У"=>"Y","Ф"=>"F","Х"=>"H","Ц"=>"C","Ч"=>"Ch",
			"Ш"=>"Sh","Щ"=>"Sh","Ы"=>"I","Э"=>"E","Ю"=>"U",
			"Я"=>"Ya",
			"ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>""," "=>"_","_"=>"_",
			"("=>"",")"=>"","'"=>"",'"'=>"","."=>"",
		);
		
		return strtr($text, $trans_arr);
	}
	
	function correct_date($date)
	{
		if($arr = explode('-', $date))
		{
			if(count($arr) > 1)
			{
				if(strlen($arr[2]) == 2) $arr[2] = "20" . $arr[2];
				$arr = implode('-', $arr);
				return date('Y-m-d', strtotime($arr));
			}
			else
			{
				return $arr[0] . "-01-01";
			}
		}
	}
	
	function correct_value($val)
	{
		$val = str_replace(' ', '', $val);
		$val = str_replace(',', '.', $val);
		$val = preg_replace("/[^0-9\.\-\+]/i", "$1", $val);
		$val = number_format($val, 2, ".", " ");
		
		return $val;
	}
}