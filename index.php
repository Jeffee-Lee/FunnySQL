<?php
include('./lib/settings.php');
if(!array_key_exists('funnysql', $_COOKIE))
    header("Location: ".$domain .$path."login");
$con_info = explode(',', $_COOKIE['funnysql']);
$con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
?>
<!doctype html>
<html style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>概述 - FunnySQL</title>
    <link rel="shortcut icon" href="<?php echo $path;?>res/favicon.png">
    <link rel="stylesheet" href="<?php echo $path?>lib/jquery-ui/jquery-ui.min.css">
    <link href="https://cdn.bootcss.com/jstree/3.3.5/themes/default/style.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $path;?>lib/css.css">
</head>
<style>


    .left-body ul {
         margin: 0;
    }
</style>
<body>
<div id="msg"><span id="msg-body"></span><span id="msg-close" style="cursor: pointer;">X</span></div>
<div id="loader"></div>
<div id="fullScreen"></div>
<div id="close"><div class="close-head">确定离开？<a >X</a ></div><div class="close-body">
        <button>确定</button>
        <button>取消</button></div></div>
<div class="head">
    <a href="<?php echo $path?>" id="nav-home">
        <img src="<?php echo $path?>res/mysql.png"  class="icon home" width="18px" height="18px">&nbsp;概述</a>
    <a href="<?php echo $path?>database" id="nav-database">
        <img src="<?php echo $path?>res/database.png"  class="icon database" width="18px" height="18px">&nbsp;数据库</a>
    <a href="<?php echo $path?>new-delete-table" id="nav-table">
        <img src="<?php echo $path?>res/table.png"  class="icon table" width="18px" height="18px">&nbsp;数据表
    </a>
    <a href="javascript:void(0)" id="sql">&nbsp;SQL</a>
    <a href="#" id="exit">X</a>
</div>
<div class="main">
    <div class="left">
        <div class="block">
            <div class="block-head">数据库信息</div>
            <div class="block-body" style="text-align: left">
                <ul>
                    <li>连接：&nbsp;&nbsp;<?php echo mysqli_get_host_info($con);?></li>
                    <li>Mysql 版本：&nbsp;&nbsp;<?php echo mysqli_get_server_info($con)?></li>
                    <li>协议版本：&nbsp;&nbsp;<?php echo $con->protocol_version;?></li>
                    <li>当前用户：&nbsp;&nbsp;<?php
                        if($result = $con->query('SELECT USER()')) {
                            echo mysqli_fetch_array($result)['USER()'];
                        }
                        $result->free_result();
                        ?></li>
                    <br/>
                    <?php
                    $result = $con->query(' SHOW VARIABLES LIKE  \'char%\';');
                    $tempName = array('客户端默认字符集：','连接默认字符集：','数据库默认字符集：','文件系统默认字符集：','结果集默认字符集：','服务器默认字符集：','系统默认字符集：');
                    $tempIndex = 0;
                    while($row = $result->fetch_assoc()) {
                        if($tempIndex == 7)
                            break;
                        echo '<li>'.$tempName[$tempIndex].'&nbsp;&nbsp;'.$row['Value'].'</li>';
                        $tempIndex ++;
                    }
                    $result->free_result();
                    ?>
                </ul></div>
        </div>
    </div>
    <div class="right">
        <div class="block">
            <div class="block-head">数据库树状图<img id="refresh" src="<?php echo $path?>res/refresh.png" width="16px" height="16px" title="刷新"/></div>
            <div class="block-body"  style="text-align: left">
                <div id="tree"></div>
            </div>
        </div>

    </div>
</div>
<script src="<?php echo $path?>lib/jquery.min.js"></script>
<script src="<?php echo $path?>lib/jquery-ui/jquery-ui.js"></script>
<script src="<?php echo $path?>lib/jquery/jquery.cookie.min.js"></script>
<script src="https://cdn.bootcss.com/jstree/3.3.5/jstree.min.js"></script>
<script>

    $(document).ready(function(){
        /* Other Start */
        $('#nav-home').addClass('active');
        /* Other End */

        /* Common Part Start */
        function showLoader() {
            $('#loader').show();
        }
        function hideLoader() {
            $('#loader').hide();
        }
        function showMsg(Message, type) {
            let color = 'red';
            if(type === undefined || type === 'error')
                color = "red";
            else if (type === 'success')
                color = "#00ff2b";
            if(Message != null) {
                $("#msg").css('background',color).addClass("msgShow").find('#msg-body').text(Message).parent('#msg').show();
                setTimeout(function(){
                    $("#msg").removeClass("msgShow").find('#msg-body').text('').parent('#msg').hide();
                }, 3333)
            }
        }
        $("#msg-close").click(function () {
            $("#msg").removeClass("msgShow").find('#msg-body').text('').parent('#msg').hide();
        });
        $("#exit").click(function () {
            $("#close, #fullScreen").show();
        });
        $('.close-body button:first-child').click(function () {
            $.cookie('funnysql', '', {expires: -10, path: "<?php echo $path;?>"});
            window.location.href = '<?php echo $path;?>index';
        });
        $('.close-head a, .close-body button:last-child').click(function () {
            $("#close, #fullScreen").hide();
        });
        $("#close").draggable();
        /* Common Part End */

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
                window.location.href = '<?php echo $path;?>view-edit-table?db='+ db + '&tb=' + tb;
            }
        });
        function loadTree(){
            $.ajax({
                url: '<?php echo $path; ?>lib/api/GetDatabasesTreeView.php',
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
        $('#refresh').click(function () {
           loadTree();
        });
    });
</script>
</body>
</html>
