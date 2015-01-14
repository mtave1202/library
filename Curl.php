<?php
/**
 * cURL用拡張クラス
 * @author admin-97
 */
class Curl {
    protected $_options = array();
    protected $_ch = null;
    protected $_exec = null;
    protected $_error_no = null;
    protected $_error = null;
    protected $_info = null;
    
    function __construct($options = array())
    {
        $this->_options = $options;
    }
    
    public function init($options = array())
    {
        if(!$this->_ch) {
            $this->_ch = curl_init();
        }
        curl_setopt_array($this->_ch, $this->_options + $options);
    }
    
    /**
     * cURLセッションを実行し、各パラメータに値を代入する
     * @throws BadMethodCallException
     */
    public function exec()
    {
        if(!$this->_ch) {
            throw new BadMethodCallException();
        }
        $this->_exec = curl_exec($this->_ch);
        $this->_error_no = curl_errno($this->_ch);
        $this->_error = curl_error($this->_ch);
        $this->_info = curl_getinfo($this->_ch);
    }
    
    /**
     * cURL接続をクローズする
     * @throws BadMethodCallException
     */
    public function close()
    {
        if(!$this->_ch) {
            throw new BadMethodCallException();
        }
        curl_close($this->_ch);
        $this->_ch = null;
        $this->_options = array();
    }
    
    /**
     * cURL転送用オプションを設定する
     * @param int $option
     * @param mixed $value
     */
    public function addOption($option,$value)
    {
        $this->_options[$option] = $value;
    }
    /**
     * POST送信パラメータを設定する
     * @param type $params
     */
    public function addPostParams($params)
    {
        $this->_options[CURLOPT_POST] = true;
        $this->_options[CURLOPT_POSTFIELDS] = $params;
    }
    /**
     * COOKIEの保存&使用パスを設定する。
     */
    public function addCookiePath($path)
    {
        $this->_options[CURLOPT_COOKIEFILE] = $path;
        $this->_options[CURLOPT_COOKIEJAR] = $path;
    }
    
    public function resetOptions()
    {
        curl_reset($this->_ch);
        $this->_options = array();
    }
    
    /**
     * 閲覧しにいくURLを設定する
     */
    public function addUrl($url)
    {
        $this->_options[CURLOPT_URL] = $url;
    }
    
    /**
     * GETメソッドで送信する
     */
    public function methodGet()
    {
        $this->_options[CURLOPT_POST] = false;
    }
    
    /**
     * POSTメソッドで送信する
     */
    public function methodPost()
    {
        $this->_options[CURLOPT_POST] = true;
    }
    
    //Setter,Getter
    public function getOptions()
    {
        return $this->_options;
    }
    public function setOptions($options)
    {
        $this->_options = $options;
    }
    
    public function getExec($decode = true)
    {
        return htmlspecialchars_decode($this->_exec);
    }
    public function getError()
    {
        return $this->_error;
    }
    public function getErrorNo()
    {
        return $this->_error_no;
    }
    public function getInfo($key = null)
    {
        if(is_null($key)) {
            return $this->_info;
        } else if(isset($this->_info[$key])) {
            return $this->_info[$key];
        }
        return null;
    }
}

