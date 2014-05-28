<?php
class Model_User extends \library\Model
{
    protected $_table_name = 'user';
    protected $_data_types = array(
        'id'          => \PDO::PARAM_INT,
        'name'        => \PDO::PARAM_STR,
        'password'    => \PDO::PARAM_STR,
        'create_time' => \PDO::PARAM_INT,
    );
}
