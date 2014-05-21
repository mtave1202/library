<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set("display_errors",1);

include_once('../Config.php');
include_once('../Models.php');
include_once('../Model.php');

$db_config = array(
    \library\Config::DEFAULT_DB => array(
        \library\Config::DB_CONFIG_DBNAME   => 'user',
        \library\Config::DB_CONFIG_HOST     => 'localhost',
        \library\Config::DB_CONFIG_USERNAME => 'user',
        \library\Config::DB_CONFIG_PASSWORD => 'password',
        \library\Config::DB_CONFIG_CHARSET  => 'utf-8',
    ),
    'admin' => array(
        \library\Config::DB_CONFIG_DBNAME   => 'admin',
        \library\Config::DB_CONFIG_HOST     => 'localhost',
        \library\Config::DB_CONFIG_USERNAME => 'user',
        \library\Config::DB_CONFIG_PASSWORD => 'password',
        \library\Config::DB_CONFIG_CHARSET  => 'utf-8',
    ),
);

$table_config = array(
    'table_1' => 'admin'
);

$model_dir = __DIR__ . '/Model';

$config = new \library\Config($db_config, $table_config, $model_dir);
$models = new library\Models($config);
var_dump($models->Test);
?>
