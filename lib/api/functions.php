<?php
include "../settings.php";
/* GET 请求处理函数 */
// 创建数据库
function CreateDatabase($databaseName, $collationName) {
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

    $success = false;
    $msg = null;
    $data = array();

    $colHeaders = array('','表名','记录数');
    $columns = array(array('type'=>'text','className'=>'htCenter htMiddle','width'=>20, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle', 'width'=>100, 'renderer'=>'html'),array('type'=>'text','className'=>'htCenter htMiddle', 'width'=>25));
    $temp = array();
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $sql = "SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.tables WHERE TABLE_SCHEMA='$databaseName'";
        $result = $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
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
                            array_push($data, array('<a href="javascript:void(0)" class="delete-table delete" tb="'.$value.'">删除</a>','<a href="./view-edit-table.php?db='.$databaseName.'&tb='.$value.'" class="access " title="访问数据表 '.$value.'">'.$value.'</a>', $row['COUNT(*)']));
                    }
                }
        }
    }
    $con->close();

    $tbListHtml = '';
    foreach($temp as $value) {
        $tbListHtml .= "<option value='$value'>$value</option>";
    }

    return $success?json_encode(array('success'=>$success,'colHeaders'=>$colHeaders,'columns'=>$columns,'data'=>$data,'tbListHtml'=>$tbListHtml)):json_encode(array('success'=>$success,'msg'=>$msg));
}

// 删除数据表
function DeleteTable($databaseName, $tableName) {
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);


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
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

    $success = false;
    $msg = '';
    $data = array();
    $dbList = "";
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
                $dbList .= "<option value='{$row['SCHEMA_NAME']}'>{$row['SCHEMA_NAME']}</option>";
                array_push($data, array('<a href="javascript:void(0)" class="deleteDatabase delete" db="'.$row['SCHEMA_NAME'].'">删除</a>',"<a href='./new-delete-table.php?db=".$row['SCHEMA_NAME']."' title='访问数据库 ".$row['SCHEMA_NAME']."' class='access'>".$row['SCHEMA_NAME']."</a>",$row['DEFAULT_COLLATION_NAME']));
            }
        }
    }
    return json_encode(array('success'=>$success,'msg'=>$msg,'data'=>$data,"dbList"=>$dbList));
}

/**
 * 添加索引
 *
 * @param $db string 数据库名
 * @param $tb string 数据表名
 * @param $col string 列名
 * @param $type string 索引类型， 0: 普通索引; 1: 唯一索引; 2: 全文索引; 3: 空间索引
 * @param $name string 索引名
 * @return string
 *
 * @author jeffee
 */
function CreateIndex($db,$tb,$col,$type,$name) {
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

    $success = false;
    $msg = null;

    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        $arrIndex = array('0'=>'','1'=>' UNIQUE','2'=>' FULLTEXT','3'=>' SPATIAL');
        $sql = "CREATE{$arrIndex[$type]} INDEX `$name` ON $db.$tb(`$col`)";
        $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '索引创建成功！';
        }
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}

function AlterDatabase($db,$collation) {
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

    $success = false;
    $msg = null;
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {

        $sql = "ALTER DATABASE $db COLLATE $collation";
        $con->query($sql);
        if($con->errno)
            $msg = $con->error;
        else {
            $success = true;
            $msg = '编码创建成功！';
        }
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}

/* POST 请求处理函数 */
// login页面根据用户输入设置连接
function SetConnect($host,$port,$userName,$password) {

    function isGetAvailable($name) {
        return isset($_POST[$name]) && !empty($_POST[$name]);
    }
    $success = false;
    $msg = '';
    // 判断GET的数据，password字段可以为空
    if(isGetAvailable('host') && isGetAvailable("port") && isGetAvailable("userName") && isset($_POST["password"])) {
        $host = $_POST['host'];
        $port = $_POST["port"];
        $userName = $_POST["userName"];
        $password = $_POST['password'];
        $con = new mysqli($host, $userName, $password, '', $port);
        if($con->connect_errno)
            $msg = $con->connect_error;
        else {
            setcookie('session',base64_encode(json_encode(array('host'=>$host,"port"=>$port,"userName"=>$userName,"password"=>$password))),time() + 60*60*24, "/");
            $success = true;
            $msg = "连接成功！";
        }
        $con->close();
    } else
        $msg = '请检查请求';
    return json_encode(array('success'=>$success,"msg"=>$msg));
}
// 移除连接
function RemoveConnect(){
    setcookie('session','',time()-60*60*24,"/");
}

/**
 * 插入多行数据
 *
 * @param $db string 数据库名
 * @param $tb string 数据表名
 * @param $data array 二维数组，插入数据的集合，数组中每一项为一个整条插入的数据
 * @return string
 *
 * @author jeffee
 */
function InsertData($db, $tb, $data) {
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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
 * 清洗数据值
 * $value 为空时，返回字符串 "null";
 * $value 不为空时，如果对应列的属性为数字类型，返回原值，否则返回两侧添加"的$value值
 *
 * @param $db string 数据库名
 * @param $tb string 数据表名
 * @param $column string 插入的列名
 * @param $value string 需要清洗的值
 * @return string
 *
 * @author jeffee
 */
function CleanUpData($db, $tb, $column,$value) {
    if($value == null)
        return 'null';
    else {
        $con_info = json_decode(base64_decode($_COOKIE['session']));
        $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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

function UpdateData($db,$tb,$beforeChange,$afterChange) {
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

    $success = false;
    $msg = '';
    if($con->connect_errno)
        $msg = $con->connect_error;
    else {
        if(!$con->select_db($db))
            $msg = "不存在数据库$db";
        else {
            $result = $con->query("DESC $tb");
            if ($con->errno)
                $msg = $con->error;
            else {
                $columns = array();
                while ($row = $result->fetch_assoc())
                    array_push($columns, $row['Field']);
                $arrBeforeChange = array();
                foreach ($beforeChange as $index =>$value) {
                    array_push($arrBeforeChange, CleanUpData($db, $tb, $columns[$index], $value));
                }
                $condition = '';
                foreach ($columns as $index => $value)
                    $condition .= $value.'='.$arrBeforeChange[$index].' and ';
                $condition = substr($condition, 0, strlen($condition)-4);
                $arrAfterChange = array();
                foreach ($afterChange as $index=>$value) {
                    array_push($arrAfterChange, CleanUpData($db, $tb, $columns[$index], $value));
                }
                $con->query('set autocommit=0');
                $con->begin_transaction();
                $updateSuccess = true;
                foreach ($arrAfterChange as $index=>$value) {
                    $sql = "UPDATE $tb SET ".$columns[$index].'='.$value." WHERE $condition";
                    $con->query($sql);
                    if($con->errno) {
                        $updateSuccess = false;
                        $msg .= $con->error;
                        break;
                    } else {
                        $arrBeforeChange[$index] = $value;
                        $condition = '';
                        foreach ($columns as $i => $v)
                            $condition .= $v.'='.$arrBeforeChange[$i].' and ';
                        $condition = substr($condition, 0, strlen($condition)-4);
                    }
                }
                if($updateSuccess) {
                    $success = true;
                    $msg = "数据更新成功！";
                    $con->commit();
                } else {
                    $con->rollback();
                }
            }
        }
    }
    $con->close();
    return json_encode(array('success'=>$success,'msg'=>$msg));
}
//echo sql(array("SHOW DATABASES"));
function Sql($sql) {
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);
    $success = false;
    $msg = array();
    $i = 0;
    if($con->connect_errno)
        $msg = $con->connect_error;
    else
        while(true){
            if($i >= count($sql))
                break;
            if(strlen(str_replace(" ","",$sql[$i])) == 0) {
                $i ++;
                continue;
            }
            $success = true;
            $pushArray = "";
            $tSql = $sql[$i];
            $isTableResult = array("show","desc","describe","select");
            $isMsgResult = array("insert",'update',"alter","start","begin", "create", 'delete');
            $common = strtolower(array_values(array_filter(explode(' ',$tSql),"strlen"))[0]);

            if(in_array($common,$isTableResult)) {
                $startTime = microtime(true);
                $result = $con->query("$tSql");
                $costTime = microtime(true) - $startTime;
                if($con->errno)
                    $pushArray = SqlMsg($tSql,$costTime,"error",$con->error);
                else {
                    $pushArray = SqlTable($tSql,$result,$costTime);
                }
            } else if(in_array($common, $isMsgResult)) {
                $isTransaction = array("start","begin");
                if(in_array($common,$isTransaction)) {
                    $arrSql = array();
                    array_push($arrSql, $tSql);
                    $endTransaction = array('rollback', 'commit');
                    $i ++;
                    while($i < count($sql)) {
                        $tSql = $sql[$i];
                        $common = strtolower(array_values(array_filter(explode(' ',$tSql),"strlen"))[0]);
                        array_push($arrSql,$sql[$i]);
                        if(in_array($common, $endTransaction))
                            break;
                        $i ++;
                    }
                    $i --;
                    $tSql = implode(';', $arrSql);
                    $startTime = microtime(true);
                    foreach ($arrSql as $index=>$value) {
                        $con->query($value);
                        $costTime = microtime(true) - $startTime;
                        if($con->errno) {
                            $pushArray = SqlMsg($tSql,$costTime,"error",$con->error.' '.$tSql);
                            break;
                        }
                        if($index == count($arrSql) - 1)
                            $pushArray = SqlMsg($tSql,$costTime,"success",'事务操作完成');
                    }
                } else {
                    if($common == 'update') {
                        $startTime = microtime(true);
                        $con->query("$tSql");
                        $costTime = microtime(true) - $startTime;
                        if($con->errno)
                            $pushArray = SqlMsg($tSql,$costTime,"error",$con->error);
                        else {
                            $pushArray = SqlMsg($tSql,$costTime,"success",'更新语句执行成功');
                        }
                    } else if($common == 'insert') {
                        $startTime = microtime(true);
                        $con->query("$tSql");
                        $costTime = microtime(true) - $startTime;
                        if($con->errno)
                            $pushArray = SqlMsg($tSql,$costTime,"error",$con->error);
                        else {
                            $pushArray = SqlMsg($tSql,$costTime,"success",'插入语句执行成功');
                        }
                    } else if($common == 'create') {
                        $startTime = microtime(true);
                        $con->query("$tSql");
                        $costTime = microtime(true) - $startTime;
                        if($con->errno)
                            $pushArray = SqlMsg($tSql,$costTime,"error",$con->error);
                        else {
                            $pushArray = SqlMsg($tSql,$costTime,"success",'创建语句执行成功');
                        }
                    } else if($common == 'alter') {
                        $startTime = microtime(true);
                        $con->query("$tSql");
                        $costTime = microtime(true) - $startTime;
                        if($con->errno)
                            $pushArray = SqlMsg($tSql,$costTime,"error",$con->error);
                        else {
                            $pushArray = SqlMsg($tSql,$costTime,"success",'修改语句执行成功');
                        }
                    } else if($common == 'delete') {
                        $startTime = microtime(true);
                        $con->query("$tSql");
                        $costTime = microtime(true) - $startTime;
                        if($con->errno)
                            $pushArray = SqlMsg($tSql,$costTime,"error",$con->error);
                        else {
                            $pushArray = SqlMsg($tSql,$costTime,"success",'删除语句执行成功');
                        }
                    }
                }
            } else {
                 $con->query("$tSql");
                if($con->errno)
                    $pushArray = SqlMsg($tSql,0,"error",$con->error);
            }
            array_push($msg,$pushArray);
            $i ++;
        }
    return json_encode(array('success'=>$success,'msg'=>$msg));
}


function SqlMsg($sql,$costTime,$type,$msg) {
    if($type == "success")
        $headBackgroundColor = "#c5ff8582";
    else
        $headBackgroundColor = "#ff858561";
    $title = stripslashes($sql);
    if(strlen($sql) > 20)
        $sql = substr($sql,0,20)."...";
    return "<div class=\"block\" style=\"margin-top: 30px\"><div class=\"block-head\" style=\"background-color: $headBackgroundColor;\" title=\"$title\">$sql<span class='closeBlock' title='关闭' style='float: right;margin-left: 20px; margin-right: 10px'>X</span><span title='CostTime' style='float: right'>耗时: $costTime ms</span><div class=\"block-operate\"><a href=\"javascript:void(0)\" style=\"text-decoration: underline dotted;\" class=\"toggleBody\">收起&nbsp;↑</a></div></div><div class=\"block-body\">$msg</div></div>";
}

function SqlTable($sql,$result,$costTime) {

    $columns = "";
    foreach ($result->fetch_fields() as $columnName) {
        $columns .= "<th>".$columnName->name."</th>";
    }
    $data = array();
    while($row = $result->fetch_array(MYSQLI_NUM))
        array_push($data,$row);
    $data = json_encode($data);
    $id = uniqid("table_");
    $innerHtml = "<div class=\"block $id\" style=\"margin-top: 30px;\"><div class=\"block-head\" style=\"background-color:#c5ff8582\" title=\"$sql\">";
    if(strlen($sql) > 20)
        $innerHtml .= substr($sql,0,20)."...";
    else
        $innerHtml .= $sql;
    $innerHtml .= "<span class='closeBlock' title='关闭' style='float: right;margin-left: 20px; margin-right: 10px'>X</span><span title='CostTime' style='float: right'>耗时: $costTime ms</span><div class=\"block-operate\"><a href=\"javascript:void(0)\" style=\"text-decoration: underline dotted;\" class=\"toggleBody\">收起&nbsp;↑</a></div></div>";
    $innerHtml .= /** @lang text */
    <<<EOF
<div class="block-body">
    <div>
        <table id="$id" class="display nowrap" width="100%">
            <thead>
                <tr>
                    $columns
                </tr>
        </thead>
        </table>
            </div>
            </div></div>
            <script>
            $(document).ready(function () {
                let data = $data;
                var table = $('#$id').DataTable({
                    data: data,
                    "scrollX": true,
                    "info": false,
                    "search": "查找:",
                    "aaSorting": [],
                    "language": {
                        "zeroRecords":    "未匹配到任何结果",
                        "emptyTable": "无任何结果!",
                        "paginate": {
                            "first":      "首页",
                            "last":       "末页",
                            "next":       "下一页",
                            "previous":   "上一页"
                        },
                        "lengthMenu":     "显示 _MENU_ 项 ",
                    },
                });
            });
        </script>
EOF;
    return $innerHtml;
}