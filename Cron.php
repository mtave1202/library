<?php
/**
 * Cronの元になるClass
 * 
 * @author Daisuke Abeyama
 */
namespace library;
abstract class Cron 
{
    /**
     * @var Config 
     */
    protected $_config;
    /**
     *
     * @var Storage
     */
    protected $_storage;
    
    function __construct(Config $config) 
    {
        $this->_config = $config;
        $this->_storage = new \library\Storage($config);
    }
    /**
     * 実行
     */
    abstract public function run();
}
