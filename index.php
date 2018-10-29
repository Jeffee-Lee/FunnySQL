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

    .info, .database-tree{
        display: block;
        border: 1px solid #e7eaed;
        margin: 30px 20px;
        border-radius: 10px;
    }
    .database-tree {
        float: left;
        width: 50%;
    }
    .info {
        float: right;
        width: 40%;
    }
    .info-head, .database-tree-head{
        background: #bbbbbb;
        color: #ffffff;
        padding: 9px;
        font-size: 20px;
        border-radius: 10px 10px 0 0;
    }
    .info-body ul {
        margin: 0;
    }
    .info-body, .database-tree-body{
        padding: 18px 0;
        background: #f3f3f3;
    }
</style>
<body style="background: white;">
<div id="block"></div>
<div id="msg"><div class="msg-head">确定离开？<a >X</a ></div><div class="msg-body">
        <button>确定</button>
        <button>取消</button></div></div>
<div class="head">
    <a href="<?php echo $path?>" id="nav-home">
        <img src="<?php echo $path?>res/mysql.png"  class="icon home" width="18px" height="18px">&nbsp;概述</a>
    <a href="mysql_database.php" id="nav-database">
        <img src="<?php echo $path?>res/database.png"  class="icon database" width="18px" height="18px">&nbsp;数据库</a>
    <a href="new-delete-table" id="nav-table">
        <img src="<?php echo $path?>res/table.png"  class="icon table" width="18px" height="18px">&nbsp;数据表
    </a>
    <a href="javascript:void(0)" id="sql">&nbsp;SQL</a>
    <a href="#" id="exit"> X </a>
</div>
<div class="main">
    <div class="info">
        <div class="info-head">数据库服务器</div>
        <div class="info-body">
            <ul>
                <li>服务器：&nbsp;&nbsp;<?php echo mysqli_get_host_info($con);?></li>
                <li>服务器版本：&nbsp;&nbsp;<?php echo mysqli_get_server_info($con)?></li>
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
    <div class="database-tree"><div class="database-tree-head">数据库树状图</div>
    <div class="database-tree-body">

    </div></div>
</div>
<script src="<?php echo $path?>lib/jquery.min.js"></script>
<script src="<?php echo $path?>lib/jquery-ui/jquery-ui.js"></script>
<script src="<?php echo $path?>lib/handsontable/handsontable.full.min.js"></script>
<script src="<?php echo $path?>lib/jquery/jquery.cookie.min.js"></script>
<script src="https://cdn.bootcss.com/jstree/3.3.5/jstree.min.js"></script>
<script>

    $(document).ready(function(){
        /* Other Start */
        $('#nav-home').addClass('active');
        /* Other End */

        /* Common Part Start */
        $(".head > a").click(function () {
            if ($(this).nextAll().length !== 0) {
                //不是最右边的关闭
                $('a.active').removeClass('active');
                $(this).toggleClass('active');
            } else {
                $('#msg').toggle();
                $('#block').toggle();
                $('.msg-body button:first-child').click(function () {
                    $.cookie('funnysql', '', {expires: -10, path: "<?php echo $path;?>"});
                    window.location.replace('<?php echo $domain . $path;?>index');
                });
            }
        });
        $('.msg-head a').click(function () {
            $('#msg').hide();
            $('#block').hide();
        });
        $('.msg-body button:last-child').click(function () {
            $('#msg').hide();
            $('#block').hide();
        });
        $('#msg').draggable();
        /* Common Part End */

        // language=JQuery-CSS
        $(".head > a").click(function () {
            if($(this).nextAll().length !== 0) {
                //不是最右边的关闭
                $('a.active').removeClass('active');
                $(this).toggleClass('active');
            } else {
                $('.msg').toggle();
                $('.msg-body button:first-child').click(function () {
                    $.cookie('funnysql', '', {expires: -10, path: "<?php echo $path;?>"});
                    window.location.replace('<?php echo $domain.$path;?>test');
                });
            }
        });
        $('.msg-head a').click(function () {
            $('.msg').hide();
        });
        $('.msg-body button:last-child').click(function () {
            $('.msg').hide();
        });
        $.ajax({
            url: '<?php echo $domain . $path; ?>lib/api/GetDatabasesTreeView.php',
            success: function (datae) {
                $(".database-tree-body").jstree({
                    "plugins" : [
                        "sort",
                    ],
                    'core': {
                        'data': JSON.parse(datae),
                    }
                });
            }
        });

    });
</script>
</body>
</html>
