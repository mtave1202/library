<?php
/**
 * 様々なpath等を登録し、必要な形にして返す為のクラス。
 * 言ってしまえばHelper
 * @author Administrator
 */
namespace library;
class Assets {
    /**
     * $_assets = array(
     *     {key} => array(
     *          'path' => '/hogehoge/',
     *          'uri'  => 'http://hogehoge/',
     *     ),
     * );
     * @var array
     */
    private static $_assets = array();
    
    public static function set($assets)
    {
        foreach($assets as $key => $data) {
            $data['path'] = isset($data['path']) ? $data['path'] : '';
            $data['uri']  = isset($data['uri']) ? $data['uri'] : '';
            $data['path'] = preg_match('/'. '\\' .DIRECTORY_SEPARATOR.'$/',$data['path']) ? substr($data['path'],0,-1) : $data['path'];
            $data['uri'] = preg_match('/'. '\\' .DIRECTORY_SEPARATOR.'$/',$data['uri']) ? substr($data['uri'],0,-1) : $data['uri'];
            self::$_assets[$key] = $data;
        }
    }
    
    public static function path($filename,$key='root')
    {
        return self::ret($filename,$key,'path');
    }
    
    public static function uri($filename,$key='root')
    {
       return self::ret($filename,$key,'uri');
    }
    
    private static function ret($filename,$key,$type)
    {
        if(!array_key_exists($key,self::$_assets)) {
            throw new \InvalidArgumentException();
        }
        $filename = !preg_match('/^'. '\\' . DIRECTORY_SEPARATOR .'/',$filename) ? DIRECTORY_SEPARATOR . $filename : $filename;
        return self::$_assets[$key][$type] . $filename;
    }
}

?>
