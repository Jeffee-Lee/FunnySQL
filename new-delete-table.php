<?php
include ('/lib/settings.php');
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
            <div class="block" style="margin-top: 30px;">
                <div class="block-head">删除数据表</div>
                <div class="block-body">

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
            <div class="block">
                <div class="block-head" id="blockRight1"><?php echo $dba?><img id="refresh" src="<?php echo $path?>res/refresh.png" width="16px" height="16px" title="刷新"/></div>
                <div class="block-body" >
                    <div id="showDetail"></div>
                </div>
            </div>
        </div>


    </div>

    <div class="more" id="more">&nbsp;&nbsp;更多操作&nbsp;&nbsp;</div>
    <div class="more" id="more-info">
        <a href="<?php echo $path;?>new-delete-table" id="newDelete" class="subMore"> 新建/删除 </a>
        <a href="<?php echo $path;?>view-edit-table" id="viewEdit"  class="subMore"> 查看/编辑 </a>
    </div>
    <script src="<?php echo $path?>lib/jquery.min.js"></script>
    <script src="<?php echo $path?>lib/jquery-ui/jquery-ui.js"></script>
    <script src="<?php echo $path?>lib/handsontable/handsontable.full.min.js"></script>
    <script src="<?php echo $path;?>lib/jquery/jquery.cookie.min.js"></script>
    <script>

        $(document).ready(function(){
            /* Other Start */
            $("#nav-table").addClass('active');
            $("#more").hover(function () {
                $("#more").fadeOut('slow');
                $("#more-info").fadeIn('slow');
            });
            $("#more-info").mouseleave(function () {
                $("#more-info").fadeOut('slow');
                $("#more").fadeIn('slow');
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

            /* Left Part Start */
            let originalDb = '<?php echo $dba;?>';
            // 选择数据库改变
            $('.select-database').change(function () {
                let db = $(this).val();
                reloadSelectTable(db);
                loadDetailTable(db);
                originalDb = db;
                window.history.replaceState({},'','<?php echo $domain.$path.basename(__FILE__,'.php');?>?db='+db);

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
                        if(size !== null)
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
                                url: '<?php echo $domain.$path;?>lib/Processing.php?type=3&db=' + db + '&tb=' + tb + '&data=' + data,
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
                                        reloadSelectTable(db);
                                    }
                                    else
                                        showMsg(data.msg,'error');
                                }
                            });
                    }
                }


            });
            // 左下块删除按钮点击
            $("#left-bottom-delete").click(function () {
                let tb = $("#left-bottom-select-table").val();

                let msg = '确定删除数据库'+originalDb+'中的数据表'+tb;
                if (confirm(msg)) {
                    $.ajax({
                        url: '<?php echo $domain . $path . "lib/Processing.php?type=4&db=";?>' + originalDb + '&tb=' +tb,
                        dataType: 'json',
                        timeout: 3000,
                        beforeSend: function(){
                            showLoader();
                        },
                        complete: function(){
                            hideLoader();
                        },
                        success: function (data) {
                            if(data.success) {
                                showMsg(data.msg,'success');
                                loadDetailTable(originalDb);
                                reloadSelectTable(originalDb);
                            }
                            else
                                showMsg(data.msg, 'error');
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
                    timeout: 3000,
                    success: function (data) {
                        if(data.success) {
                            $("#left-bottom-select-table").html(data.msg);
                        }
                        else
                            showMsg(data.msg, 'error');
                    }
                })
            }

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
            });
            loadDetailTable(originalDb);
            function loadDetailTable(db){
                if(db === undefined)
                    db = '<?php echo $dba;?>';
                $.ajax({
                    url: '<?php echo $path."lib/Processing.php?type=5&db=";?>'+db,
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
                                        url: '<?php echo $domain . $path . "lib/Processing.php?type=4&db=";?>'+db+'&tb='+tb,
                                        dataType: 'json',
                                        timeout: 3000,
                                        success: function (data) {
                                            if(data.success) {
                                                showMsg(data.msg,'success');
                                                reloadSelectTable(db);
                                                loadDetailTable(db);
                                            }
                                            else
                                                showMsg(data.msg, 'error');
                                        }
                                    });
                                }
                            });
                            $("#blockRight1").html(db+"<img id=\"refresh\" src=\"<?php echo $path?>res/refresh.png\" width=\"16px\" height=\"16px\" title=\"刷新\"/>");
                            $('#refresh').click(function () {
                                reloadSelectTable(db);
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