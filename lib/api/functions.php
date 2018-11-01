<?php
include "../settings.php";
/* GET 请求处理函数 */
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

// 删除数据库
function DeleteDatabase($databaseName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
    $success = false;
    $msg = '';
    $system = array('information_schema', 'mysql', 'performance_schema', 'sys');
    if(in_array($databaseName,$system))
        $msg = '无法删除系统表！';
    else if($con->errno)
        $msg = $con->error;
    else {
        if(!$con->select_db($databaseName)) {
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

    }
    $con->close();
    return json_encode(array('success'=>$success, 'msg'=>$msg));
}
// 创建数据表
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
// 获取数据库名及编码列表
function GetDatabaseDetail($databaseName) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;
    $data = array();
    global $path;

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
                            array_push($data, array('<a href="javascript:void(0)" class="delete-table delete" tb="'.$value.'">删除</a>','<a href="'.$path.'view-edit-table?db='.$databaseName.'&tb='.$value.'" class="access " title="访问数据表 '.$value.'">'.$value.'</a>', $row['COUNT(*)']));
                    }
                }
        }
    }
    $con->close();
    return $success?json_encode(array('success'=>$success,'colHeaders'=>$colHeaders,'columns'=>$columns,'data'=>$data)):json_encode(array('success'=>$success,'msg'=>$msg));
}

// 删除数据表
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
// 获取数据表列表，选择框下拉内嵌代码
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
// 获取数据表的数据
function LoadTableData($databaseName, $tableName, $page = 1,$limit = 30) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);

    $success = false;
    $msg = null;
    $data = array();

    $colHeaders = array('','');
    $columnsName = array();
    $columns = array(array('type'=>'text','className'=>'htCenter htMiddle','width'=>70, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle','width'=>70, 'renderer'=>'html'));
    $key = array();
    $totalPage = 0;

    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = "SELECT COLUMN_NAME,DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$databaseName' AND TABLE_NAME='$tableName'";
        $result = $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            $isDate = array('date','time','year','datetime','timestamp');
            while($row = $result->fetch_assoc()) {
                $colHeaderTemp = $row['COLUMN_NAME'];
                array_push($colHeaders,$colHeaderTemp);
                array_push($columnsName, $colHeaderTemp);
                if(!in_array($row['DATA_TYPE'],$isDate))
                    $columnTemp = array('type'=>'text','className'=>'htCenter');
                else {
                    switch ($row['DATA_TYPE']) {
                        case 'date':
                            $columnTemp = array('type'=>'date','className'=>'htCenter','dateFormat'=>'YYYY-MM-DD');
                            break;
                        case 'time':
                            $columnTemp = array('type'=>'date','className'=>'htCenter','dateFormat'=>'HH:mm:ss');
                            break;
                        case 'year':
                            $columnTemp = array('type'=>'date','className'=>'htCenter','dateFormat'=>'YYYY');
                            break;
                        default:
                            $columnTemp = array('type'=>'date','className'=>'htCenter','dateFormat'=>'YYYY-MM-DD HH:mm:ss.SSSSSS');
                    }
                }
                array_push($columns,$columnTemp);
            }
            $sql = "SELECT * FROM $databaseName.$tableName LIMIT ".($page - 1)*$limit.','.$limit;
            $result = $con->query($sql);
            if($con->errno) {
                $success = false;
                $msg = $con->error;
            } else {
                $success = true;

                 $count = 0;
                while($row = $result->fetch_assoc()) {

                    $list = array("<a href='javascript:void(0)' class='edit editData' row='$count'>编辑</a>","<a href='javascript:void(0)' class='deleteData delete'column='$count'>删除</a>");
                    foreach ($columnsName as $item) {
                        $value = $row[$item];
                        array_push($list, $value);
                    }
                    array_push($data,$list);
                    $count ++;
                }
                $result = $con->query("SELECT COUNT(*) FROM $databaseName.$tableName");
                if($row = $result->fetch_assoc())
                    $totalPage = ceil(intval($row['COUNT(*)'])/$limit);


            }
            $result->free_result();
        }
    }
    $con->close();
    if($totalPage == 0)
        $page = 0;
    return $success?json_encode(array('success'=>$success,'colHeaders'=>$colHeaders,'columns'=>$columns,'data'=>$data,'totalPage'=>$totalPage,'currentPage'=>$page)):json_encode(array('success'=>$success,'msg'=>$msg));
}
// 删除一条数据记录
function DeleteTableData($databaseName, $tableName, $condition) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;
    $adaa = $condition;
    $condition = substr($condition, 0, strlen($condition) -4);
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = "DELETE FROM $databaseName.$tableName WHERE $condition";
        $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '删除成功！';
        }
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg, 'sql'=>$adaa));
}
// 获取数据库列表
function GetDatabases() {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
    $success = false;
    $msg = '';
    $data = array();
    global $path;
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = 'SELECT SCHEMA_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA;';
        $result = $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            while($row = $result->fetch_assoc()) {
                array_push($data, array('<a href="javascript:void(0)" class="deleteDatabase delete" db="'.$row['SCHEMA_NAME'].'">删除</a>',"<a href='".$path."new-delete-table?db=".$row['SCHEMA_NAME']."' title='访问数据库 ".$row['SCHEMA_NAME']."' class='access'>".$row['SCHEMA_NAME']."</a>",$row['DEFAULT_COLLATION_NAME']));
            }
        }
    }
    return json_encode(array('success'=>$success,'msg'=>$msg,'data'=>$data));
}


/* POST 请求处理函数 */
// login页面根据用户输入设置连接
function SetConnect($host,$port,$userName,$password) {
    global $path;
    $success = false;
    $msg = '';
    $con = new mysqli($host, $userName, $password,'',$port);
    if($con->connect_errno) {
        $msg = $con->connect_error;
    } else {
        $success = true;
        $cookie = $host.','.$port.','.$userName.','.$password;
        setcookie('funnysql',$cookie,time()+60*60*24,$path);
        $msg = '连接成功！';
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}
// 移除连接
function RemoveConnect(){
    global $path;
    setcookie('funnysql','',time()-60*60*24,$path);
}
/**
 * 插入多行数据
 * 
 * @param: $db string 数据库名
 * @param: $tb string 数据表名
 * @param: $data array 二维数组，插入数据的集合，数组中每一项为一个整条插入的数据
 * @return: string (json)
 * 
 * @author: jeffee 
 */
function InsertData($db, $tb, $data) {
    $con_info = explode(',', $_COOKIE['funnysql']);
    $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
    $success = false;
    $msg = null;

    if($con->connect_errno) {
        $msg = $con->connect_error;
    } else {
        if(!$con->select_db($db))
            $msg = "不存在数据库$db";
        else {
            $result = $con->query("DESC $tb");
            if($con->errno)
                $msg = $con->error;
            else {
                $columns = array();
                while($row = $result->fetch_assoc())
                    array_push($columns, $row['Field']);
                $strColumns = implode(',',$columns);
                $arrValue = array();
                foreach ($data as $value){
                    $temp = array();

                    foreach ($value as $index =>$column) {
                        array_push($temp, CleanUpData($db, $tb, $columns[$index], $column));
                    }
                    array_push($arrValue, '('.implode(',',$temp).')');
                }
                $con->query('set autocommit=0');
                $con->begin_transaction();
                $insertSuccess = true;

                foreach ($arrValue as $index=>$item) {
                    $con->query("INSERT INTO $tb($strColumns) VALUES $item");
                    if($con->errno) {
                        $insertSuccess = false;
                        $msg .= $con->error.'<br>';
                    }
                }
                if($insertSuccess) {
                    $success = true;
                    $msg = '数据插入成功！';
                    $con->commit();
                } else {
                    $msg = substr($msg,0, strlen($msg) -4);
                    $con->rollback();
                }
            }
        }
    }
    $con->close();
    return json_encode(array('success'=>$success, 'msg' => $msg));
}
/**
 * 清洗数据值，用于InsertData
 * $value 为空时，返回字符串 "null";
 * $value 不为空时，如果对应列的属性为数字类型，返回原值，否则返回两侧添加"的$value值
 * 
 * @param: $db string 数据库名
 * @param: $tb string 数据表名
 * @param: $column string 插入的列名
 * @param: $value string 需要清洗的值
 * @return: string
 * 
 * @author: jeffee
 */
function CleanUpData($db, $tb, $column,$value) {
    if($value == null)
        return 'null';
    else {
        $con_info = explode(',', $_COOKIE['funnysql']);
        $con = new mysqli($con_info[0], $con_info[2], $con_info[3], '', $con_info[1]);
        $isDigital = array('tinyint','smallint','mediumint','int','bigint','float','double','decimal');
        $result = $con->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$tb' AND COLUMN_NAME='$column'");
        if($row = $result->fetch_assoc()) {
            if(in_array($row['DATA_TYPE'],$isDigital))
                return $value;
            else
                return '"'.$value.'"';
        }
        $result->close();
        $con->close();
    }
}