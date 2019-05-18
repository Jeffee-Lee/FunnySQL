<?php
    error_reporting(0);
    include('./lib/settings.php');
    session_start([
        "gc_maxlifetime"=> 60 * 60 * 24 * 7,
        "cookie_lifetime"=> 60 * 60 * 24 * 7
    ]);
    if(!array_key_exists('host', $_SESSION))
        header("Location: ./login.php");
    $con = new mysqli($_SESSION["host"], $_SESSION["userName"], $_SESSION["password"], '', $_SESSION["port"]);

    $output = array();

    $output[0] = mysqli_get_host_info($con);
    $output[1] = mysqli_get_server_info($con);
    $output[2] = $con->protocol_version;

    if($result = $con->query('SELECT USER()')) {
        $output[3] = mysqli_fetch_array($result)['USER()'];
    }
    $result->free_result();

    if($row = $con->query("select @@basedir as basePath from dual;")->fetch_array(MYSQLI_NUM)) {
        $output[4] = $row[0];
    }

    if($row = $con->query("show global variables like \"%datadir%\";")->fetch_assoc()) {
        $output[5] = $row["Value"];
    }

    $result = $con->query(' SHOW VARIABLES LIKE  \'char%\';');
    $tempName = array('客户端默认字符集：','连接默认字符集：','数据库默认字符集：','文件系统默认字符集：','结果集默认字符集：','服务器默认字符集：','系统默认字符集：');
    $tempIndex = 0;
    while($row = $result->fetch_assoc()) {
        if($tempIndex == 7)
            break;
        $output[6 + $tempIndex] = $row['Value'];
        $tempIndex ++;
    }
    $result->free_result();
    ?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $PAGE_TITLE_HOME;?></title>
    <link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
    <link rel="stylesheet" href="./lib/jstree/3.3.8/themes/default/style.min.css">
    <link rel="stylesheet" href="./lib/css.css">
</head>
<body>
<div>
    <div id="common">
        <div id="msg" v-show="isMsgShow" style="display: none;" :class="{'msgShow':isMsgShow, 'errorMsg':isShowError, 'successMsg': isShowSuccess}">
            <span id="msg-body">{{ message }}</span>
            <span id="msg-close" style="cursor: pointer;" @click="hideMsg">X</span>
        </div>
        <div id="loader" style="display: none;" v-show="isShowLoader"></div>
        <div id="fullScreen" v-show="isShowClose" style="display: none;"></div>
        <div id="close" v-show="isShowClose" style="display: none">
            <div class="close-head">确定离开？<span title="关闭" @click="isShowClose = false">X</span></div>
            <div class="close-body">
                <button @click="logout">确定</button>
                <button @click="isShowClose = false">取消</button>
            </div>
        </div>
        <div class="head">
            <a href="./" id="nav-home" class="active">
                <img src="./res/mysql_active.png"  class="icon home" >
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
            <span id="exit" title="退出" @click="isShowClose = true">X</span>
        </div>
    </div>
    <div class="main" id="app">
        <div class="left">
            <div class="block">
                <div class="block-head">数据库信息</div>
                <div class="block-body" style="text-align: left; margin-left: 16px;">
                    <table>
                        <tbody>
                        <tr>
                            <td>连接：</td>
                            <td><?php echo $output[0];?></td>
                        </tr>
                        <tr>
                            <td>Mysql 版本：</td>
                            <td><?php echo $output[1];?></td>
                        </tr>
                        <tr>
                            <td>协议版本：</td>
                            <td><?php echo $output[2];?></td>
                        </tr>
                        <tr>
                            <td>当前用户：</td>
                            <td><?php echo $output[3];?></td>
                        </tr>
                        <tr>
                            <td>Mysql 安装路径：</td>
                            <td><?php echo $output[4];?></td>
                        </tr>
                        <tr>
                            <td>Mysql 数据文件路径：</td>
                            <td><?php echo $output[5];?></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>客户端默认字符集：</td>
                            <td><?php echo $output[6];?></td>
                        </tr>
                        <tr>
                            <td>连接默认字符集：</td>
                            <td><?php echo $output[7];?></td>
                        </tr>
                        <tr>
                            <td>数据库默认字符集：</td>
                            <td><?php echo $output[8];?></td>
                        </tr>
                        <tr>
                            <td>文件系统默认字符集：</td>
                            <td><?php echo $output[9];?></td>
                        </tr>
                        <tr>
                            <td>结果集默认字符集：</td>
                            <td><?php echo $output[10];?></td>
                        </tr>
                        <tr>
                            <td>服务器默认字符集：</td>
                            <td><?php echo $output[11];?></td>
                        </tr>
                        <tr>
                            <td>系统默认字符集：：</td>
                            <td><?php echo $output[12];?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="right">
            <div class="block">
                <div class="block-head">数据库树状图
                    <img id="refresh" src="./res/refresh.png" width="16px" height="16px" title="刷新" @click="refresh"/></div>
                <div class="block-body"  style="text-align: left">
                    <div id="tree" @dblclick="selectDb"></div>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="./lib/jquery.min.js"></script>
<script src="./lib/jstree/3.3.8/jstree.min.js"></script>
<script src="./lib/vue.min.js"></script>
<script src="./lib/axios.min.js"></script>
<script src="./lib/js.js"></script>
<script>
    let app = new Vue({
        el: "#app",
        methods: {
            refresh: function () {
                app.loadTree();
            },
            loadTree: function () {
                common.showLoader();
                axios.get("./lib/api/GetDatabasesTreeView.php")
                    .then(function (response) {
                        let data = response.data;
                        let tree = $('#tree');

                        tree.jstree(true).settings.core.data = data;
                        tree.jstree(true).refresh();
                    })
                    .finally(function () {
                        common.hideLoader();
                    })
            },
            selectDb: function (e) {
                let tree = $('#tree');
                let node = tree.jstree().get_node(e.target);

                if (!tree.jstree('is_leaf',node)) {
                    if (!tree.jstree('is_open', node)) {
                        tree.jstree('close_node', node);
                    } else {
                        tree.jstree('open_node', node);
                    }
                } else {
                    let parent = tree.jstree().get_node(node.parents[0]);
                    let db = parent.text;
                    let tb = node.text;
                    if (db) {
                        window.location.href = './view-edit-table.php?db='+ db + '&tb=' + tb;
                    }
                }
            }
        },
        mounted: function () {
            this.$nextTick(function () {
                let tree = $('#tree');
                tree.jstree({
                    "plugins" : [
                        "sort",
                    ]
                });
                app.loadTree();
            });
        }
    });
</script>
</body>
</html>
