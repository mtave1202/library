<?php
function checkHash($password,$hashed_password)
{
    if(PHP_VERSION_ID > 50500) {
        return password_verify($password,$hashed_password);
    } else {
        return crypt($password,$hashed_password) === $hashed_password;
    }
}

function createHash($password,$cost)
{
    if(PHP_VERSION_ID > 50500 && false) {
        $hash = password_hash($password,PASSWORD_BCRYPT,['cost'=>$cost]);
    } else {
        //salt生成
        $strinit = "abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ012345679"; 
        $strarray = preg_split("//", $strinit, 0, PREG_SPLIT_NO_EMPTY); 
        for ($i = 0, $str = null; $i < 22; $i++) { 
            $str .= $strarray[array_rand($strarray, 1)]; 
        }
        $salt = '$2y$'.sprintf('%02d',$cost).'$'.$str;
        //$salt = '$2y$12$CvhMLWAfvTzx2AYZ30K7VK';
        $hash = crypt($password,$salt);
        
        echo $salt."<br/>";
        echo $hash."<br/>";
    }
    return $hash;
}


//CRYPT_BLOWFISHを用いたパスワードのハッシュ
//phpのバージョンが5.5以上の場合はpassword_hashを使う
$cost = 12;
$user_input = 'password';
$db_password = '$2y$12$tuhta64uXvQNrvfHKgEXNuT0cgdabEKfGhX4J8GUxkZh/pSOSpeXq';
if(checkHash($user_input,$db_password)) {
    echo "password verified!!<br/>";
} else {
    echo "password not verified...<br/>";
}


