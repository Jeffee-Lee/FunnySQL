<?php
include('./lib/settings.php');
session_start([
    "gc_maxlifetime"=> 60 * 60 * 24 * 7,
    "cookie_lifetime"=> 60 * 60 * 24 * 7
]);
if(!array_key_exists('host', $_SESSION))
    header("Location: ./login.php");
//$con_info = json_decode(base64_decode($_COOKIE['session']));
$con = new mysqli($_SESSION["host"], $_SESSION["userName"], $_SESSION["password"], '', $_SESSION["port"]);

// 获取数据库列表
$databases = array();
$result = $con->query('SHOW DATABASES;');
while($row = $result->fetch_assoc())
    array_push($databases, $row['Database']);
$result->free_result();


// 连接的数据库
$dba = $databases[0];
if(isset($_GET['db']) and !empty($_GET['db']))
    $dba = $_GET['db'];

// 获取数据库对应的数据表列表
$tables =array();
$result = $con->query('SHOW TABLES FROM '.$dba);
while($row = $result->fetch_assoc()) {
    array_push($tables,$row[sprintf('Tables_in_%s',$dba)]);
}
$result->free_result();

// 连接的数据表
$tb = $tables[0];
if(isset($_GET['tb']) and !empty($_GET['tb']))
    $tb = $_GET['tb'];

?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $PAGE_TITLE_Toolbox;?></title>
    <link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
    <link rel="stylesheet" href="./lib/handsontable/handsontable.full.min.css">
    <link rel="stylesheet" href="./lib/css.css">

</head>
<body>
<div id="common">
    <div id="msg" v-show="isMsgShow" style="display: none;" :class="{'msgShow':isMsgShow, 'errorMsg':isShowError, 'successMsg': isShowSuccess}">
        <span id="msg-body">{{ message }}</span>
        <span id="msg-close" style="cursor: pointer;" @click="hideMsg">X</span>
    </div>
    <div id="loader" style="display: none;" v-show="isShowLoader"></div>
    <div id="fullScreen" v-show="isShowClose" style="display: none;"></div>
    <div id="close" v-show="isShowClose" style="display: none">
        <div class="close-head">确定离开？<span title="关闭" @click="hideClose">X</span></div>
        <div class="close-body">
            <button @click="logout">确定</button>
            <button @click="hideClose">取消</button>
        </div>
    </div>
    <div class="head">
        <a href="lib" id="nav-home">
            <img src="./res/mysql.png"  class="icon home icon-inactive">
            <img src="./res/mysql_active.png"  class="icon home icon-active">
            &nbsp;概述
        </a>
        <a href="./database.php" id="nav-database">
            <img src="./res/database.png"  class="icon database icon-inactive" >
            <img src="./res/database_active.png"  class="icon database icon-active">
            &nbsp;数据库
        </a>
        <a href="./new-delete-table.php" id="nav-table">
            <img src="./res/table.png"  class="icon table icon-inactive">
            <img src="./res/table_active.png"  class="icon table icon-active">
            &nbsp;数据表
        </a>
        <a href="./sql.php" id="nav-sql">
            <img src="./res/sql.png"  class="icon sql icon-inactive" >
            <img src="./res/sql_active.png"  class="icon sql icon-active">
            &nbsp;SQL
        </a>
        <a href="./toolbox.php" id="nav-backup" class="active">
            <img src="./res/backup.png"  class="icon backup icon-inactive" >
            <img src="./res/backup_active.png"  class="icon backup icon-active">
            &nbsp;工具箱
        </a>
        <span id="exit" title="退出" @click="showClose">X</span>
    </div>
</div>

<div class="main" id="app">

    <div class="left">
        <div class="block">
            <div class="block-head">备份</div>
            <div class="block-body">
                <select name="databaseName" title="选择数据库" class="input" v-model="databaseName">
                    <?php
                    foreach ($databases as $value) {
                        $output = '<option value="' . $value . '" ';
                        if(!strcmp($value, $dba))
                            $output .= 'selected';
                        $output .= '>'.$value.'</option>';
                        echo $output;
                    }
                    ?>
                </select>
                <select name="tableName" :title="isHasTable ? '选择数据表' : '无数据表可选择'"
                        class="input" v-model="tableName" :disabled="!isHasTable">
                    <option :value="table" v-for="table in tableList">{{ table }}</option>
                </select>
                <button @click="backupDatabase">备份数据库</button>
                <button @click="backupTable" :disabled="!isHasTable"
                        :title="isHasTable ?'':'无数据表可备份'" :disabled="!isHasTable">备份数据表</button>
            </div>
        </div>
    </div>
</div>

<script src="./lib/jquery.min.js"></script>
<script src="./lib/handsontable/handsontable.full.min.js"></script>
<script src="./lib/vue.min.js"></script>
<script src="./lib/axios.min.js"></script>
<script src="./lib/js.js"></script>
<script>
    let app = new Vue({
        el: "#app",
        data: {
            databaseName: "<?php echo $dba;?>",
            tableName: "<?php echo $tb;?>",
            tableList: <?php echo json_encode($tables);?>,
            isHasTable: true
        },
        methods: {
            backupDatabase: function () {
                window.location = './lib/api/backupDb.php?db=' + app.databaseName;
            },
            backupTable: function () {
                window.location = './lib/api/backupTb.php?db=' + app.databaseName + '&tb=' + app.tableName;
            }
        },
        watch: {
            databaseName: function (val) {
                this.databaseName = val;
                axios.get("./lib/api/GetTableList.php?db=" + val)
                    .then(function (response) {
                        let data = response.data;
                        if (data.length !== 0) {
                            app.isHasTable = true;
                            app.tableList = data;
                            app.tableName = data[0];
                        } else {
                            app.tableList = null;
                            app.tableName = null;
                            app.isHasTable = false;
                        }
                    })
            }
        }
    });
</script>

</body>
</html>
