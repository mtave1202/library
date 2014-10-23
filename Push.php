<?php
// declare(encoding='UTF-8');
namespace library;
/**
 *
 * <code>
 * $push = new Push(array(
 *     'ios' => array(
 *         'send_retry_times' => 0,
 *         'environment' => 'production',
 *         'provider_certification_authority' => '/path/to/provider.pem',
 *         'root_certification_authority' => '/path/to/root.pem',
 *     ),
 *     'android' => array(
 *         'api_key' => 'Your API key',
 *     ),
 * ));
 *
 * $bool = $push->send(
 *     'ios',
 *     'device_token',
 *     $push->message('Hello!')
 *         ->badge(3)
 *         ->sound(null)
 * );
 *
 * echo ($bool ? 'Succsss' : 'Failure');
 * </code>
 *
 * @package push
 * @category messaging
 */
require_once(__DIR__.'/ApnsPHP/Autoload.php');
require_once(__DIR__.'/Push/Exception.php');
require_once(__DIR__.'/Push/Message.php');
class Push implements \ApnsPHP_Log_Interface
{
    /**
     * @var string
     */
    const TYPE_IOS = 'ios';

    /**
     * @var string
     */
    const TYPE_ANDROID = 'android';

    /**
     * @var array
     * @see __construct()
     */
    protected $_config;

    /**
     * @var \ApnsPHP_Push
     */
    protected $_iosPush;

    /**
     * @param array $config
     * <code>
     * array(
     *     'ios' => array(
     *        'send_retry_times' => int 失敗時、再送信を試行する回数,
     *        'environment' => string "sandbox" もしくは "production",
     *        'provider_certification_authority' => string 証明書へのファイルパス,
     *        'root_certification_authority' => string 証明書へのファイルパス,
     *     ),
     *     'android' => array(
     *         'api_key' => string API キー,
     *     ),
     * )
     * </code>
     */
    public function __construct($config)
    {
        $this->_config = $config;
        $this->_iosPush = null;
    }

    public function __destruct()
    {
        if ($this->_iosPush !== null) {
            $this->_iosPush->disconnect();
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function config($key = null, $value = null)
    {
        $numArgs = func_num_args();
        if (!$numArgs) {
            return $this->_config;
        }
        if ($numArgs === 1) {
            if (!is_array($key)) {
                $key = array($key);
            }
            $p = $this->_config;
            foreach ($key as $k) {
                if (!isset($p[$k])) {
                    return null;
                }
                $p = $p[$k];
            }
            return $p;
        }
        if (!is_array($key)) {
            $key = array($key);
        }
        $count = count($key);
        $p =& $this->_config;
        foreach ($key as $k) {
            if (!--$count) {
                $p[$k] = $value;
                break;
            }
            if (!isset($p[$k]) || !is_array($p[$k])) {
                $p[$k] = array();
            }
            $p =& $p[$k];
        }
        return $this;
    }

    /**
     * @param string $type Push::TYPE_* 定数。
     * @param string $deviceToken 送信先を示すデバイストークン。
     * @param Push_Message $message
     * @return bool 送信に成功した場合に true 。
     * @throws Push_Exception デバイストークンが無効だった場合。
     * @throws Exception その他エラーが発生した場合。
     */
    public function send($type, $deviceToken, Push_Message $message)
    {
        switch ($type) {
            case self::TYPE_IOS:
                return $this->_ios($deviceToken, $message);
            case self::TYPE_ANDROID:
                return $this->_android($deviceToken, $message);
            default:
                return false;
        }
    }

    /**
     * @param string $message
     * @return Push_Message
     */
    public function message($message)
    {
        return new Push_Message($message);
    }

    /**
     * @param string $message
     */
    public function log($message)
    {
        // do nothing
    }

    /**
     * @param string $deviceToken 送信先を示すデバイストークン。
     * @param Push_Message $m
     * @return bool 送信に成功した場合に true 。
     * @throws Push_Exception デバイストークンが無効だった場合。
     * @throws Exception その他エラーが発生した場合。
     */
    protected function _ios($deviceToken, Push_Message $m)
    {
        $push = $this->_getIosPush(isset($this->_config['ios']) && !empty($this->_config['ios']['reload_before_send']));
        $message = new \ApnsPHP_Message($deviceToken);
        $badge = $m->badge();
        if ($badge !== null) {
            $message->setBadge($badge);
        }
        $message->setText($m->text());
        $sound = $m->sound();
        if ($sound !== null) {
            $message->setSound($sound);
        } else {
            $message->setSound('default');
        }
        foreach ($m->properties() as $name => $value) {
            $message->setCustomProperty($name, $value);
        }
        $push->add($message);
        $push->send();
        $errors = $push->getErrors();
        if (empty($errors)) {
            return true;
        }
        $keys = array_keys($errors);
        $key = end($keys);
        $error = $errors[$key];
        if (isset($error['ERRORS'][0]['statusMessage']) && $error['ERRORS'][0]['statusMessage'] === 'Invalid token') {
            throw new Push_Exception(self::TYPE_IOS . ' ' . $deviceToken);
        }
        throw new \Exception(serialize($errors));
    }

    /**
     * @return \ApnsPHP_Push
     */
    protected function _getIosPush($reload = false)
    {
        if ($this->_iosPush && !$reload) {
            return $this->_iosPush;
        }

        $config = $this->_config['ios'];
        $push = new \ApnsPHP_Push(
            $config['environment'] === 'sandbox'
                ? \ApnsPHP_Abstract::ENVIRONMENT_SANDBOX
                : \ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION
            ,$config['provider_certification_authority']
        );
        $push->setLogger($this);
        //$push->setRootCertificationAuthority($config['root_certification_authority']);
        $push->setSendRetryTimes($config['send_retry_times']);
        $push->connect();
        return $this->_iosPush = $push;
    }

    /**
     * @param string $deviceToken 送信先を示すデバイストークン。
     * @param Push_Message $message
     * @return bool 送信に成功した場合に true 。
     * @throws Push_Exception デバイストークンが無効だった場合。
     * @throws Exception その他エラーが発生した場合。
     */
    protected function _android($deviceToken, Push_Message $message)
    {
        $url = 'https://android.googleapis.com/gcm/send';

        $header = array(
          'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
          'Authorization: key=' . $this->_config['android']['api_key'], //API keyはここ
        );
        $customIdentifier = $message->customIdentifier();
        $properties = $message->properties();
        if (empty($properties)) {
            $properties = array('_dummy' => '1');
        }
        $post = array(
          'registration_id' => $deviceToken,
          'data.message' => $message->text(),
          'data.payload' => json_encode($properties),
        );
        if ($customIdentifier !== null) {
            $post['collapse_key'] = $customIdentifier;
        }
        $post = http_build_query($post, '&');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $ret = @curl_exec($ch);

        /**
         * 成功時は
         * string(38) "id=0:1354284444918878%d082bbd1ce0038c9"
         * のようなレスポンス。
         * エラー時は
         * string(25) "Error=InvalidRegistration"
         * のようなレスポンス。
         */
        if (is_string($ret) && preg_match('/\Aid=.+\z/', $ret)) {
            return true;
        }

        $ret = (string)$ret;
        if ($ret === 'Error=InvalidRegistration') {
            throw new Push_Exception(self::TYPE_ANDROID . ' ' . $deviceToken);
        }
        throw new \Exception($ret);
    }
}
