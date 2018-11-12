<?php
include('./lib/settings.php');
if(!array_key_exists('session', $_COOKIE))
    header("Location: ./login");
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
    <title><?php echo $PAGE_TITLE_DATABASE;?></title>
    <link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
    <link rel="stylesheet" href=./lib/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" href="./lib/handsontable/handsontable.full.min.css">
    <link rel="stylesheet" href="./lib/css.css">

</head>
<style>
    .input {
        font-size: 16px;
        width: 45%;
        margin: 8px;;
        height: 25px;
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
    <a href="./" id="nav-home">
        <img src="./res/mysql.png"  class="icon home icon-inactive">
        <img src="./res/mysql_active.png"  class="icon home icon-active">
        &nbsp;概述
    </a>
    <a href="./database" id="nav-database" class="active">
        <img src="./res/database_active.png"  class="icon database">
        &nbsp;数据库
    </a>
    <a href="./new-delete-table" id="nav-table">
        <img src="./res/table.png"  class="icon table icon-inactive">
        <img src="./res/table_active.png"  class="icon table icon-active">
        &nbsp;数据表
    </a>
    <a href="./sql" id="nav-sql">
        <img src="./res/sql.png"  class="icon sql icon-inactive" >
        <img src="./res/sql_active.png"  class="icon sql icon-active">
        &nbsp;SQL
    </a>
    <a href="javascript:void(0)" id="exit">X</a>
</div>

<div class="main">

    <div class="left">
        <div class="block">
            <div class="block-head">新建数据库</div>
            <div class="block-body">
                <form class="left-form" onsubmit="return false;">
                    <input type="text" name="newDatabaseName"  class="input" id="newDatabaseName" title="数据库名" required placeholder="数据库名">
                    <select name="db_collation" title="数据库编码" class="input">
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
                    <div>
                        <button id="submitCreate" style="margin-bottom: 5px">&nbsp;&nbsp;创建&nbsp;&nbsp;</button>
                    </div>

                </form>
            </div>
        </div>
        <div class="block" style="margin-top: 30px">
            <div class="block-head">修改数据库编码</div>
            <div class="block-body">
                <select name="dbList" title="数据库名" class="input dbList"></select>
                <select name="change_db_collation" class="input" title="新的数据库编码">
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
                <div>
                    <button id="submitChange">&nbsp;&nbsp;修改&nbsp;&nbsp;</button>
                </div>
            </div>
        </div>
        <div class="block" style="margin-top: 30px;">
            <div class="block-head">备份数据库</div>
            <div class="block-body">
                <select name="backup_db" title="数据库名" class="input dbList" ></select>
                <button id="backup">&nbsp;&nbsp;备份&nbsp;&nbsp;</button>
            </div>
        </div>
    </div>
    <div class="right" >
        <div class="block">
            <div class=" block-head">删除数据库<img id="refresh" src="./res/refresh.png" width="16px" height="16px" title="刷新"/></div>
            <div class="block-body">
                <div id="databaseTable"></div>
            </div>
        </div>


    </div>
<script src="./lib/jquery.min.js"></script>
<script src="./lib/jquery-ui/jquery-ui.js"></script>
<script src="./lib/jquery/jquery.cookie.min.js"></script>
<script src="./lib/handsontable/handsontable.full.min.js"></script>
<script src="./lib/js.js"></script>

<script>

    $(document).ready(function(){
        /* Common Part Start */
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
        /* Common Part End */


        // 创建按钮点击
        $('#submitCreate').click(function () {
            let name = $('input[name="newDatabaseName"]').val();
            let charset = $('select[name="db_collation"]').val();
            if(name !== '') {
                $.ajax({
                    url: './lib/Processing.php?type=1&databaseName=' + name + '&collationName=' + charset,
                    dataType: 'json',
                    timeout: 3000,
                    success: function (data) {
                        if(data.success) {
                            showMsg(data.msg,'success');
                            loadDatabases();
                        } else
                            showMsg(data.msg, 'error');
                    },
                    error: function () {
                        showMsg('连接错误,请稍后重试!','error')
                    }
                });
            }
        });

        $("#submitChange").click(function () {
           let db =  $("select[name='dbList']").val();
           let collation = $('select[name="change_db_collation"]').val();
           $.ajax({
               url: './lib/Processing.php?type=11&db=' + db + '&collation=' + collation,
               dataType: 'json',
               timeout: 3000,
               success: function (data) {
                   if(data.success) {
                       showMsg(data.msg,'success');
                       loadDatabases();
                   } else {
                       showMsg(data.msg);
                   }
               },
               error: function () {
                   showMsg('连接错误,请稍后重试!','error')
               }
           })
        });
        $("#backup").click(function () {
            let db = $("select[name='backup_db']").val();
            window.location = './lib/api/backupDb.php?db=' + db;
        });
        let hot = new Handsontable(document.getElementById('databaseTable'), {
            fillHandle: false,
            stretchH: 'all',
            colHeaders: ['', '数据库名', '数据库编码'],
            rowHeights: 30,
            columnSorting: {
                indicator: true
            },
            disableVisualSelection: true,
            columns: [
                {
                    width: 30,
                    className: 'htCenter htMiddle',
                    renderer: 'html'
                },
                {
                    readOnly: true,
                    className: 'htCenter htMiddle',
                    renderer: 'html'
                },{
                    readOnly: true,
                    className: 'htCenter htMiddle',
                }
            ]
        });
        function loadDbSelect(dbList){
            $(".dbList").html(dbList);
        }
        function deleteDbClick() {
            $('.deleteDatabase').unbind("click").click(function () {
                let db = $(this).attr('db');
                if(confirm('确定删除数据库'+ db + '吗？'))
                    $.ajax({
                        url: './lib/Processing.php?type=2&db=' + db,
                        dataType: 'json',
                        timeout: 3000,
                        success: function (data) {
                            if(data.success) {
                                showMsg(data.msg,'success');
                                loadDatabases();
                            } else
                                showMsg(data.msg, 'error');
                        },
                        error: function () {
                            showMsg('连接错误,请稍后重试!','error');
                        }
                    });
            });
        }
        function loadDatabases(){
            $.ajax({
                url: './lib/Processing.php?type=9',
                dataType: 'json',
                timeout: 3000,
                beforeSend: function() {
                  showLoader();
                },
                success: function (data) {
                    if(data.success) {
                        hot.loadData(data.data);
                        loadDbSelect(data.dbList);
                    }
                    else
                        showMsg(data.msg,'error');
                },
                error: function() {
                    showMsg('连接错误,请稍后重试!','error');
                },
                complete: function() {
                    hideLoader();
                },
            })
        }
        loadDatabases();
        $('#refresh').click(function () {
            loadDatabases();
        });
        setInterval(function () {
            deleteDbClick();
        },1000);
    });
</script>
</body>
</html>
