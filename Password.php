<?php
/**
 * Description of Password
 *
 * @author Daisuke Abeyama
 */
namespace library;
class Password {
    protected static $_cost = 12;
    
    public static function setCost($cost)
    {
        $cost = (int)$cost;
        if($cost < 4) {
            $cost = 4;
        } else if($cost > 31) {
            $cost = 31;
        }
        self::$_cost = $cost;
    }
    
    public static function verify($password,$hashed_password)
    {
        if(PHP_VERSION_ID > 50500) {
            return password_verify($password,$hashed_password);
        } else {
            return crypt($password,$hashed_password) === $hashed_password;
        }
    }
    
    public static function hash($password)
    {
        if(PHP_VERSION_ID > 50500) {
            $hash = password_hash($password,PASSWORD_BCRYPT,['cost'=>self::$_cost]);
        } else {
            //salt生成
            $strinit = "abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ012345679"; 
            for ($i = 0, $str = null; $i < 22; ++$i) { 
                $str .= $strinit[mt_rand(0,strlen($strinit)-1)]; 
            }
            $salt = '$2y$'.sprintf('%02d',self::$_cost).'$'.$str;
            $hash = crypt($password,$salt);
        }
        return $hash;
    }
    
    public static function isNeedRehash($hash)
    {
        if(PHP_VERSION_ID > 50500) {
            return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => self::$_cost]);
        } else {
            if(substr($hash,0,4) !== '$2y$') {
                return true;
            }
            $cost = intval(substr($hash,4,2));
            return $cost !== self::$_cost;
        }
    }
}

?>
