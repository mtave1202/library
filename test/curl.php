<?php
class Curl {
    /**
     * COOKIE保存パス
     */
    const COOKIE_PATH = '';
    /**
     * cURLセッション
     * @var Curl 
     */
    public $_curl = null;
    /**
     * cURLに設定するオプション
     */
    protected $_options = array();
    /**
     * cURLのデフォルトオプション
     * @var array
     */
    public static $_default_options = array(
        CURLOPT_SSL_VERIFYPEER => true, //SSL検証
        CURLOPT_RETURNTRANSFER => true, //返り値が文字列
        CURLOPT_FOLLOWLOCATION => true, //リダイレクトする
        CURLOPT_MAXREDIRS => 10,//何回までリダイレクトするか
        CURLOPT_AUTOREFERER => true, //リダイレクトにリファラを付ける
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_COOKIESESSION => true,
        CURLOPT_POST => false,
        CURLINFO_HEADER_OUT => true, //ヘッダ表示
        CURLOPT_VERBOSE => true,
        CURLOPT_FORBID_REUSE => false,
    );
    /**
     * cURLがセッション開始状態か
     * @var type 
     */
    protected $_runnning = false;
    
    function __construct()
    {
        $this->_curl = new Curl(self::$_default_options);
        if(static::COOKIE_PATH) {
            $this->_curl->addCookiePath(static::COOKIE_PATH);
            if(!file_exists(static::COOKIE_PATH)) {
                touch(static::COOKIE_PATH);
            }
        }
    }
    
    /**
     * cURLで情報収集開始
     * @return array
     * @throws InvalidArgumentException
     */
    public function run()
    {
        $this->_curl->init();
        $this->_curl->exec();
        if($this->_curl->getErrorNo()) {
            throw new InvalidArgumentException("[".$this->_curl->getErrorNo()."]" .$this->_curl->getError());
        }
        $this->_runnning = true;
    }
    
    /**
     * cURLのオプションを初期状態に戻す
     * @return type
     */
    public function optionReset()
    {
        if(!$this->_runnning) {
            return;
        }
        $this->_curl->resetOptions();
        $this->_curl->setOptions(self::$_default_options);
        if(static::COOKIE_PATH) {
            $this->_curl->addCookiePath(static::COOKIE_PATH);
        }
    }
    
    /**
     * cURLのセッション終了
     */
    public function close()
    {
        if(!$this->_runnning) {
            return;
        }
        //curl削除
        $this->_curl->close();
        $this->_curl->setOptions(self::$_default_options);
        if(static::COOKIE_PATH) {
            $this->_curl->addCookiePath(static::COOKIE_PATH);
        }
        $this->_runnning = false;
    }
}