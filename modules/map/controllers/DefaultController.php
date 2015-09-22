<?php

namespace app\modules\map\controllers;

use Yii;
use yii\web\Controller;
use app\modules\map\models\Poiskstroek;

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

    public function actionJson()
    {
        $json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/json/russia_map_fix.json"), true);
        $json2 = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/json/boundary.json"), true);

        $model = new Poiskstroek();
        $data = $model->dataObject($json, $json2);

        echo json_encode($data);
    }
}
