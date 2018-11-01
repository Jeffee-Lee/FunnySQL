<?php
require_once('settings.php');
require_once('./api/functions.php');

// GET请求处理
if(isset($_GET['type']) and !empty($_GET['type']) ) {
    switch ($_GET['type']) {
        // 1: 创建数据库
        case '1':
            echo CreateDatabase($_GET['databaseName'],$_GET['collationName']);
            break;
        case '2':
            echo DeleteDatabase($_GET['db']);
            break;
        case '3':
            echo CreateTable($_GET['db'],$_GET['tb'],$_GET['data']);
            break;
        case '4':
            echo DeleteTable($_GET['db'],$_GET['tb']);
            break;
        case '5':
            echo GetDatabaseDetail($_GET['db']);
            break;
        case '6':
            echo GetTablesList($_GET['db']);
            break;
        case '7':
            echo LoadTableData($_GET['db'],$_GET['tb'],$_GET['p']);
            break;
        case '8':
            echo DeleteTableData($_GET['db'],$_GET['tb'],$_GET['condition']);
            break;
        case '9':
            echo GetDatabases();
            break;
    }
};

// POST 请求处理
if(isset($_POST['type']) and !empty($_POST['type'])) {
    switch ($_POST['type']) {
        case '1':
            echo SetConnect($_POST['host'],$_POST['port'],$_POST['userName'],$_POST['password']);
            break;
        case '2':
            RemoveConnect();
            break;
        case '3':
            echo InsertData($_POST['db'],$_POST['tb'], $_POST['data']);
            break;
    }
};
