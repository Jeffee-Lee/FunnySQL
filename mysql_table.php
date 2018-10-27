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

    /*Left Part*/
    .left {
        float: left;
        width: 34.33%;
        margin: 30px 20px;

    }
    .left-top, .left-bottom {
        margin-bottom: 50px;
        display: block;
        border: 1px solid #e7eaed;
        border-radius: 10px;
    }
    .left-top-head,.left-bottom-head {
        background: #bbbbbb;
        color: #ffffff;
        padding: 9px;
        font-size: 20px;
        border-radius: 10px 10px 0 0;
    }
    .left-top-body,.left-bottom-body {
        padding: 18px 0;
        background: #f3f3f3;
        text-align: center;
    }
    .input {
        font-size: 17px;
        vertical-align: middle;
        height: 24px;
    }
    .create-table {
        padding-top: 30px;
    }

    /*Right Part*/
    .right {
        float: right;
        width: 58%;
        display: block;
        border: 1px solid #e7eaed;
        margin: 30px 20px;
        border-radius: 10px;
    }
    .right-head {
        background: #bbbbbb;
        color: #ffffff;
        padding: 9px;
        font-size: 20px;
        border-radius: 10px 10px 0 0;
    }
    .right-body {
        padding: 18px 0;
        background: #f3f3f3;
        text-align: center;
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
                        <div class="btn" style="text-align: center; padding-top: 30px;">
                            <button class="subBtn" id="addRow">添加一行</button>
                            <button class="subBtn" id="removeRow">删除最后一行</button>
                            <button class="subBtn" id="push">提交</button>
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
                    <button id="left-bottom-delete">删除</button>
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
    <script src="<?php echo $domain.$path?>lib/jquery.min.js"></script>
    <script src="<?php echo $domain.$path?>lib/handsontable/handsontable.full.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script>

        $(document).ready(function(){

            // Public
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

            // Left Part
            let originalDb = '<?php echo $dba;?>';
            let container = document.getElementById('create');
            $('.select-database').blur(function () {
                let db = $(this).val();
                if(db !== originalDb)
                    location.href = '<?php echo $domain.$path;?>mysql_table?db='+db;
            });
            let hot = new Handsontable(container, {
                fillHandle: false,
                stretchH: 'all',
                colHeaders: ['名', '类型', '长度', '不是 null', '主键'],
                minRows: 15,
                height: 395,
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
            $('#addRow').click(function () {
                hot.alter('insert_row', hot.countRows());
            });
            $('#removeRow').click(function () {
                hot.alter('remove_row', hot.countRows() - 1);
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
                                        loadDetailTable();
                                        reloadSelectTable();
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
            function reloadSelectTable() {
                $.ajax({
                    url: '<?php echo $domain.$path.'lib/Processing.php?type=6&db='.$dba;?>',
                    dataType: 'json',
                    success: function (data) {
                        if(data.success) {
                            $('#left-bottom-select-table').html(data.msg);
                            loadDetailTable();
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
                minRows: 20,
                rowHeights: 30,
                height: 630,
                autoWrapRow: true,
            });
            function loadDetailTable(){
                $.ajax({
                    url: '<?php echo $domain.$path."lib/Processing.php?type=5&db=".$dba;?>',
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
                                        url: '<?php echo $domain . $path . "lib/Processing.php?type=4&db=" . $dba.'&tb=';?>'+tb,
                                        dataType: 'json',
                                        success: function (data) {
                                            if(data.success) {
                                                alert(data.msg);
                                                loadDetailTable();
                                            }
                                            else
                                                alert(data.msg);
                                        }
                                    });
                                }
                            });
                        } else {
                            alert(data.msg);
                        }
                    },
                });
            }
            loadDetailTable();
        });
    </script>
</body>
</html>
<?php $con->close();?>