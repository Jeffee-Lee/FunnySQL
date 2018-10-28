<?php
include('./lib/settings.php');
if(!array_key_exists('funnysql', $_COOKIE))
    header("Location: ".$domain.$path."login");
$con_info = explode(',', $_COOKIE['funnysql']);
$con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);

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

$page = 1;
if(isset($_GET['p']) and !empty($_GET['p']))
    $page = $_GET['p'];
$con->close();
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
    <link href="<?php echo $domain.$path?>lib/handsontable/handsontable.full.min.css" rel="stylesheet" media="screen">
</head>
<style>
    /*Public*/
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
    }

    .left, .right {
        display: block;
        border: 1px solid #e7eaed;
        margin: 30px 20px 0 20px;
        border-radius: 10px;
    }
    .left {
        float: left;
        width: 34.33%;
    }
    .right {
        float: right;
        width: 59%;
    }
    .left-part1-head,.right-head {
        background: #bbbbbb;
        color: #ffffff;
        padding: 9px;
        font-size: 20px;
        border-radius: 10px 10px 0 0;
    }
    .left-part1-body,.right-body {
        padding: 18px 0;
        background: #f3f3f3;
        text-align: center;
    }
    .pageSkip {
        display: inline-block; background: transparent url('https://cdn3.iconfinder.com/data/icons/google-material-design-icons/48/ic_keyboard_arrow_left_48px-32.png') no-repeat -10px -10px;
        text-indent: -999em;
        background-size: 40px; opacity: 0.7;
        vertical-align: middle; width: 20px; height: 20px;
    }
    .pageNext {
        background-image: url('https://cdn3.iconfinder.com/data/icons/google-material-design-icons/48/ic_keyboard_arrow_right_48px-32.png');
    }

    td:hover {
        border: 2px solid #328fff;
    }
    .deleteData {
        color: red;
        text-decoration: none;
    }
    .deleteData:hover {
        color: #ff2f17;
    }
    .deleteData:active {
        color: #ff6957;
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
        <img src="" class="icon">&nbsp;数据库</a>
    <a href="#" class="tab-4">
        <img src="" class="icon ic_s_db">&nbsp;数据库</a>
    <a href="#" class="tab-5">
        <img src="" class="icon ic_s_db">&nbsp;数据库</a>
    <a href="#" class="tab-6">
        <img src="" class="icon ic_s_db">&nbsp;数据库</a>
    <a href="#" class="exit">X</a>
</div>
<div class="main">
    <div class="left">
        <div class="left-part1-head">选择数据表</div>
        <div class="left-part1-body">
            <select name="databaseName" id="databaseName" title="选择数据库">
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
            <select name="tableName" id="tableName" title="选择数据表">
                <?php
                foreach ($tables as $value){
                    $output = '<option value="' . $value . '" ';
                    if(!strcmp($value, $tb))
                        $output .= 'selected';
                    $output .= '>'.$value.'</option>';
                    echo $output;
                }
                ?>
            </select>
        </div>
    </div>
    <div class="right">
        <div class="right-head"><?php echo $tb;?></div>
        <div class="right-body">
            <div class="dataTable" id="dataTable"></div>
            <div class="changPage">
                <a href="javascript:void(0)" class="pageSkip" id="pagePrev"></a>

                <input type="number" maxlength="10" title="Page" style="display: none; font-size: 16px; width: 80px; cursor: text" id="inputPage">
                <label id="pageInfo" style="font-size: 16px ;width: 60px;cursor: pointer">文字</label>
                <a href="javascript:void(0)" class="pageSkip pageNext" id="pageNext"></a>
        </div>
    </div>
</div>
<script src="<?php echo $domain.$path?>lib/jquery.min.js"></script>
<script src="<?php echo $domain.$path?>lib/handsontable/handsontable.full.min.js"></script>
<script src="https://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script>

    $(document).ready(function() {

        /* Common Part Start */
        $(".head > a").click(function () {
            if ($(this).nextAll().length !== 0) {
                //不是最右边的关闭
                $('a.active').removeClass('active');
                $(this).toggleClass('active');
            } else {
                $('.msg').toggle();
                $('.msg-body button:first-child').click(function () {
                    $.cookie('funnysql', '', {expires: -10, path: "<?php echo $path;?>"});
                    window.location.replace('<?php echo $domain . $path;?>index');
                });
            }
        });
        $('.msg-head a').click(function () {
            $('.msg').hide();
        });
        $('.msg-body button:last-child').click(function () {
            $('.msg').hide();
        });
        /* Common Part End */

        /* Left Part1 Start */
        // 数据库选择改变
        $('#databaseName').change(function () {
            let db = $(this).val();
            reloadSelectTable(db);
            window.history.pushState({},0,'<?php echo $domain.$path;?>temp?db='+db);
            currentPage = 1;
            // reloadSelectTable 执行完后在获取第一个值
            setTimeout(function () {
                let tb = $('#tableName').val();
                loadDataTable(db,tb);
                $('.right-head').html(tb);
            },500);

        });

        let totalPage = '1';
        let currentPage = <?php echo $page;?>;
        let originalDb = '<?php echo $dba;?>';
        let originalTb = '<?php echo $tb;?>';
        let colHeaders = null;
        // 数据表选择改变
        $('#tableName').change(function () {
            let db = $('#databaseName').val();
            let tb = $(this).val();
            window.history.pushState({},0,'<?php echo $domain.$path;?>temp?db='+db+'&tb='+tb);
            currentPage = 1;
            $('.right-head').html(tb);
            loadDataTable(db, tb);
        });
        // 函数，刷新数据表选择下拉列表
        function reloadSelectTable(db) {
            if(db === undefined)
                db = '<?php echo $dba;?>';
            $.ajax({
                url: '<?php echo $domain.$path.'lib/Processing.php?type=6&db=';?>'+db,
                dataType: 'json',
                success: function (data) {
                    if(data.success) {
                        $('#tableName').html(data.msg);
                    }
                    else
                        alert(data.msg);
                }
            })
        }
        /* Left Part1 End */

        /* Right Part Start */
        let dataTable = new Handsontable(document.getElementById('dataTable'),{
            fillHandle: false,
            stretchH: 'all',
            readOnly: true,
            colHeaders: true,
            minRows: 20,
            maxRows: 20,
            height: 650,
            rowHeights: 30,
            manualColumnResize: true,
            width: 1120,
            disableVisualSelection: true,
        });
        loadDataTable(originalDb,originalTb, currentPage);
        function loadDataTable(db, tb, page) {
            if(db === undefined)
                db = '<?php echo $dba;?>';
            if(tb === undefined)
                tb = '<?php echo $tb;?>';
            if(page === undefined)
                page = '<?php echo $page;?>';
            $.ajax({
                url: '<?php echo $domain.$path."lib/Processing.php?type=7&db=";?>'+db + '&tb=' + tb + '&p='+ page,
                dataType: 'json',
                success: function (data) {
                    if(data.success) {
                        colHeaders = data.colHeaders;
                        let columns = data.columns;
                        dataTable.updateSettings({
                            colHeaders: colHeaders,
                            columns: columns,
                        });
                        dataTable.loadData(data.data);
                        currentPage = data.currentPage;
                        totalPage = data.totalPage;
                        originalDb = db;
                        originalTb = tb;
                        console.log(db);
                        console.log(tb);
                        $('#pageInfo').html(currentPage + '/' + totalPage);
                        $('.deleteData').click(function () {
                            let row = dataTable.getDataAtRow($(this).attr('column'));
                            let i = 2;
                            let condition = '';
                            while(i < colHeaders.length) {
                                condition += colHeaders[i] + "='" + row[i] + "'";
                                if(i !== colHeaders.length - 1)
                                    condition += ' and ';
                                i ++;
                            }
                            $.ajax({
                                url: '<?php echo $domain.$path."lib/Processing.php?type=8&db=";?>'+db + '&tb=' + tb + '&p='+ page + '&condition='+ condition,
                                dataType : 'json',
                                success: function (data) {
                                    if(data.success) {
                                        loadDataTable(db,tb,currentPage);
                                    }
                                    else
                                        alert(data.msg);
                                }
                            })
                        });
                    }
                    else
                        alert(data.msg);
                }
            });
        }
        $('#pageInfo').click(function () {
            $('#inputPage').show().focus();
            $('#pageInfo').hide();
        });
        $('#inputPage').mouseout(function () {
            $('#inputPage').hide().val('');
            $('#pageInfo').show();
        });
        $('#inputPage').keydown(function (even) {
            console.log(even.keyCode);
            if(even.keyCode === 13) {
                let inputPage = $(this).val();
                if(inputPage > totalPage || inputPage <= 0)
                    alert('超出范围！');
                else {
                    loadDataTable(originalDb,originalTb,inputPage);
                    $('#inputPage').hide();
                    $('#pageInfo').show();
                }
            }
        });
        $('#pagePrev').click(function () {
           if(parseInt(currentPage) === 1)
               alert('已经是第一页了！');
           else
               loadDataTable(originalDb,originalTb, parseInt(currentPage) - 1);
        });
        $('#pageNext').click(function () {
            console.log(totalPage);
            if(parseInt(currentPage) >= parseInt(totalPage))
                alert('已经是最后一页了！');
            else
                loadDataTable(originalDb,originalTb, parseInt(currentPage) + 1);
        });

        /* Right Part end */
    });
</script>
</body>
</html>