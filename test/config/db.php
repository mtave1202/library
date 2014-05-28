<?php
/**
 * DBへの接続情報を設定する。
 * 現状対応しているのがMySqlのみ。
 * 定数や、各値についてはlibrary\Configを参照の事。
 */
return array(
    'default' => array(
        \library\Config::DB_CONFIG_DBNAME   => 'user',
        \library\Config::DB_CONFIG_HOST     => 'localhost',
        \library\Config::DB_CONFIG_USERNAME => 'user',
        \library\Config::DB_CONFIG_PASSWORD => 'password',
    ),
    'admin' => array(
        \library\Config::DB_CONFIG_DBNAME   => 'admin',
        \library\Config::DB_CONFIG_HOST     => 'localhost',
        \library\Config::DB_CONFIG_USERNAME => 'user',
        \library\Config::DB_CONFIG_PASSWORD => 'password',
    ),
);
