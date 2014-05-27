<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Test extends Model {
    protected $_table_name = 'test';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'name' => \PDO::PARAM_STR,
    );
}

