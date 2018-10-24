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
    <title>FunnySQL</title>
    <link rel="shortcut icon" href="<?php echo $domain.$path;?>res/favicon.png">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link href="https://cdn.bootcss.com/jstree/3.3.5/themes/default/style.min.css" rel="stylesheet">
</head>
<style>
    * {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }
    .head {
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 99;
        border-top: 10px solid #E7EAED;
        border-left: 8px solid #E7EAED;
        background: #E7EAED;
    }
    .head a {
        float: left;
        text-decoration: none;
        color: #235a81;
        padding: .6em;
        border-radius: 12px 12px 0 0;
    }
    .head > a > img {
        margin-right: .5em;
        vertical-align: -3px;
    }
    .head a:hover{
        background: -webkit-gradient(linear, left top, left bottom, from(#ffffff), to(#e5e5e5));
    }
    .head .active {
        background: white;
        border-bottom-color: white;
        color: black;
    }
    .head .active:hover {
        background: white;
    }
    .head a:last-child {
        float: right;
        border: none;
        -webkit-border-radius: unset;
        -moz-border-radius: unset;
        border-radius: unset;
    }
    .head a:last-child:hover {
        background: red;
        color: white;
    }
    .head a:last-child:active {
        background: #ff6f6d;
    }
    .msg {
        position: fixed;
        top: 60px;
        left: 45%;
        width: 200px;
        border: 1px solid #a4a4a4;
        background: whitesmoke;
        display: none;
    }
    .msg-head {
        cursor: default;
        padding: 6px 7px;
        background: -webkit-linear-gradient(top, #ffffff, #dcdcdc);
        font-size: 16px;
    }
    .msg-head a {
        float: right;
        margin: -7px;
        padding: 7px;
        color: #4b4b4b;
    }
    .msg-head a:hover {
        background: red;
        color: white;
    }
    .msg-head a:active {
        background: #ff6f6d;
    }
    .msg-body {
        margin: 10px 7px;
        text-align: center;
    }
    .msg-body button:first-child {
        margin-right: 30px;
    }

    .main {
        margin-top: 60px;
        width: 100%;
        height: 300px;
    }
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
<div class="msg"><div class="msg-head">确定离开？<a >X</a ></div><div class="msg-body">
        <button>确定</button>
        <button>取消</button></div></div>
<div class="head">
    <a href="<?php echo $domain.$path?>" class='tab-0 active'>
        <img src="<?php echo $domain.$path?>res/mysql.png"  class="icon mysql" width="16px" height="16px">&nbsp;概述</a>
    <a href="mysql_database.php" class="tab-1">
        <img src="<?php echo $domain.$path?>res/database.png"  class="icon database" width="16px" height="16px">&nbsp;数据库</a>
    <a href="#" class="tab-2">
        <img src="http://10.242.8.182/phpMyAdmin/themes/pmahomme/img/s_db.png"  class="icon table">&nbsp;数据表
    </a>
    <a href="#" class="tab-3">
        <img src="http://10.242.8.182/phpMyAdmin/themes/pmahomme/img/s_db.png" class="icon">&nbsp;数据库</a>
    <a href="#" class="tab-4">
        <img src="http://10.242.8.182/phpMyAdmin/themes/pmahomme/img/s_db.png" class="icon ic_s_db">&nbsp;数据库</a>
    <a href="#" class="tab-5">
        <img src="http://10.242.8.182/phpMyAdmin/themes/pmahomme/img/s_db.png" class="icon ic_s_db">&nbsp;数据库</a>
    <a href="#" class="tab-6">
        <img src="http://10.242.8.182/phpMyAdmin/themes/pmahomme/img/s_db.png" class="icon ic_s_db">&nbsp;数据库</a>
    <a href="#" class="exit">X</a>
</div>
<div class="main">
    <div class="info">
        <div class="info-head">数据库服务器</div>
        <div class="info-body">
            <ul>
                <li>服务器：&nbsp;&nbsp;<?php echo mysqli_get_host_info($con);?></li>

                <li>服务器版本：&nbsp;&nbsp;<?php echo mysqli_get_server_info($con)?></li>
                <li>服务器字符集：&nbsp;&nbsp;<?php echo strtoupper($con->get_charset()->{'charset'});?></li>
                <li>协议版本：&nbsp;&nbsp;<?php echo $con->protocol_version;?></li>
                <li>当前用户：&nbsp;&nbsp;<?php
                    if($result = $con->query('SELECT USER()')) {
                        echo mysqli_fetch_array($result)['USER()'];
                    }
                    ?></li>
            </ul></div>
    </div>
    <div class="database-tree"><div class="database-tree-head">数据库树状图</div>
    <div class="database-tree-body">

    </div></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="https://cdn.bootcss.com/jstree/3.3.5/jstree.min.js"></script>

<script>

    $(document).ready(function(){

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
