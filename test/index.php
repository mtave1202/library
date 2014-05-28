<?php
include_once('./_include.php');
$ids = array(
    array('test_id'=>1,'test2_id'=>1),
    array('test_id'=>2,'test2_id'=>1),
);
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
        <h3><?= htmlspecialchars('$storage->Test->primaryOne(1)')?></h3>
        <table>
        <?php foreach($storage->Test->primaryOne(1) as $key => $value):?>
            <tr><th><?=$key?></th><td><?=htmlspecialchars($value)?></td></tr>
        <?php endforeach?>
        </table>
        
        <h3><?= htmlspecialchars('$storage->Test_Test->primary('.var_export($ids,true).')')?></h3>
        <?php foreach($storage->Test_Test->primary($ids) as $primarys):?>
            <table>
            <?php foreach($primarys as $key => $value):?>
            <tr><th><?=$key?></th><td><?=htmlspecialchars($value)?></td></tr>
            <?php endforeach?>
            </table>
        <?php endforeach?>
    </body>
</html>