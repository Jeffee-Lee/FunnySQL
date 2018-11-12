<?php
include ('./lib/settings.php');
if(!array_key_exists('session', $_COOKIE))
    header("Location: ./login.php");
$con_info = json_decode(base64_decode($_COOKIE['session']));
$con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

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

// 连接的数据表
$tb = null;
if(isset($_GET['tb']) and !empty($_GET['tb']))
    $tb = $_GET['tb'];
?>
<!doctype html>
<html style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $PAGE_TITLE_TABLE;?></title>
    <link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
    <link rel="stylesheet" href="./lib/jquery-ui/jquery-ui.min.css">
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
        <a href="./database.php" id="nav-database">
            <img src="./res/database.png"  class="icon database icon-inactive" >
            <img src="./res/database_active.png"  class="icon database icon-active">
            &nbsp;数据库
        </a>
        <a href="./new-delete-table.php" id="nav-table" class="active">
            <img src="./res/table_active.png"  class="icon table">
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
                <div class="block-head">新建数据表</div>
                <div class="block-body">
                    <div class="select">
                        <select name="select-database" id="left-top-select-database" class="select-database input" title="选择数据库">
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
                        <input type="text" placeholder="新建数据表名" id="tableName" class="input" >
                    </div>
                    <div class="create-table" style="padding-top: 30px;">
                        <div id="create"></div>
                        <div class="btn" style="text-align: center; padding-top: 15px;">
                            <button class="subBtn" id="addRow">添加一行</button>
                            <button class="subBtn" id="removeRow">删除一行</button>
                            <button class="subBtn" id="clearData">&nbsp;&nbsp;清空&nbsp;&nbsp;</button>
                            <button class="subBtn" id="push">&nbsp;&nbsp;创建&nbsp;&nbsp;</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
        <div class="right">
            <div class="block">
                <div class="block-head" id="blockRight1"><?php echo $dba?><img id="refresh" src="./res/refresh.png" width="16px" height="16px" title="刷新"/></div>
                <div class="block-body" >
                    <div id="showDetail"></div>
                </div>
            </div>
        </div>


    </div>

    <div class="more" id="more">&nbsp;&nbsp;更多操作&nbsp;&nbsp;</div>
    <div class="more" id="more-info">
        <a href="./new-delete-table.php" id="newDelete" class="subMore"> 新建/删除 </a>
        <a href="./view-edit-table.php" id="viewEdit"  class="subMore"> 查看/编辑 </a>
    </div>
    <script src="./lib/jquery.min.js"></script>
    <script src="./lib/jquery-ui/jquery-ui.js"></script>
    <script src="./lib/jquery/jquery.cookie.min.js"></script>
    <script src="./lib/handsontable/handsontable.full.min.js"></script>
    <script src="./lib/js.js"></script>
    <script>

        $(document).ready(function(){
            /* Other Start */
            $("#more").hover(function () {
                $("#more").fadeOut('slow');
                $("#more-info").fadeIn('slow');
            });
            $("#more-info").mouseleave(function () {
                $("#more-info").fadeOut('slow');
                $("#more").fadeIn('slow');
            });
            /* Other End */


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

            /* Left Part Start */
            let originalDb = '<?php echo $dba;?>';
            // 选择数据库改变
            $('.select-database').change(function () {
                let db = $(this).val();
                loadDetailTable(db);
                originalDb = db;
                window.history.replaceState({},'','./new-delete-table.php?db='+db);

            });
            // 创建结构表
            let createTable = new Handsontable(document.getElementById('create'), {
                fillHandle: false,
                stretchH: 'all',
                colHeaders: ['名', '类型', '长度', 'No Null', '主键'],
                minRows: 16,
                height: 400,
                columns: [
                    {
                        type: 'text',
                        className: 'htRight',
                        width: 40,
                    },
                    {
                        editor: 'select',
                        selectOptions:  ['TINYINT','SMALLINT','MEDIUMINT','INT','BIGINT','FLOAT','DOUBLE','DECIMAL',
                            'CHAR','VARCHAR','TINYBLOB','TINYTEXT','BLOB','TEXT','MEDIUMBLOB','MEDIUMTEXT','LONGBLOB','LONGTEXT',
                            'DATE','TIME','YEAR','DATETIME','TIMESTAMP'],
                        width: 40,
                        className: 'htRight',
                    },
                    {
                        type:  'numeric',
                        width: 40,
                        className: 'htRight',
                    },
                    {
                        type: 'checkbox',
                        width: 30,
                        className: 'htCenter',

                    },
                    {
                        type: 'checkbox',
                        width: 30,
                        className: 'htCenter',
                    }
                ]
            });
            // 添加一行
            $("#addRow").click(function () {
                createTable.alter('insert_row', createTable.countRows());
            });
            // 删除一行
            $("#removeRow").click(function () {
                if(createTable.countRows() === 16)
                    showMsg('😁 不能再删除了！','error');
                else
                    createTable.alter('remove_row', createTable.countRows() - 1);
            });
            // 清空结构表
            $("#clearData").click(function () {
                createTable.loadData(['','','',false,false]);
            });
            // 开始创建
            $("#push").click(function () {
                let db = $("#left-top-select-database").val();
                let tb = $("#tableName").val();
                if(tb === null || tb === '')
                    showMsg('请输入新建数据表名！','error');
                else {
                    let temp = createTable.getData();
                    let data = [];
                    for(let i = 0; i < createTable.countRows(); i++)
                        if(temp[i][0] !== null && temp[i][1] !== null && temp[i][0] !== '' && temp[i][1] !== '')
                            data.push(temp[i]);
                    let isValSize = true;
                    data.forEach(function (each) {
                        let size =each[2];
                        const pattern = /^[1-9]\d*$/g;
                        if(size !== null && size !== '')
                            if(!(pattern.test(size))) {
                                isValSize = false;
                                return 0;
                            }
                    });

                    if(data.length === 0)
                        showMsg('请检查输入数据表结构的输入！','error');
                    else if(!isValSize)
                        showMsg('长度只能为正整数！','error');
                    else {
                        function getMsg(msg,index) {
                            let returnMsg = msg[index][0] + ' '+ msg[index][1];
                            if(msg[index][2]!==null && msg[index][2]!=='')
                                returnMsg += '(' + msg[index][2] + ')';
                            if(msg[index][3]===true)
                                returnMsg += ' Not Null ';
                            if(msg[index][4]===true)
                                returnMsg += '主键';
                            return returnMsg;
                        }
                        let msg = '确定在 '+db+' 创建表 '+tb+'\n'+'字段：\n';
                        let count = 0;
                        while(count < data.length) {
                            msg += '    '+getMsg(data,count) + '\n';
                            count ++;
                        }
                        let r = confirm(msg);
                        if(r)
                            $.ajax({
                                url: './lib/Processing.php?type=3&db=' + db + '&tb=' + tb + '&data=' + data,
                                dataType: 'json',
                                timeout: 3000,
                                beforeSend: function () {
                                    showLoader();
                                },
                                complete: function () {
                                    hideLoader();
                                },
                                success: function (data) {
                                    if(data.success) {
                                        showMsg(data.msg, 'success');
                                        loadDetailTable(db);
                                    }
                                    else
                                        showMsg(data.msg,'error');
                                }
                            });
                    }
                }


            });


            // Right Part
            let detailTable = new Handsontable(document.getElementById('showDetail'), {
                fillHandle: false,
                stretchH: 'all',
                readOnly: true,
                colHeaders: true,
                minRows: 25,
                rowHeights: 30,
                height: 780,
                columnSorting: true,
                manualColumnResize: true,
                disableVisualSelection: true,
            });
            loadDetailTable(originalDb);
            $(window).resize(function () {
               loadDetailTable(originalDb);
            });
            function loadDetailTable(db){
                if(db === undefined)
                    db = '<?php echo $dba;?>';
                $.ajax({
                    url: './lib/Processing.php?type=5&db='+db,
                    dataType: 'json',
                    timeout: 3000,
                    beforeSend: function(){
                        showLoader();
                    },
                    complete: function() {
                        hideLoader();
                    },
                    success: function (data) {
                        if(data.success) {
                            let colHeaders = data.colHeaders;
                            let columns = data.columns;
                            detailTable.updateSettings({
                                colHeaders: colHeaders,
                                columns: columns,
                            });
                            detailTable.loadData(data.data);
                            $('.delete-table').click(function () {
                                let tb = $(this).attr('tb');
                                if(confirm('确定删除数据表'+ tb)) {
                                    $.ajax({
                                        url: './lib/Processing.php?type=4&db='+db+'&tb='+tb,
                                        dataType: 'json',
                                        timeout: 3000,
                                        success: function (data) {
                                            if(data.success) {
                                                showMsg(data.msg,'success');
                                                loadDetailTable(db);
                                            }
                                            else
                                                showMsg(data.msg, 'error');
                                        }
                                    });
                                }
                            });
                            $("#blockRight1").html(db+"<img id=\"refresh\" src=\"./res/refresh.png\" width=\"16px\" height=\"16px\" title=\"刷新\"/>");
                            $('#refresh').click(function () {
                                loadDetailTable(db);
                            })
                        } else {
                            showMsg(data.msg, 'error');
                        }
                    },
                });
            }


        });
    </script>
</body>
</html>
<?php $con->close();?>