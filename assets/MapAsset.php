<?php

namespace app\assets;

use yii\web\AssetBundle;

class MapAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/russia_map.css'
    ];
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/topojson/1.6.19/topojson.min.js',
        'js/russia_map.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}