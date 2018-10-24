<?php

// 创建数据库
function CreateDatabase($databaseName, $collationName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
    $success = false;
    $msg = '';
    if($con->connect_errno) {
        $msg = $con->connect_error;
    } else if($con->select_db($databaseName)) {
        $msg = '数据库'.$databaseName.'已存在！';
    } else {
        $sql = 'CREATE DATABASE '.$databaseName.' COLLATE '.$collationName;
        $con->query($sql);
        if($con->error)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '数据库'.$databaseName.'创建成功！';
        }
    }
    return json_encode(array('success'=>$success, 'msg'=>$msg));
}

// 删除数据库(一个或多个)
function DeleteDatabase($databaseNamesList) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
    $output = array();

    // 将删除的数据库列表字符串转换为数组
    $databaseNamesList = explode(',', $databaseNamesList);
    foreach ($databaseNamesList as $databaseName) {
        $success = false;
        $msg = '';
        if($con->connect_errno) {
            $msg = $con->connect_error;
        } else if(!$con->select_db($databaseName)) {
            $msg = '数据库'.$databaseName.'不存在！';
        } else {
            $sql = 'DROP DATABASE '.$databaseName;
            $con->query($sql);
            if($con->error)
                $msg = $con->error;
            else
            {
                $success = true;
                $msg = '数据库'.$databaseName.'删除成功！';
            }
        }
        array_push($output, json_encode(array('success'=>$success, 'msg'=>$msg)));
    }
    return json_encode($output);
}