<?php
require_once('settings.php');
require_once('./api/functions.php');

//获取GET请求类型
if(isset($_GET['type']) and !empty($_GET['type']) ) {
    switch ($_GET['type']) {
        // 1: 创建数据库
        case '1':
            echo CreateDatabase($_GET['databaseName'],$_GET['collationName']);
            break;
        case '2':
            echo DeleteDatabase($_GET['databaseNamesList']);
            break;
        case '3':
            echo gettype($_GET['data']).' '.$_GET['data'].' '.(explode(',',$_GET['data'])[0] == '');
            break;
    }
}