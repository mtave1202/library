<?php
include_once('./_include.php');
$p = $_POST;
$command = isset($p['command']) ? $p['command'] : null;
if($command === 'login') {
    //登録処理
    $id = isset($p['id']) ? $p['id'] : null;
    $password = isset($p['password']) ? $p['password'] : null;
    if(!empty($id) && !empty($password)) {
        $user = $storage->User->primaryOne($id);
        if(!empty($user)) {
            $hash = $user['password'];
            if(\library\Password::verify($password, $hash)) {
                echo "ログイン成功<br/>";
            }
        } else {
            echo "Error:入力内容に不正があります<br/>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>HTML5サンプル</title>
    <style>
        table {
            border-collapse: collapse;
            margin-bottom:5px;
        }
        th{
            width: 25%;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            color: #333;
            background-color: #eee;
            border: 1px solid #b9b9b9;
        }
        td{
            padding: 6px;
            background-color: #fff;
            border: 1px solid #b9b9b9;
        }
    </style>
</head>
    <body>
        <form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
            <table>
                <tr><th>ID</th><td><input type="text" name="id" maxlength="20" /></td></tr>
                <tr><th>パスワード</th><td><input type="password" name="password" maxlength="20" /></td></tr>
                <tr><td colspan="2"><input type="submit" value="ログイン" /></td></tr>
            </table>
            <input type="hidden" name="command" value="login"/>
        </form>
    </body>
</html>