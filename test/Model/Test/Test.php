<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author Administrator
 */
class Model_Test_Test extends \library\Model {
    //put your code here
    protected $_table_name = 'test_test';
    protected $_primary_key = array('test_id','test2_id');
    protected $_data_types = array(
        'test_id'  => \PDO::PARAM_INT,
        'test2_id' => \PDO::PARAM_INT,
        'name'     => \PDO::PARAM_STR,
    );
}
