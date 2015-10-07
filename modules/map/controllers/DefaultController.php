<?php
namespace app\modules\map\controllers;

use Yii;
use yii\web\Controller;
use app\modules\map\models\Poiskstroek;

ini_set('memory_limit', '256M');

class DefaultController extends Controller
{
    public $layout = '/map';

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionData($id = null)
    {
		$cache = Yii::$app->cache;
			
		$model = new Poiskstroek();
		
		if($id > 0)
		{
			if($data = $cache->get("poiskstroekData:" . $id))
			{
				echo json_encode($data, JSON_UNESCAPED_UNICODE);
			}
		}
		else
		{
			if($data = $cache->get("poiskstroekData"))
			{
				echo json_encode($data, JSON_UNESCAPED_UNICODE);
			}
		}
    }
    
    public function actionJsonTest()
    {
        set_time_limit(0);
        
        $json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/json/map/russia_fix_2.json"), true);
        
        $json_regions = $json['objects']['region']['geometries'];
        $json_districts = $json['objects']['district']['geometries'];
        $json_city = $json['objects']['city']['geometries'];
        
        for($i = 0; $i < count($json_districts); $i++)
        {
            if(isset($json_districts[$i]['properties']))
            {
                $value = mb_strtolower($json_districts[$i]['properties']['name'], 'utf-8');

                $kld_subjcode = $json_districts[$i]['properties']['kld_subjcode'];

                if(preg_match('/район/usi', $value))
                {
                    $value = trim(str_replace("район", "", $value));
                }
                elseif(preg_match('/улус/usi', $value))
                {
                    $value = trim(str_replace("улус", "", $value));
                }
                elseif(preg_match('/кожуун/usi', $value))
                {
                    $value = trim(str_replace("кожуун", "", $value));
                }
                
                unset($json['objects']['district']['geometries'][$i]['properties']['kladr_code']);
                unset($json['objects']['district']['geometries'][$i]['properties']['kld_regcode']);
                unset($json['objects']['district']['geometries'][$i]['properties']['kld_citycode']);

                if($sql = Yii::$app->db->createCommand("select nbs_name, b.*  from kladr.namebase as a left join kladr.kladr as b on a.nbs_pcode = b.kld_nbscode where LOWER(nbs_name) = '" . $value . "' and b.kld_subjcode = '" . $kld_subjcode . "' and b.kld_actcode = 0")->queryOne())
                {
                    $full_kladr_code = str_pad($sql['kld_subjcode'], 2, "0", STR_PAD_LEFT) . str_pad($sql['kld_regcode'], 3, "0", STR_PAD_LEFT) . str_pad($sql['kld_citycode'], 3, "0", STR_PAD_LEFT);

                    $json['objects']['district']['geometries'][$i]['properties']['kladr_code'] = $full_kladr_code;
                    $json['objects']['district']['geometries'][$i]['properties']['kld_subjcode'] = $sql['kld_subjcode'];
                    $json['objects']['district']['geometries'][$i]['properties']['kld_regcode'] = $sql['kld_regcode'];
                    $json['objects']['district']['geometries'][$i]['properties']['kld_citycode'] = $sql['kld_citycode'];
                    
                }
                else
                {
                    
                    $json['objects']['district']['geometries'][$i]['properties']['kld_subjcode'] = $kld_subjcode;
                }
				
                unset($json['objects']['district']['geometries'][$i]['properties']['alt_name']);
            }
        }
        
        for($i = 0; $i < count($json_city); $i++)
        {
            if(isset($json_districts[$i]['properties']))
            {
                $value = mb_strtolower($json_city[$i]['properties']['name'], 'utf-8');
            
                $kld_subjcode = $json_city[$i]['properties']['kld_subjcode'];
                
                unset($json['objects']['city']['geometries'][$i]['properties']['kladr_code']);
                unset($json['objects']['city']['geometries'][$i]['properties']['kld_regcode']);
                unset($json['objects']['city']['geometries'][$i]['properties']['kld_citycode']);

                if($sql = Yii::$app->db->createCommand("select nbs_name, b.*  from kladr.namebase as a left join kladr.kladr as b on a.nbs_pcode = b.kld_nbscode where LOWER(nbs_name) = '" . $value . "' and b.kld_subjcode = '" . $kld_subjcode . "' and b.kld_actcode = 0")->queryOne())
                {
                    $full_kladr_code = str_pad($sql['kld_subjcode'], 2, "0", STR_PAD_LEFT) . str_pad($sql['kld_regcode'], 3, "0", STR_PAD_LEFT) . str_pad($sql['kld_citycode'], 3, "0", STR_PAD_LEFT);

                    $json['objects']['city']['geometries'][$i]['properties']['kladr_code'] = $full_kladr_code;
                    $json['objects']['city']['geometries'][$i]['properties']['kld_subjcode'] = $sql['kld_subjcode'];
                    $json['objects']['city']['geometries'][$i]['properties']['kld_regcode'] = $sql['kld_regcode'];
                    $json['objects']['city']['geometries'][$i]['properties']['kld_citycode'] = $sql['kld_citycode'];

                }
                else
                {
                    
                    $json['objects']['city']['geometries'][$i]['properties']['kld_subjcode'] = $kld_subjcode;
                }

                //unset($value);
                unset($json['objects']['city']['geometries'][$i]['properties']['alt_name']);
                //unset($json_city[$i]);
            }
        }
        
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
    }
}
