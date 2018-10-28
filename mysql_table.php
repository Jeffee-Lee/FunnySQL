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
        z-index: 1999;
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
        display: block;
        border: 1px solid #e7eaed;
        border-radius: 10px;
    }
    .left-top {
        margin-bottom: 60px;
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
        width: 59%;
        display: block;
        border: 1px solid #e7eaed;
        margin: 30px 20px 0 20px;
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
            <img src="data:image/png;base64,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
"  class="icon mysql" width="16px" height="16px">&nbsp;概述</a>
        <a href="mysql_database.php" class="tab-1">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC8AAAAyCAYAAADMb4LpAAAACXBIWXMAAA7EAAAOxAGVKw4bAAADTklEQVRoge2a3W9TZRyAn57T9vQDurZrofsIxLGBEmGCI+yDDOOVGhMS0AVCwiUmCw5C/DMMwUmWyKWJiqJLuDFe+bGwDzKJlhkVN2YkGyv0c4WuPac7PVyM1jG2tU2gZ03Oc/u+78mT9+L9PRfHpGmaRomoOZX/Ht3lduIfJhNTzKRmCacjhBbuE05HWFhcYF6ZR0NjYTGNrMpIooTDbMeEiRprDQ6zA7/dR8CxFb/dR6OzgRZ3M7vcO9m+aRuiIJaqg2k9+WgmxkhojOtzI4zdv0EwOoGsyiV/vFwkUaK1dg/tWw9yqK6TzkA7tTbvmvufkU9lUwxOX+OLyStcnxtB1dQXJlsM0SRyqK6Tky3HOdp0BKfF+dR6QV5RFQb++IyPf79IVI7pIrsetZKXj147R++rH2AVrcAT+XA6wrEfjjMevqmzYnEO+F/nu7eu4Lf7EAB6h/qqQhxgPHyT3qE+gCX5H2d/0VWoXPK+AsCbDYd1lSmXvK8AMNDdT5t/v65CpdLm389Adz+w4rX5dGKAC8F+YnJcV8HV8Eoezrf28eGe3qdfm22ft3DtnW/Z52sllU1x9c4gX05+zUhoTPd3vjPQzomWHnp2HMNpcfJbJMiR79/j7qnJJXn7ZQ+SKHFu7xnO7j2DR3IDSxN2ODTK8NxoxSdsV10HXYGOwoSNywk+uXWJi7cuIasy6dPx/+XzuCwu3m8+yonmHjoCBxFMQmGtlLZJKAkAHmVTZHNZLIKFTU8mo9vqLqttclqO0dANvpr6hqtTgySzycLaqvLL2WL380b9YbrruziwpY3dnpcxC+bnd9UrWMwt8mf8b8Yf/MrQvWF+mv2ZcCay6t6i8iuxClZe8exih6uJppqXaHQ20uCsx2fz4rV52WzdjE20IZoEXFZX4VxSSaJqOTJqhofKQ2KZGJFMjNnUPWZSM0zP/8ud5DR/xW+j5JSSXNKn45R1jUpOIRidIBidKOfYC0MovmXjUv3yDrNDb4+yyPsabVNJjLapBEbb5DHaZh2MtlkLo22eI4a8XhhhVmmMMKskRpgZYWaEmRFm+lH9eVCtVL/8u9vf1tujLPK+RttUgjXbZvmmjd42yyn6y8pGaZvVWFd+JZVum2I8Bnrad8eZorexAAAAAElFTkSuQmCC"  class="icon database" width="16px" height="16px">&nbsp;数据库</a>
        <a href="mysql_table.php" class="tab-2 active">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAZCAYAAAAv3j5gAAAACXBIWXMAAA7EAAAOxAGVKw4bAAABwklEQVRIie2VvW4TURCFv7syokiVFEFIqSJR8QJuaPIEESIIGhpexU2aVLQ0FMhSmjyC2ygl0BCJivBXhCJYiuRoDsX98dxlvbECTsVYa+/ec+6cnbkz43A5u9LHb1OuTKzCBk1ga/0ug8evjjmfzlYikm1j7Q7NqkUAzqczmpWrJPsvdHMbjibqsydvz3rxZTjD0UQNgCQElC/J/bo1tXot8wChfFuguU8YeMQD7U3dDzW/LeYfi5AkCIBCrZIxZyHUTjo5BOQYg4pUhbTY2kF1clp+wnA00db2g+t3/oV9/nQaIxrvbSb9ACG/SQDB88PvjJ9u/pGqxADg2eEPxnv3XBR1+h/tn8Y+igSBjHwb82NAXlYtpojmNFoBlT6FBpQzcq/ZcQByTtpicvuqFqHOQF0MC+w6fBnOrQndbtW92V1fSHpx9LMXX4azc5BSZ2aEXKyxqmOVpyWZm2dlLARCUJokYKY0WERcDlU1xBEklckTRRSJRcCAEDGz5MTw/VI4eciY8FoxIj+ZSyXPX8fMT+k55gtANu+e0kMuogbg3dlF/KvouLLDvquP8/7Lr3giL1+f6MPXaccR/jt7eH+N3049rpNFFdKRAAAAAElFTkSuQmCC"  class="icon table" width="16px" height="16px">&nbsp;数据表
        </a>
        <a href="temp.php" class="tab-3">
            <img src="" class="icon">&nbsp;临时</a>
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

            /* Common */
            //顶部标签点击
            $(".head > a").click(function () {
                if($(this).nextAll().length !== 0) {
                    //不是最右边的关闭
                    $('a.active').removeClass('active');
                    $(this).toggleClass('active');
                } else
                    $('.msg').toggle();
            });
            // 弹出窗右上角关闭按钮或弹出窗取消按钮点击
            $('.msg-head a ,.msg-body button:last-child').click(function () {
                $('.msg').hide();
            });
            // 弹出窗确定按钮点击
            $('.msg-body button:first-child').click(function () {
                $.cookie('funnysql', '', {expires: -10, path: "<?php echo $path;?>"});
                window.location.replace('<?php echo $domain.$path;?>test');
            });

            // Left Part
            let originalDb = '<?php echo $dba;?>';

            $('.select-database').change(function () {
                let db = $(this).val();
                if(db !== originalDb){
                    console.log('dbdb '+db);
                    reloadSelectTable(db);
                    loadDetailTable(db);
                    originalDb = db;
                    window.history.pushState({},0,'<?php echo $domain.$path?>mysql_table?db='+db);
                }
            });
            let container = document.getElementById('create');
            let hot = new Handsontable(container, {
                fillHandle: false,
                stretchH: 'all',
                colHeaders: ['名', '类型', '长度', 'No Null', '主键'],
                minRows: 20,
                height: 500,
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
                if(hot.countRows() === 20)
                    alert('😁不能再删除了！');
                else
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
                disableVisualSelection: true,
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