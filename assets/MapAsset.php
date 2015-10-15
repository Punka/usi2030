<?php

namespace app\assets;

use yii\web\AssetBundle;

class MapAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/russia_map.css',
    ];
    public $js = [
		'js/d3js.js',
		'js/topojson.js',
		'http://d3js.org/queue.v1.min.js',
		'js/russia_map.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
