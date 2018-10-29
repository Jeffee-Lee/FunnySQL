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
    <title>数据表 - FunnySQL</title>
    <link rel="shortcut icon" href="<?php echo $path;?>res/favicon.png">
    <link rel="stylesheet" href="<?php echo $path?>lib/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" href="<?php echo $path;?>lib/handsontable/handsontable.full.min.css">
    <link rel="stylesheet" href="<?php echo $path;?>lib/css.css">
</head>
<style>

    /*Left Part*/
    .left-top, .left-bottom, .right {
        border-radius: 10px;
        border: 1px solid #d9d9d9;
    }
    .left-top {
        margin-bottom: 30px;
    }
    .left-top-head,.left-bottom-head, .right-head {
        background: #d9d9d914;
        border-bottom: 1px solid #d9d9d9;
        color: black;
        padding: 9px;
        font-size: 18px;
        border-radius: 10px 10px 0 0;
    }
    .left-top-body,.left-bottom-body, .right-body {
        padding: 18px 10px;
        background: white;
        text-align: center;
        border-radius: 0 0 10px 10px;
    }
    .input {
        font-size: 16px;
        width: 45%;
        margin: 8px;;
        height: 25px;
    }
    .create-table {
        padding-top: 30px;
    }
    button {
        margin: 15px 5px 15px 15px;
        padding: 7px 18px;
        border: 1px solid #d9d9d9;
        background: #cdcdcd1f;
        outline: none;
    }
    button:hover {
        background: #d3f3ff;
    }
    button:active {
        background: #d3fffb;
    }

    .delete-table {
        color: red;
    }
    .delete-table:hover {
        color: #ff2f17;
    }
    .delete-table:hover {
        color: #ff6957;
    }
    .handsontable thead th {
        background: #d9d9d914;
    }
    .more{
        position: fixed;
        left: 0;
        bottom: 0;
        margin: 20px;
        font-size: 16px;
        cursor: pointer;
        display: inline-block;
    }
    #more {
        background: #fff24694;
        padding: 10px;
    }
    #more:hover,#newDelete:hover,#viewEdit:hover{
        color: #00000085;
    }
    .subMore {
        display: inline-block;
        padding: 10px;
        margin-right: 0;
    }
    #newDelete {
        background: #00ff0080;
    }
    #viewEdit {
        background: #00f8ff94;
    }
    a {
        text-decoration: none;
        color: #000;
    }
    #more-info {
        display: none;
    }
</style>
<body>
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

        <div class="left">
            <div class="left-top">
                <div class="left-top-head">新建数据表</div>
                <div class="left-top-body">
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
                    <div class="create-table">
                        <div id="create"></div>
                        <div class="btn" style="text-align: center; padding-top: 15px;">
                            <button class="subBtn" id="addRow">添加一行</button>
                            <button class="subBtn" id="removeRow">删除一行</button>
                            <button class="subBtn" id="clearData">&nbsp;&nbsp;清空&nbsp;&nbsp;</button>
                            <button class="subBtn" id="push">&nbsp;&nbsp;提交&nbsp;&nbsp;</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="left-bottom">
                <div class="left-bottom-head">删除数据表</div>
                <div class="left-bottom-body">

                    <select name="select-table" id="left-bottom-select-table" class="input" title="选择删除的数据表名">
                        <?php
                        $result = $con->query('SHOW TABLES FROM '.$dba);
                        while($row = $result->fetch_assoc()) {
                            $value = $row[sprintf('Tables_in_%s',$dba)];
                            echo '<option value="' . $value . '">'.$value.'</option>';
                        }
                        $result->free_result();
                        ?>
                    </select>
                    <button id="left-bottom-delete">&nbsp;&nbsp;删除&nbsp;&nbsp;</button>
                </div>
            </div>

        </div>
        
        <div class="right">
            <div class="right-head"><?php echo $dba?></div>
            <div class="right-body" >
                <div id="showDetail"></div>
            </div>
        </div>


    </div>

    <div class="more" id="more">&nbsp;&nbsp;更多操作&nbsp;&nbsp;</div>
    <div class="more" id="more-info">
        <a href="<?php echo $path;?>new-delete-table" id="newDelete" class="subMore"> 新建/删除 </a>
        <a href="<?php echo $path;?>view-edit-table" id="viewEdit"  class="subMore"> 查看/编辑 </a>
    </div>
    <script src="<?php echo $domain.$path?>lib/jquery.min.js"></script>
    <script src="<?php echo $path?>lib/jquery-ui/jquery-ui.js"></script>
    <script src="<?php echo $domain.$path?>lib/handsontable/handsontable.full.min.js"></script>
    <script src="<?php echo $path;?>lib/jquery/jquery.cookie.min.js"></script>
    <script src="<?php echo $path;?>lib/js.js"></script>
    <script>

        $(document).ready(function(){
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

            // Left Part
            let originalDb = '<?php echo $dba;?>';

            $('.select-database').change(function () {
                let db = $(this).val();
                if(db !== originalDb){
                    console.log('dbdb '+db);
                    reloadSelectTable(db);
                    loadDetailTable(db);
                    originalDb = db;
                    window.history.pushState({},'','<?php echo $domain.$path.basename(__FILE__,'.php');?>?db='+db);
                }
            });
            let container = document.getElementById('create');
            let hot = new Handsontable(container, {
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
            $('#addRow').click(function () {
                hot.alter('insert_row', hot.countRows());
            });
            $('#removeRow').click(function () {
                if(hot.countRows() === 16)
                    alert('😁不能再删除了！');
                else
                    hot.alter('remove_row', hot.countRows() - 1);
            });
            $('#clearData').click(function () {
                hot.loadData(['','','',false,false]);
            });
            $('#push').click(function () {
                let db = $('#left-top-select-database').val();
                let tb = $('#tableName').val();
                if(tb === null || tb === '')
                    alert('请输入新建数据表名！');
                else {
                    let temp = hot.getData();
                    let data = [];
                    for(let i = 0; i < hot.countRows(); i++)
                        if(temp[i][0] !== null && temp[i][1] !== null && temp[i][0] !== '' && temp[i][1] !== '')
                            data.push(temp[i]);
                    let isValSize = true;
                    data.forEach(function (each) {
                        let size =each[2];
                        const pattern = /^[1-9]\d*$/g;
                        if(size !== null)
                            if(!(pattern.test(size))) {
                                isValSize = false;
                                return 0;
                            }
                    });

                    if(data.length === 0)
                        alert('请检查输入数据表结构的输入！');
                    else if(!isValSize)
                        alert('长度只能为正整数！');
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
                                url: '<?php echo $domain.$path;?>lib/Processing.php?type=3&db=' + db + '&tb=' + tb + '&data=' + data,
                                dataType: 'json',
                                success: function (data) {
                                    if(data.success) {
                                        alert(data.msg);
                                        loadDetailTable(db);
                                        reloadSelectTable(db);
                                    }
                                    else
                                        alert(data.msg);
                                }
                            });
                    }
                }


            });
            $('#left-bottom-delete').click(function () {
                let tb = $('#left-bottom-select-table').val();
                let db = '<?php echo $dba;?>';

                let msg = '确定删除数据库'+db+'中的数据表'+tb;
                if (confirm(msg)) {
                    $.ajax({
                        url: '<?php echo $domain . $path . "lib/Processing.php?type=4&db=" . $dba.'&tb=';?>'+tb,
                        dataType: 'json',
                        success: function (data) {
                            if(data.success) {
                                alert(data.msg);
                                loadDetailTable();
                                reloadSelectTable();
                            }
                            else
                                alert(data.msg);
                        }
                    });
                }
            });
            function reloadSelectTable(db) {
                if(db === undefined)
                    db = '<?php echo $dba;?>'
                $.ajax({
                    url: '<?php echo $domain.$path.'lib/Processing.php?type=6&db=';?>'+db,
                    dataType: 'json',
                    success: function (data) {
                        if(data.success) {
                            $('#left-bottom-select-table').html(data.msg);
                        }
                        else
                            alert(data.msg);
                    }
                })
            }

            // Right Part
            let showDetail = document.getElementById('showDetail');
            let detailTable = new Handsontable(showDetail, {
                fillHandle: false,
                stretchH: 'all',
                readOnly: true,
                colHeaders: true,
                minRows: 25,
                rowHeights: 30,
                height: 780,
                columnSorting: true,
                manualColumnResize: true,
            });
            loadDetailTable(originalDb);
            function loadDetailTable(db){
                if(db === undefined)
                    db = '<?php echo $dba;?>';
                $.ajax({
                    url: '<?php echo $domain.$path."lib/Processing.php?type=5&db=";?>'+db,
                    dataType: 'json',
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
                                console.log('click');
                                if(confirm('确定删除数据表'+ tb)) {
                                    $.ajax({
                                        url: '<?php echo $domain . $path . "lib/Processing.php?type=4&db=";?>'+db+'&tb='+tb,
                                        dataType: 'json',
                                        success: function (data) {
                                            if(data.success) {
                                                alert(data.msg);
                                                reloadSelectTable(db);
                                                loadDetailTable(db);
                                            }
                                            else
                                                alert(data.msg);
                                        }
                                    });
                                }
                            });
                            $('.right-head').html(db);
                        } else {
                            alert(data.msg);
                        }
                    },
                });
            }

        });
    </script>
</body>
</html>
<?php $con->close();?>