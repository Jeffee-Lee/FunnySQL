<?php
error_reporting(0);
include('./lib/settings.php');
if(!array_key_exists('session', $_COOKIE))
    header("Location: ./login.php");
$con_info = json_decode(base64_decode($_COOKIE['session']));
$con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);
?>
<!doctype html>
<html style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $PAGE_TITLE_HOME;?></title>
    <link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
    <link rel="stylesheet" href="./lib/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" href="./lib/jstree/3.3.5/themes/default/style.min.css">
    <link rel="stylesheet" href="./lib/css.css">
</head>
<style>

    .left-body ul {
         margin: 0;
    }
</style>
<body>
<div id="msg"><span id="msg-body"></span><span id="msg-close" style="cursor: pointer;">X</span></div>
<div id="loader">
</div>
<div id="fullScreen"></div>
<div id="close"><div class="close-head">确定离开？<a >X</a ></div><div class="close-body">
        <button>确定</button>
        <button>取消</button></div></div>
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
    <a href="javascript:void(0)" id="exit">X</a>
</div>
<div class="main">
    <div class="left">
        <div class="block">
            <div class="block-head">数据库信息</div>
            <div class="block-body" style="text-align: left; margin-left: 16px;">
                <table>
                    <tbody>
                    <tr>
                        <td>连接：</td>
                        <td><?php echo mysqli_get_host_info($con);?></td>
                    </tr>
                    <tr>
                        <td>Mysql 版本：</td>
                        <td><?php echo mysqli_get_server_info($con)?></td>
                    </tr>
                    <tr>
                        <td>协议版本：</td>
                        <td><?php echo $con->protocol_version;?></td>
                    </tr>
                    <tr>
                        <td>当前用户：</td>
                        <td><?php
                            if($result = $con->query('SELECT USER()')) {
                                echo mysqli_fetch_array($result)['USER()'];
                            }
                            $result->free_result();
                            ?></td>
                    </tr>
                    <tr>
                        <td>Mysql 安装路径：</td>
                        <td>
                            <?php
                            if($row = $con->query("select @@basedir as basePath from dual;")->fetch_array(MYSQLI_NUM))
                                echo $row[0];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Mysql 数据文件路径：</td>
                        <td>
                            <?php
                            if($row = $con->query("show global variables like \"%datadir%\";")->fetch_assoc())
                                echo $row["Value"];
                            ?>
                        </td>
                    </tr>
                    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                    <?php
                    $result = $con->query(' SHOW VARIABLES LIKE  \'char%\';');
                    $tempName = array('客户端默认字符集：','连接默认字符集：','数据库默认字符集：','文件系统默认字符集：','结果集默认字符集：','服务器默认字符集：','系统默认字符集：');
                    $tempIndex = 0;
                    while($row = $result->fetch_assoc()) {
                        if($tempIndex == 7)
                            break;
                        echo '<tr><td>'.$tempName[$tempIndex].'</td><td>'.$row['Value'].'</td><tr>';
                        $tempIndex ++;
                    }
                    $result->free_result();
                    ?>
                    </tbody>
                </table>

            </div>
        </div>
        
    </div>
    <div class="right">
        <div class="block">
            <div class="block-head">数据库树状图<img id="refresh" src="./res/refresh.png" width="16px" height="16px" title="刷新"/></div>
            <div class="block-body"  style="text-align: left">
                <div id="tree"></div>
            </div>
        </div>

    </div>
</div>
<script src="./lib/jquery.min.js"></script>
<script src="./lib/jquery-ui/jquery-ui.js"></script>
<script src="./lib/jquery/jquery.cookie.min.js"></script>
<script src="./lib/jstree/3.3.5/jstree.min.js"></script>
<script src="./lib/js.js"></script>
<script>

    $(document).ready(function(){
        $('.close-body button:first-child').click(function () {
            $.ajax({
                url: './lib/Processing.php',
                method: 'post',
                data: {'type': '2'},
                success: function () {
                    window.location.href = './';
                }
            });
        });
        $('#refresh').click(function () {
            loadTree();
        });
        $("#tree").jstree({
            "plugins" : [
                "sort",
            ]
        });
        $("#tree").bind('dblclick', function (e) {
            let node = $(this).jstree().get_node(e.target);
            if(!$(this).jstree('is_leaf',node)) {
                if(!$(this).jstree('is_open', node))
                    $(this).jstree('close_node', node);
                else {
                    $(this).jstree('open_node', node);
                }
            }
            else {
                let parent = $(this).jstree().get_node(node.parents[0]);
                let db = parent.text;
                let tb = node.text;
                window.location.href = './view-edit-table.php?db='+ db + '&tb=' + tb;
            }
        });
        function loadTree(){
            $.ajax({
                url: './lib/api/GetDatabasesTreeView.php',
                dataType: 'json',
                beforeSend: function() {
                    showLoader();
                },
                success: function (data) {
                    $('#tree').jstree(true).settings.core.data = data;
                    $('#tree').jstree(true).refresh();
                },
                complete: function() {
                    hideLoader();
                },
            });
        }
        loadTree();

    });
</script>
</body>
</html>
