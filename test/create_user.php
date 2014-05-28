<?php
include_once('./_include.php');
$p = $_POST;
$command = isset($p['command']) ? $p['command'] : null;
if($command === 'add') {
    //登録処理
    $name = isset($p['name']) ? $p['name'] : null;
    $password = isset($p['password']) ? $p['password'] : null;
    if(!empty($name) && !empty($password)) {
        $hash = \library\Password::hash($password);
        $values = array(
            'name' => $name,
            'password' => $hash,
            'create_time' => time(),
        );
        $storage->beginTransaction();
        $re = $storage->User->insertOne($values);
        $re ? $storage->commit() : $storage->rollback();
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
                <tr><th>名前</th><td><input type="text" name="name" maxlength="20" /></td></tr>
                <tr><th>パスワード</th><td><input type="password" name="password" maxlength="20" /></td></tr>
                <tr><td colspan="2"><input type="submit" value="登録" /></td></tr>
            </table>
            <input type="hidden" name="command" value="add"/>
        </form>
    </body>
</html>