<?php
include('/lib/settings.php');
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
    <title>数据表 - FunnySQL</title>
    <link rel="shortcut icon" href="<?php echo $path;?>res/favicon.png">
    <link rel="stylesheet" href="<?php echo $path?>lib/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" href="<?php echo $path?>lib/handsontable/handsontable.full.min.css">
    <link rel="stylesheet" href="<?php echo $path?>lib/css.css">
</head>
<style>
    .pageSkip {
        display: inline-block;
        background: transparent url('<?php echo $path.'res/arrow-left.png';?>') no-repeat -10px -10px;
        text-indent: -999em;
        background-size: 40px;
        opacity: 0.7;
        vertical-align: middle;
        width: 20px;
        height: 20px;
    }
    .pageNext {
        background-image: url('<?php echo $path.'res/arrow-right.png';?>');
    }
    .changPage {
        margin-top: 10px;
    }

</style>
<body>
<div id="msg"><span id="msg-body"></span><span id="msg-close" style="cursor: pointer;">X</span></div>
<div id="loader"></div>
<div id="fullScreen"></div>
<div id="close"><div class="close-head">确定离开？<a >X</a ></div>
    <div class="close-body">
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

<div class="more" id="more">&nbsp;&nbsp;更多操作&nbsp;&nbsp;</div>
<div class="more" id="more-info">
    <a href="<?php echo $path;?>new-delete-table" id="newDelete" class="subMore"> 新建/删除 </a>
    <a href="<?php echo $path;?>view-edit-table" id="viewEdit"  class="subMore"> 查看/编辑 </a>
</div>

<div class="block" id="popUpEdit" style="display: none;">
    <div class="block-head"><span id="editTitle">编辑数据</span><span id="editClose">X</span></div>
    <div class="block-body" id="popUpEdit-body"><div id="editTable"></div><button>确定</button><button>取消</button></div>
</div>
<div class="main">
    <div class="left">
        <div class="block">
            <div class="block-head">选择数据表</div>
            <div class="block-body">
                <select name="databaseName" id="databaseName" title="选择数据库" class="input">
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
                <select name="tableName" id="tableName" title="选择数据表" class="input">
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
        <div class="block" style="margin-top: 30px;">
            <div class="block-head">插入数据</div>
            <div class="block-body">
                <div id="insertDataTable"></div>
                <button id="insertAdd">添加一行</button>
                <button id="insertDelete">删除一行</button>
                <button id="insertClear">&nbsp;&nbsp;清空&nbsp;&nbsp;</button>
                <button id="insertSubmit">&nbsp;&nbsp;提交&nbsp;&nbsp;</button>
            </div>
        </div>
    </div>
    <div class="right">
        <div class="block">
            <div class="block-head" id="blockRight1-head"><?php echo $tb;?></div>
            <div class="block-body">
                <div class="dataTable" id="dataTable"></div>
                <div class="changPage">
                    <a href="javascript:void(0)" class="pageSkip" id="pagePrev"></a>

                    <input type="number" maxlength="10" title="Page" style="display: none; font-size: 16px; width: 80px; cursor: text" id="inputPage">
                    <label id="pageInfo" style="font-size: 16px ;width: 60px;cursor: pointer"></label>
                    <a href="javascript:void(0)" class="pageSkip pageNext" id="pageNext"></a>
                </div>
        </div>
    </div>
</div>


</div>
<script src="<?php echo $path?>lib/jquery.min.js"></script>
<script src="<?php echo $path?>lib/jquery-ui/jquery-ui.js"></script>
<script src="<?php echo $path?>lib/handsontable/handsontable.full.min.js"></script>
<script src="<?php echo $path?>lib/jquery/jquery.cookie.min.js"></script>
<script>

    $(document).ready(function() {
        /* Other Start */
        $('#nav-table').addClass('active');
        $('#more').hover(function () {
            $('#more').fadeOut('slow');
            $('#more-info').fadeIn('slow');
        });
        $('#more-info').mouseleave(function () {
            $('#more-info').fadeOut('slow');
            $('#more').fadeIn('slow');
        });
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
                $("#msg").css('background',color).addClass("msgShow").find('#msg-body').html(Message).parent('#msg').show();
                setTimeout(function(){
                    $("#msg").removeClass("msgShow").find('#msg-body').html('').parent('#msg').hide();
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
            $.ajax({
                url: '<?php echo $path;?>lib/Processing.php',
                method: 'post',
                data: {'type': '2'},
                success: function () {
                    window.location.href = '<?php echo $path;?>';
                }
            });
        });
        $('.close-head a, .close-body button:last-child').click(function () {
            $("#close, #fullScreen").hide();
        });
        $("#close").draggable();
        /* Common Part End */

        /* Left Block 1 Start */
        let totalPage = '1';
        let currentPage = '<?php echo $page;?>';
        let originalDb = '<?php echo $dba;?>';
        let originalTb = '<?php echo $tb;?>';
        let colHeaders = null;
        // 数据库选择改变
        $('#databaseName').change(function () {
            let db = $(this).val();
            reloadSelectTable(db);
            currentPage = 1;
            // reloadSelectTable 执行完后在获取第一个值
            setTimeout(function () {
                let tb = $('#tableName').val();
                loadDataTable(db,tb, 1);
            },500);
        });
        // 数据表选择改变
        $('#tableName').change(function () {
            let db = $('#databaseName').val();
            let tb = $(this).val();
            currentPage = 1;
            loadDataTable(db, tb, 1);
        });
        // 函数，刷新数据表选择下拉列表
        function reloadSelectTable(db) {
            if(db === undefined)
                db = '<?php echo $dba;?>';
            $.ajax({
                url: '<?php echo $domain.$path.'lib/Processing.php?type=6&db=';?>'+db,
                dataType: 'json',
                timeout: 3000,
                success: function (data) {
                    if(data.success) {
                        $('#tableName').html(data.msg);
                    }
                    else
                        showMsg(data.msg,'error');
                }
            })
        }
        /* Left Block 1 End */

        /* Left Block 2 Start */
        let insertDataTable = new Handsontable(document.getElementById('insertDataTable'),{
            fillHandle: false,
            stretchH: 'all',
            colHeaders: true,
            minRows: 10,
            height: 275,
            manualColumnResize: true,

        });
        // 添加一行
        $('#insertAdd').click(function () {
            insertDataTable.alter('insert_row', insertDataTable.countRows())
        });
        // 删除一行
        $("#insertDelete").click(function () {
            if(insertDataTable.countRows() === 10)
                showMsg('😁 不能再删除了！','error');
            else
                insertDataTable.alter('remove_row', insertDataTable.countRows() - 1);
        });
        // 清空结构表
        $("#insertClear").click(function () {
            insertDataTable.loadData([]);
        });
        $('#insertSubmit').click(function () {
            let temp = insertDataTable.getData();
            let data = [];
            for(let i = 0; i < insertDataTable.countRows(); i++) {
                let isValue = false;
                for(let j = 0; j < insertDataTable.countCols(); j++)
                    if(temp[i][j] !== '' && temp[i][j] !== null)
                        isValue = true;
                if(isValue)
                    data.push(temp[i]);
            }
            if(data.length === 0)
                showMsg('啥都没填，就别提交了！');
            else {
                $.ajax({
                    url : '<?php echo $path?>lib/Processing',
                    method: 'post',
                    dataType: 'json',
                    timeout: 3000,
                    data: {'type': '3','db': originalDb,'tb': originalTb,'data': data},
                    success: function (data) {
                        if(data.success) {
                            showMsg(data.msg,'success');
                            loadDataTable(originalDb,originalTb,1);
                            insertDataTable.loadData([]);
                        }
                        else
                            showMsg(data.msg);
                    }
                })
            }
        });
        /* Left Block 2 Start */

        /* Right Part Start */
        let dataTable = new Handsontable(document.getElementById('dataTable'),{
            fillHandle: false,
            stretchH: 'all',
            colHeaders: true,
            minRows: 30,
            maxRows: 30,
            height: 740,
            manualColumnResize: true,
            readOnly: true,
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
                beforeSend: function(){
                    showLoader();
                },
                complete: function() {
                    hideLoader();
                },
                success: function (data) {
                    if(data.success) {
                        colHeaders = data.colHeaders;
                        let columns = data.columns;
                        dataTable.updateSettings({
                            colHeaders: colHeaders,
                            columns: columns,
                        });
                        insertDataTable.updateSettings({
                            colHeaders: colHeaders.slice(2,),
                            columns: columns.slice(2,),
                        });
                        editTable.updateSettings({
                            colHeaders: colHeaders.slice(2,),
                            columns: columns.slice(2,),
                        });

                        dataTable.loadData(data.data);
                        currentPage = data.currentPage;
                        totalPage = data.totalPage;
                        originalDb = db;
                        originalTb = tb;
                        window.history.replaceState({},'','<?php echo $domain.$path.basename(__FILE__,'.php');?>?p='+currentPage+'&db='+db + '&tb='+tb);
                        $('#pageInfo').html(currentPage + '/' + totalPage);
                        $('#blockRight1-head').html(tb + "<img id=\"refresh\" src=\"<?php echo $path?>res/refresh.png\" title=\"刷新\"/>");
                        $('#refresh').click(function () {
                            loadDataTable(originalDb,originalTb, 1);
                        });
                        $('.deleteData').click(function () {
                            let row = dataTable.getDataAtRow($(this).attr('column'));
                            let i = 2;
                            let condition = '';
                            while(i < colHeaders.length) {
                                if(row[i] !== null && row[1] !== '') {
                                    condition += colHeaders[i] + "='" + row[i] + "' and ";
                                }
                                i ++;
                            }
                            $.ajax({
                                url: '<?php echo $domain.$path."lib/Processing.php?type=8&db=";?>'+db + '&tb=' + tb + '&p='+ page + '&condition='+ condition,
                                dataType : 'json',
                                timeout: 3000,
                                success: function (data) {
                                    if(data.success) {
                                        loadDataTable(db,tb,currentPage);
                                    }
                                    else
                                        showMsg(data.msg);
                                }
                            })
                        });


                        $('.editData').click(function () {
                            let selectRow = $(this).attr('row');
                            let load = [];
                            load.push(dataTable.getDataAtRow(parseInt(selectRow)).slice(2,));
                            editTable.loadData(load);
                            $('#fullScreen,#popUpEdit').show();
                        });

                    }
                    else
                        showMsg(data.msg);
                }
            });
        }


        let editTable = new Handsontable(document.getElementById('editTable'), {
            fillHandle: false,
            manualColumnResize: true,
            colHeaders: true,
            stretchH: 'all',
            maxRows: 1,
            height: 70,
        });

        // window resize 后会使得表格中的点击触发事件失效，只能重新加载表格，暂时没有找到其他的解决方法
        $( window ).resize(function () {
            loadDataTable(originalDb, originalTb, currentPage);
        });
        $("#editClose, #popUpEdit-body button:last-child").click(function () {
            $('#fullScreen,#popUpEdit').hide();
        });
        $("#popUpEdit").draggable();
        $("#fullScreen").click(function () {
            $('#fullScreen,#popUpEdit,#close').hide();
        });
        $('#pageInfo').click(function () {
            $('#inputPage').show().focus();
            $('#pageInfo').hide();
        });
        $('#inputPage').mouseout(function () {
            $(this).hide().val('');
            $('#pageInfo').show();
        }).keydown(function (even) {
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
        $(document).keydown(function (even) {
            if(even.keyCode === 37 || even.keyCode === 38) {
                if(parseInt(currentPage) === 1)
                    alert('已经是第一页了！');
                else
                    loadDataTable(originalDb,originalTb, parseInt(currentPage) - 1);
            } else if(even.keyCode === 39 || even.keyCode === 40) {
                if(parseInt(currentPage) >= parseInt(totalPage))
                    alert('已经是最后一页了！');
                else
                    loadDataTable(originalDb,originalTb, parseInt(currentPage) + 1);
            }
        });
        $('#pageNext').click(function () {
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