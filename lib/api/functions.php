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
    $con->close();
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
        array_push($output, array('success'=>$success, 'msg'=>$msg));
    }
    $con->close();
    return json_encode($output);
}

function CreateTable($databaseName, $tableName, $data)
{
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;

    if ($con->connect_errno)
        $msg = $con->connect_error;
    else if (!$con->select_db($databaseName))
        $msg = '不存在数据库' . $databaseName . '!';
    else {
        $result = $con->query("SHOW TABLES LIKE '" . $tableName . "'");
        if($con->errno)
            $msg = $con->error;
        else if ($result->num_rows == 1)
            $msg = '数据库' . $databaseName . '已存在表' . $tableName . '!';
        else {
            $data = explode(',', $data);
            $key = array();
            $countColumns = count($data) / 5;
            $sql = 'CREATE TABLE ' . $tableName . '(';
            for ($i = 0; $i < $countColumns; $i++) {
                $name = $data[$i * 5];
                $type = $data[$i * 5 + 1];
                $size = $data[$i * 5 + 2];
                $notNull = $data[$i * 5 + 3];
                $isKey = $data[$i * 5 + 4];

                $sql .= $name . ' ' . $type;
                if (!empty($size))
                    $sql .= '(' . $size . ')';
                if (!strcmp($notNull, 'true'))
                    $sql .= ' NOT NULL';
                if (!strcmp($isKey, 'true'))
                    array_push($key, $name);
                if (!(($i == $countColumns - 1) && count($key) == 0))
                    $sql .= ',';
            }
            if (count($key) != 0) {
                $sql .= 'PRIMARY KEY(';
                foreach ($key as $index => $value) {
                    $sql .= $value;
                    if ($index != count($key) - 1)
                        $sql .= ',';
                }
                $sql .= ')';
            }

            $sql .= ')ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $result = $con->query($sql);
            if($con->errno)
                $msg = $con->error;
            else {
                $success = true;
                $msg = '数据表'.$tableName.'创建成功！';
            }
        }
    }
    $con->close();
    return json_encode(Array('success'=>$success, 'msg'=>$msg));
}

function GetDatabaseDetail($databaseName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);

    $success = false;
    $msg = null;
    $data = array();

    $colHeaders = array('','表名','记录数');
    $columns = array(array('type'=>'text','className'=>'htCenter htMiddle','width'=>20, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle', 'width'=>100, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle', 'width'=>25));
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = "SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.tables WHERE TABLE_SCHEMA='$databaseName'";
        $result = $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $temp = array();
            while($row = $result->fetch_assoc()) {
                $tableName = $row['TABLE_NAME'];
                array_push($temp, $tableName);
            }
            $count = count($temp);
            $result->free_result();
            if(count($temp) == 0) {
                $success = true;
                array_push($temp,array('','',''));
            } else
                foreach ($temp as $value) {
                $result = $con->query('SELECT COUNT(*) FROM '.$databaseName.'.'.$value);
                if($con->errno)
                    $msg = $con->error;
                else {
                    $success = true;
                    if ($row = $result->fetch_assoc())
                        array_push($data, array('<a href="javascript:void(0)" style="color:red" class="delete-table" tb="'.$value.'">删除</a>','<a href="https://www.baidu.com" title="'.$value.'">'.$value.'</a>', $row['COUNT(*)']));
                }
            }
        }
    }
    $con->close();
    return $success?json_encode(array('success'=>$success,'colHeaders'=>$colHeaders,'columns'=>$columns,'data'=>$data,'temp'=>$sql,'count'=>$count)):json_encode(array('success'=>$success,'msg'=>$msg));
}

function DeleteTable($databaseName, $tableName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $con->query('DROP TABLE '.$databaseName.'.'.$tableName);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '数据表'.$tableName.'成功删除！';
        }
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}


function GetTablesList($databaseName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);


    $success = false;
    $msg = '';

    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $success = true;
        $result = $con->query('SHOW TABLES FROM '.$databaseName);
        while($row = $result->fetch_assoc()) {
            $value = $row[sprintf('Tables_in_%s',$databaseName)];
            $msg .= '<option value="' . $value . '">'.$value.'</option>';
        }
        $result->free_result();
    }

    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}