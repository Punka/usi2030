<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'formatter' => [
            //'dateFormat' => 'dd.MM.yyyy',
            'decimalSeparator' => '.',
            'thousandSeparator' => '',
            //'currencyCode' => 'EUR',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
<<<<<<< HEAD
        'poiskstroek' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=psdb1.cdii5kanexo4.eu-west-1.rds.amazonaws.com;port=5432;dbname=poiskstroek20150907',
            'username' => 'postgres',
            'password' => 'CepDosoufoowwib9',
            'charset' => 'utf8',
        ],
=======
        'poiskstroek' => require(__DIR__ . '/poiskstroek.php'),
>>>>>>> f12cfe7958a8f3bfb199b0c5cb2ba961829f6bfb
    ],
    'params' => $params,
];
