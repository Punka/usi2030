<?php
/**
 * Created by PhpStorm.
 * User: Rahad
 * Date: 19.09.2015
 * Time: 14:18
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Locality;
use app\modules\map\models\Poiskstroek;

class ParseController extends Controller
{
    private $json;
    
    public function actionIndex()
    {
        echo "Its working!\n";
    }
    
    public function actionLocality()
    {
        $this->json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "web/json/map/russia_final.json"), true);
        
        $model = new Poiskstroek();
        
        $model->parseLocality($this->json);
        
        unset($this->json);
    }

    public function actionPoiskstroek()
    {
        $model = new Poiskstroek();
        
        $model->parseData();
        
        unset($model);
    }
	
	public function actionCachePoiskstroekData()
	{
		$model = new Poiskstroek();
		
		$model->cachePoiskstroekData();
	}
}