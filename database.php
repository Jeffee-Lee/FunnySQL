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
    <title>数据库 - FunnySQL</title>
    <link rel="shortcut icon" href="<?php echo $path;?>res/favicon.png">
    <link rel="stylesheet" href="<?php echo $path?>lib/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" href="<?php echo $path?>lib/handsontable/handsontable.full.min.css">
    <link rel="stylesheet" href="<?php echo $path?>lib/css.css">

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
                    <br>
                    <button id="submitCreate" style="margin-bottom: 5px">&nbsp;&nbsp;创建&nbsp;&nbsp;</button>
                </form>
            </div>
        </div>
        <div class="block" style="margin-top: 30px">
            <div class="block-head">修改数据库编码</div>
            <div class="block-body"></div>
        </div>
    </div>
    <div class="right" >
        <div class="block">
            <div class=" block-head">删除数据库<img id="refresh" src="<?php echo $path?>res/refresh.png" width="16px" height="16px" title="刷新"/></div>
            <div class="block-body">
                <div id="databaseTable"></div>
            </div>
        </div>


</div>
<script src="<?php echo $path?>lib/jquery.min.js"></script>
<script src="<?php echo $path?>lib/jquery-ui/jquery-ui.js"></script>
<script src="<?php echo $path?>lib/handsontable/handsontable.full.min.js"></script>
<script src="<?php echo $path?>lib/jquery/jquery.cookie.min.js"></script>


<script>

    $(document).ready(function(){
        /* Other Start */
        $('#nav-database').addClass('active');
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


        // 创建按钮点击
        $('#submitCreate').click(function () {
            let name = $('input[name="newDatabaseName"]').val();
            let charset = $('select[name="db_collation"]').val();
            if(name !== '') {
                $.ajax({
                    url: '<?php echo $domain.$path;?>lib/Processing.php?type=1&databaseName=' + name + '&collationName=' + charset,
                    dataType: 'json',
                    timeout: 3000,
                    success: function (data) {
                        if(data.success) {
                            showMsg(data.msg,'success');
                            loadDatabases();
                        }
                        else
                            showMsg(data.msg, 'error');
                    },
                    error: function () {
                        showMsg('连接错误,请稍后重试!','error')

                    }
                });
            }
        });

        let container = document.getElementById('databaseTable');
        let hot = new Handsontable(container, {
            fillHandle: false,
            stretchH: 'all',
            colHeaders: ['', '数据库名', '数据库编码'],
            rowHeights: 30,
            columnSorting: {
                indicator: true
            },
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
        function loadDatabases(){
            $.ajax({
                url: '<?php echo $path."lib/Processing.php?type=9";?>',
                dataType: 'json',
                timeout: 3000,
                beforeSend: function() {
                  showLoader();
                },
                success: function (data) {
                    if(data.success) {
                        hot.loadData(data.data);
                        $('.deleteDatabase').click(function () {
                            let db = $(this).attr('db');
                            if(confirm('确定删除数据库'+ db + '吗？'))
                                $.ajax({
                                   url: '<?php echo $path."lib/Processing.php?type=2&db=";?>' + db,
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
    });
</script>
</body>
</html>
