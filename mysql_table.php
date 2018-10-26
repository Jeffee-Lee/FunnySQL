<?php
include('lib/settings.php');
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

// 连接类型
$type = '0';
if (isset($_GET['t']))
    $type = $_GET['t'];

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
        <?php if($type == '0') {?>
        <link href="https://cdn.bootcss.com/handsontable/6.0.1/handsontable.full.css" rel="stylesheet" media="screen">
        <style>
            .left {
                float: left;
                width: 50%;
                display: block;
                border: 1px solid #e7eaed;
                margin: 30px 20px;
                border-radius: 10px;
            }
            .left-head {
                background: #bbbbbb;
                color: #ffffff;
                padding: 9px;
                font-size: 20px;
                border-radius: 10px 10px 0 0;
            }
            .left-body {
                padding: 18px 0;
                background: #f3f3f3;
            }
            .select {
                text-align: center;
            }
            select, input {
                font-size: 17px;
            }
            .create-table {
                padding-top: 30px;
            }
        </style>
        <div class="left">
            <div class="left-head">新建数据表</div>
            <div class="left-body">
                <div class="select">
                    <select name="select-database" id="select-database" title="选择数据库">
                        <?php
                        foreach ($databases as $value)
                            echo '<option value="' . $value . '" > '.$value.'</option>';
                        ?>
                    </select>
                    <input type="text" placeholder="新建数据表名" id="tableName" >
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
        <script src="https://cdn.bootcss.com/handsontable/6.0.1/handsontable.full.min.js"></script>
        <script>
            $(document).ready(function () {
                let container = document.getElementById('create');
                let hot = new Handsontable(container, {
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
                    let db = $('#select-database').val();
                    let tb = $('#tableName').val();
                    if(tb === null || tb === '')
                        alert('请输入新建数据表名！');
                    else {
                        let temp = hot.getData();
                        let data = [];
                        for(let i = 0; i < hot.countRows(); i++)
                            if(temp[i][0] !== null && temp[i][1] !== null && temp[i][0] !== '' && temp[i][1] !== '')
                                data.push(temp[i]);
                        if(data.length === 0)
                            alert('请检查输入数据表的结构！');
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
                            alert(msg);
                            $.ajax({
                                url: '<?php echo $domain.$path;?>lib/Processing.php?type=3&data=' + data,
                                dataType: 'text',
                                success: function (data) {
                                    console.log(data);
                                }
                            });
                        }
                    }


                });
            });
        </script>

        <?php } else { switch ($type) { case '1': ?>
        <style>
            .left {
                float: left;
                width: 50%;
                display: block;
                border: 1px solid #e7eaed;
                margin: 30px 20px;
                border-radius: 10px;
            }
            .left-head {
                background: #bbbbbb;
                color: #ffffff;
                padding: 9px;
                font-size: 20px;
                border-radius: 10px 10px 0 0;
            }
            .left-body {
                padding: 18px 0;
                background: #f3f3f3;
            }
        </style>
        <div class="left">
            <div class="left-head">删除数据表</div>
            <div class="left-body">
                <div class="delete-table" style="text-align: center">
                    <select name="databaseName" id="databaseName" title="选择数据库">
                        <?php
                        foreach ($databases as $value)
                            echo '<option value="' . $value . '" > '.$value.'</option>';
                        ?>
                    </select>
                    <input type="text" placeholder="选择数据表" id="tableName" list="<?php echo $dba;?>">
                    <?php
                        foreach ($databases as $database) {
                            echo '<datalist id="'.$database.'">';
                            $result = $con->query('SHOW TABLES FROM '.$database);
                            while($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row[sprintf('Tables_in_%s', $database)] . '" >';
                            }
                            echo '</datalist>';
                            $result->free_result();
                        }
                    ?>
                    <button id="delete">删除</button>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                $('#databaseName').blur(function () {
                    let database = $('#databaseName').val();
                    $('#tableName').val('');
                    $('#tableName').attr('list',database);
                });
            });
        </script>

        <?php break; case '2':?>
        <link href="https://cdn.bootcss.com/handsontable/6.0.1/handsontable.full.css" rel="stylesheet" media="screen">
        <style>
            .left {
                float: left;
                width: 50%;
                display: block;
                border: 1px solid #e7eaed;
                margin: 30px 20px;
                border-radius: 10px;
            }
            .left-head {
                background: #bbbbbb;
                color: #ffffff;
                padding: 9px;
                font-size: 20px;
                border-radius: 10px 10px 0 0;
            }
            .left-body {
                padding: 18px 0;
                background: #f3f3f3;
            }
            select, input {
                font-size: 16px;
                padding: auto 20px;
            }
        </style>
        <div class="left">
            <div class="left-head">数据表数据操作</div>
            <div class="left-body">
                <div id="select" style="text-align: center">
                    <select name="databaseName" id="databaseName" title="选择数据库">
                        <?php
                        foreach ($databases as $value) {
                            $output = '<option value="' . $value . '" ';
                            if(!strcmp($value, $dba))
                                $output .= 'selected';
                            $output .= ' > '.$value.'</option>';
                            echo $output;
                        }
                        ?>
                    </select>
                    <input type="text" placeholder="选择数据表" id="tableName" list="<?php echo $dba;?>" value="<?php echo $tb;?>">
                    <?php
                    foreach ($databases as $database) {
                        $output = '<datalist id="'.$database.'">';
                        $result = $con->query('SHOW TABLES FROM '.$database);
                        while($row = $result->fetch_assoc()) {
                            $value = $row[sprintf('Tables_in_%s', $database)];
                            $output .= '<option value="' .$value. '" ';
                            if(!strcmp($value, $tb))
                                $output .= 'selected ';
                            $output .= '>';
                        }
                        $output .= '</datalist>';
                        echo $output;
                        $result->free_result();
                    }
                    ?>
                </div>
                <div class="show">
                    <div id="table"></div>
                </div>
            </div>
        </div>
        <script src="https://cdn.bootcss.com/handsontable/6.0.1/handsontable.full.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#databaseName').blur(function () {
                    let database = $('#databaseName').val();
                    $('#tableName').val('');
                    $('#tableName').attr('list',database);
                });
                $('#tableName').focus(function () {
                    $(this).val('');
                });
                $('#tableName').blur(function () {
                    if($(this).val() != null && $(this).val() !== '')
                        location.href = '<?php echo $domain.$path?>mysql_table?t=2&db='+$('#databaseName').val()+'&tb='+$(this).val();
                });
                var data = [
                    ["", "Ford", "Tesla", "Toyota", "Honda"],
                    ["2017", 10, 11, 12, 13],
                    ["2018", 20, 11, 14, 13],
                    ["2019", 30, 15, 12, 13]
                ];
                var container = document.getElementById('table');
                var hot = new Handsontable(container, {
                    data: data,
                    rowHeaders: true,
                    colHeaders: true,
                    filters: true,
                    dropdownMenu: true
                });
            });
        </script>
        <?php break; default: echo "<script>window.location.href='".$domain.$path."mysql_table';</script>"; } }?>

        <style>
            .right {
                float: right;
                width: 40%;
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
            .right .btn {
                margin: 10px 30px;
            }
        </style>
        <div class="right">
            <div class="right-head">其他操作</div>
            <div class="right-body">
                <a href="<?php echo $domain . $path ?>mysql_table?t=1">删除数据表</a>
                <a href="<?php echo $domain . $path ?>mysql_table?t=2">查看表数据</a>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                $('#deleteTable').click(function () {
                    location.href='<?php echo $domain.$path?>mysql_table?t=1';
                });
            });
        </script>
    </div>

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


        });
    </script>
</body>
</html>
<?php $con->close();?>