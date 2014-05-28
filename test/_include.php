<?php
ini_set("display_errors",1);

include_once('../Config.php');
include_once('../Storage.php');
include_once('../Model.php');
include_once('../Password.php');

$table_config = array(
    'admin_1' => 'admin'
);

$config_data = array(
    'db_config'    => require(__DIR__ . '/config/db.php'),
    'table_config' => require(__DIR__ . '/config/table.php'),
    'model_dir'    =>  __DIR__ . '/Model',
);

$config = new \library\Config($config_data);
$storage = new library\Storage($config);
