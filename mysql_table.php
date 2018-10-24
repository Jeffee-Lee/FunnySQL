<?php
include('./lib/settings.php');
if(!array_key_exists('funnysql', $_COOKIE))
    header("Location: http://10.242.8.182/funnysql/login");
$con_info = explode(',', $_COOKIE['funnysql']);
$con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
$dba = null;
if(isset($_GET['dba']) and !empty($_GET['dba']))
    $dba = $_GET['dba'];
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

    <link href="https://cdn.bootcss.com/handsontable/6.0.1/handsontable.css" rel="stylesheet" media="screen">

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
        padding-left: 10px;
    }
    .select {
        float: left;
        padding: 50px;
        width: 40%;
        height: 300px;
    }
    .create-table {
        clear: both;
        float: left;
        width: 40%;
        padding: 50px;
    }
</style>
<body style="background: white;">
<div class="msg"><div class="msg-head">确定离开？<a >X</a ></div><div class="msg-body">
        <button>确定</button>
        <button>取消</button></div></div>
<div class="head">
    <a href="<?php echo $domain.$path?>" class='tab-0'>
        <img src="<?php echo $domain.$path?>res/mysql.png"  class="icon mysql" width="16px" height="16px">&nbsp;概述</a>
    <a href="mysql_database.php" class="tab-1">
        <img src="<?php echo $domain.$path?>res/database.png"  class="icon database" width="16px" height="16px">&nbsp;数据库</a>
    <a href="mysql_table.php" class="tab-2 active">
        <img src="<?php echo $domain.$path?>res/table.png"  class="icon table" width="16px" height="16px">&nbsp;数据表
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
    <div class="select">
        <h3>新建数据表</h3>
        <select name="select-database" id="select-database" title="选择数据库">
            <?php
            $result = $con->query('SHOW DATABASES;');
            $databases = array();
            while($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['Database'] . '" > '.$row['Database'].'</option>';
                array_push($databases, $row['Database']);
            }
            $result->free_result();
            ?>
        </select>
        <input type="text" placeholder="新建数据表名" id="tableName"></div>
        <div class="create-table">
            <div id="create"></div>
            <div class="btn" style="text-align: center; padding-top: 30px;">
                <button class="subBtn" id="addRow">添加一行</button>
                <button class="subBtn" id="removeRow">删除最后一行</button>
                <button class="subBtn" id="push">提交</button>
            </div>
        </div>


</div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
    <script src="https://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script src="https://cdn.bootcss.com/handsontable/6.0.1/handsontable.full.min.js"></script>

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

            $('#tableName').blur(function () {
                if($(this).val() === '')
                    alert('请输入新建的数据表名！');
                console.log($(this).val());
            });
            let container = document.getElementById('create');
            let hot = new Handsontable(container, {
                data: [['','','',false,false]],
                fillHandle: false,
                stretchH: 'all',
                colHeaders: ['名', '类型', '长度', '不是 null', '主键'],
                columns: [
                    {
                        type: 'text',
                        className: 'htRight',
                    },
                    {
                        type: 'autocomplete',
                        source: ['BMW', 'Chrysler', 'Nissan', 'Suzuki', 'Toyota', 'Volvo'],
                        strict: false,
                        width: 50,
                        className: 'htRight',
                    },
                    {
                        type:  'numeric',
                        width: 50,
                        className: 'htRight',
                    },
                    {
                        type: 'checkbox',
                        width: 50,
                        className: 'htCenter',

                    },
                    {
                        type: 'checkbox',
                        width: 50,
                        className: 'htCenter',
                    }
                ]
            });
            $('#addRow').click(function () {
                hot.alter('insert_row', hot.countRows());
            });
            $('#removeRow').click(function () {
                hot.alter('remove_row', hot.countRows() - 1);
            });
            $('#push').click(function () {
                console.log(hot.getData()[0]);
            });
        });
    </script>
</body>
</html>
<?php $con->close();?>