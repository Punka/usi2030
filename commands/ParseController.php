<<<<<<< HEAD
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
use app\modules\map\models\Poiskstroek;

class ParseController extends Controller
{
    public function actionIndex()
    {
        echo "Its working!\n";
    }

    public function actionPoiskstroek()
    {
        $json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "web/json/russia_map_fix.json"), true);

        $model = new Poiskstroek();

        $model->parseRegionData($json);
    }
}

=======
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
use app\modules\map\models\Poiskstroek;

class ParseController extends Controller
{
    public function actionIndex()
    {
        echo "Its working!\n";
    }

    public function actionPoiskstroek()
    {
        $json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "web/json/russia_map_fix.json"), true);

        $model = new Poiskstroek();

        $model->parseRegionData($json);
    }
}

>>>>>>> f12cfe7958a8f3bfb199b0c5cb2ba961829f6bfb
