<?php
include('./lib/settings.php');
if(!array_key_exists('funnysql', $_COOKIE))
    header("Location: http://10.242.8.182/funnysql/login");
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
    <script src="https://cdn.bootcss.com/handsontable/6.0.1/handsontable.min.js"></script>
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
        height: 300px;
    }
    .new-database, .database-table {
        display: block;
        border: 1px solid #e7eaed;
        margin: 30px 20px;
        border-radius: 10px;
    }
    .new-database {
        float: left;
        width: 50%;
    }
    .database-table {
        float: right;
        width: 40%;
    }
    .new-database-head, .table-head{
        background: #bbbbbb;
        color: #ffffff;
        padding: 9px;
        font-size: 20px;
        border-radius: 10px 10px 0 0;
    }
    .new-database-body, .table-body{
        padding: 18px 0;
        background: #f3f3f3;
    }
    .new-database-form {
        padding: 30px 50px;
    }
    input, select {
        font-size: 17px;
        line-height: 20px;
    }
    .btn {
        width: 100%;
        text-align: center;
    }
    #submit-change {
        margin: 20px;
        text-align: center;
    }
    .database-table {
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;
    }
</style>
<body style="background: white;">
<div class="msg"><div class="msg-head">确定离开？<a >X</a ></div><div class="msg-body">
        <button>确定</button>
        <button>取消</button></div></div>
<div class="head">
    <a href="<?php echo $domain.$path?>" class='tab-0'>
        <img src="<?php echo $domain.$path?>res/mysql.png"  class="icon mysql" width="16px" height="16px">&nbsp;概述</a>
    <a href="mysql_database.php" class="tab-1 active">
        <img src="<?php echo $domain.$path?>res/database.png"  class="icon database" width="16px" height="16px">&nbsp;数据库</a>
    <a href="mysql_table.php" class="tab-2">
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
    <div class="new-database"><div class="new-database-head">新建数据库</div><div class="new-database-body">
            <form class="new-database-form" onsubmit="return false;">
                <input type="text" name="newDatabaseName" id="newDatabaseName" title="数据库名" required placeholder="数据库名">
                <select name="db_collation" title="数据库编码" style="width: 250px; margin-left: 12px">
                    <optgroup label="gbk" title="GBK Simplified Chinese">
                        <option value="gbk_bin" title="简体中文, 二进制">gbk_bin</option>
                        <option value="gbk_chinese_ci" title="简体中文, 不区分大小写">gbk_chinese_ci</option>
                    </optgroup>
                    <optgroup label="utf8" title="UTF-8 Unicode">
                        <option value="utf8_bin" title="Unicode, 二进制">utf8_bin</option>
                        <option value="utf8_general_ci" title="Unicode, 不区分大小写">utf8_general_ci</option>
                        <option value="utf8_general_mysql500_ci" title="Unicode (MySQL 5.0.0), 不区分大小写">utf8_general_mysql500_ci</option>
                        <option value="utf8_unicode_520_ci" title="Unicode (UCA 5.2.0), 不区分大小写">utf8_unicode_520_ci</option>
                        <option value="utf8_unicode_ci" title="Unicode, 不区分大小写" selected>utf8_unicode_ci</option>
                    </optgroup>
                    <optgroup label="utf8mb4" title="UTF-8 Unicode">
                        <option value="utf8mb4_bin" title="Unicode (UCA 4.0.0), 二进制">utf8mb4_bin</option>
                        <option value="utf8mb4_general_ci" title="Unicode (UCA 4.0.0), 不区分大小写">utf8mb4_general_ci</option>
                        <option value="utf8mb4_unicode_520_ci" title="Unicode (UCA 5.2.0), 不区分大小写">utf8mb4_unicode_520_ci</option>
                        <option value="utf8mb4_unicode_ci" title="Unicode (UCA 4.0.0), 不区分大小写">utf8mb4_unicode_ci</option>
                    </optgroup>
                </select>
                <button class="new-database-submit" style="margin-left: 12px;">创建</button>
            </form>
        </div></div>
    <div class="database-table" >
        <div class="table-head">删除数据库</div>
        <div class="table-body">
            <div id="fortest"></div>
            <div class="btn"><button id="submit-change">提交</button>
                <button id="refresh">刷新</button></div>
        </div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>


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

        // 创建按钮点击
        $('.new-database-submit').click(function () {
            let name = $('input[name="newDatabaseName"]').val();
            let charset = $('select[name="db_collation"]').val();
            if(name !== '') {
                $.ajax({
                    url: '<?php echo $domain.$path;?>lib/Processing.php?type=1&databaseName=' + name + '&collationName=' + charset,
                    dataType: 'json',
                    success: function (data) {
                        if(data.success) {
                            alert(data.msg);
                            loadDatabases();
                        }
                        else
                            alert(data.msg);
                    },
                    error: function () {
                        alert('发生未知错误！');
                    }
                });
            }
        });

        let container = document.getElementById('fortest');
        let hot = new Handsontable(container, {
            fillHandle: false,
            stretchH: 'all',
            colHeaders: ['<b style="color: red">删除</b>', '数据库名', '数据库编码'],
            columnSorting: {
                indicator: true
            },
            columns: [
                {
                    type: 'checkbox',
                    width: 30,
                    className: 'htCenter',
                },
                {
                    readOnly: true,
                    className: 'htCenter',
                },{
                    readOnly: true,
                    className: 'htCenter',
                }
            ]
        });
        function loadDatabases(){
            $.ajax({
                url: '<?php echo $domain.$path;?>lib/api/GetDatabases.php',
                dataType: 'json',
                success: function (data) {
                    data = data['data'];
                    hot.loadData(data);
                    hot.updateSettings({
                        cells: function (row, col) {
                            const no_editable = ['information_schema', 'mysql', 'performance_schema', 'sys'];
                            let cellProperties = {};
                            if(no_editable.indexOf(hot.getData()[row][col + 1]) !== -1)
                            {
                                cellProperties.readOnly = true;
                                hot.getCell(row,col).style.backgroundColor = "#EEE";
                            }
                            return cellProperties;
                        }
                    });
                }
            });
        }
        loadDatabases();
        $('#submit-change').click(function () {
            let del = [];
            for(let i = 0; i < hot.countRows(); i ++) {
                if(hot.getData()[i][0]) {
                    del.push(hot.getData()[i][1]);
                }
            }
            if(del.length !== 0){
                let r = confirm('确定删除数据库 ' + del + ' ?');
                if(r === true) {
                    $.ajax({
                        url: '<?php echo $domain.$path;?>lib/Processing.php?type=2&databaseNamesList=' + del,
                        dataType: 'json',
                        success: function (data) {
                            let msg = '';
                            data.forEach(function (element) {
                                msg += element.msg + '\n';
                            });
                            alert(msg);
                            loadDatabases();
                        }
                    });
                }
            }
        });
        $('#refresh').click(function () {
            loadDatabases();
        });
    });
</script>
</body>
</html>
