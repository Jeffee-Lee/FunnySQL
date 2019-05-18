<?php
include ('./lib/settings.php');
session_start([
    "gc_maxlifetime"=> 60 * 60 * 24 * 7,
    "cookie_lifetime"=> 60 * 60 * 24 * 7
]);
if(!array_key_exists('host', $_SESSION))
    header("Location: ./login.php");
//$con_info = json_decode(base64_decode($_COOKIE['session']));
$con = new mysqli($_SESSION["host"], $_SESSION["userName"], $_SESSION["password"], '', $_SESSION["port"]);

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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $PAGE_TITLE_TABLE;?></title>
    <link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
    <link rel="stylesheet" href="./lib/handsontable/handsontable.full.min.css">
    <link rel="stylesheet" href="./lib/css.css">
</head>
<body>
    <div id="common">
        <div id="msg" v-show="isMsgShow" style="display: none;" :class="{'msgShow':isMsgShow, 'errorMsg':isShowError, 'successMsg': isShowSuccess}">
            <span id="msg-body">{{ message }}</span>
            <span id="msg-close" style="cursor: pointer;" @click="hideMsg">X</span>
        </div>
        <div id="loader" style="display: none;" v-show="isShowLoader"></div>
        <div id="fullScreen" v-show="isShowClose" style="display: none;"></div>
        <div id="close" v-show="isShowClose" style="display: none">
            <div class="close-head">确定离开？<span title="关闭" @click="hideClose">X</span></div>
            <div class="close-body">
                <button @click="logout">确定</button>
                <button @click="hideClose">取消</button>
            </div>
        </div>
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
                <img src="./res/table.png"  class="icon table icon-inactive">
                <img src="./res/table_active.png"  class="icon table icon-active">
                &nbsp;数据表
            </a>
            <a href="./sql.php" id="nav-sql">
                <img src="./res/sql.png"  class="icon sql icon-inactive" >
                <img src="./res/sql_active.png"  class="icon sql icon-active">
                &nbsp;SQL
            </a>
            <a href="./toolbox.php" id="nav-backup">
                <img src="./res/backup.png"  class="icon backup icon-inactive" >
                <img src="./res/backup_active.png"  class="icon backup icon-active">
                &nbsp;工具箱
            </a>
            <span id="exit" title="退出" @click="showClose">X</span>
        </div>
    </div>
    <div class="main" id="app">

        <div class="left">
            <div class="block">
                <div class="block-head">新建数据表</div>
                <div class="block-body">
                    <div class="select">
                        <select name="select-database"
                                class="input" title="选择数据库" v-model="dbName">
                            <option :value="db" v-for="db in dbList">
                                {{ db }}
                            </option>
                        </select>
                        <input type="text" placeholder="新建数据表名" class="input"
                               v-model="newTbName" onfocus="this.select()">
                    </div>
                    <div class="create-table" style="padding-top: 30px;">
                        <div id="create"></div>
                        <div class="btn" style="text-align: center; padding-top: 15px;">
                            <button class="subBtn" @click="addRow">添加一行</button>
                            <button class="subBtn" @click="removeRow">删除一行</button>
                            <button class="subBtn" @click="reset">&nbsp;&nbsp;重置&nbsp;&nbsp;</button>
                            <button class="subBtn" @click="createTb">&nbsp;&nbsp;创建&nbsp;&nbsp;</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
        <div class="right">
            <div class="block">
                <div class="block-head" id="blockRight1">
                    <span>{{ dbName }}</span>
                    <img id="refresh" src="./res/refresh.png" width="16px"
                         height="16px" title="刷新" @click="refresh"/>
                </div>
                <div class="block-body" >
                    <div id="showDetail"></div>
                </div>
            </div>
        </div>

        <div class="more" id="more" @mouseover="moreMouseOver">&nbsp;&nbsp;更多操作&nbsp;&nbsp;</div>
        <div class="more" id="more-info" @mouseleave="moreMouseLeave">
            <a href="./new-delete-table.php" id="newDelete" class="subMore"> 新建/删除 </a>
            <a href="./view-edit-table.php" id="viewEdit"  class="subMore"> 查看/编辑 </a>
        </div>
    </div>


    <script src="./lib/jquery.min.js"></script>
    <script src="./lib/handsontable/handsontable.full.min.js"></script>
    <script src="./lib/vue.min.js"></script>
    <script src="./lib/axios.min.js"></script>
    <script src="./lib/js.js"></script>
    <script>
        let createTable;
        let tbTable;
        let app = new Vue({
            el: "#app",
            data: {
                dbList: <?php echo json_encode($databases);?>,
                dbName: <?php echo "'{$dba}'";?>,
                newTbName: null
            },
            methods: {
                moreMouseOver: function () {
                   $("#more").fadeOut('slow');
                   $("#more-info").fadeIn('slow');
                },
                moreMouseLeave: function () {
                    $("#more-info").fadeOut('slow');
                    $("#more").fadeIn('slow');
                },
                loadTbTable: function () {
                    let db = app.dbName;
                    common.showLoader();
                    axios.get('./lib/Processing.php?type=5&db=' + db)
                        .then(function (response) {
                            let data = response.data;
                            if (data.success) {
                                tbTable.updateSettings({
                                    colHeaders: data.colHeaders,
                                    columns: data.columns,
                                });
                                tbTable.loadData(data.data);
                                // app.dbList = data.dbList;
                            }
                        })
                        .catch(function (error) {
                            common.showError(error);
                        })
                        .finally(function () {
                            common.hideLoader();
                        })
                },
                addRow: function () {
                    createTable.alter('insert_row', createTable.countRows());
                },
                removeRow: function () {
                    if(createTable.countRows() === 20) {
                        common.showError('😁 不能再删除了！');
                    } else {
                        createTable.alter('remove_row', createTable.countRows() - 1);
                    }
                },
                reset: function () {
                    createTable.loadData(['','','',false,false]);
                },
                createTb: function () {
                    if (! app.newTbName) {
                        common.showError('请输入新建数据表名！');
                    } else {
                        let db = app.dbName;
                        let tb = app.newTbName;
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
                           common.showError('请检查输入数据表结构的输入！');
                        else if(!isValSize)
                            common.showError('长度只能为正整数！');
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
                           if(r) {
                               common.showLoader();
                               axios.get('./lib/Processing.php?type=3&db=' + db + '&tb=' + tb + '&data=' + data)
                                   .then(function (response) {
                                       let data = response.data;
                                       if (data.success) {
                                           common.showSuccess(data.msg);
                                           app.loadTbTable();
                                           app.newTbName = null;
                                           app.reset();
                                       } else {
                                           common.showError(data.msg);
                                       }
                                   })
                                   .catch(function (error) {
                                       common.showError(error);
                                   })
                                   .finally(function () {
                                       common.hideLoader();
                                   })
                           }
                        }
                    }
                },
                refresh: function () {
                    this.loadTbTable();
                },
                rebindClickDelete: function () {
                    $('.delete-table').unbind('click').click(function () {
                       let tb = $(this).attr('tb');
                       let db = app.dbName;
                       if(confirm('确定删除数据表'+ tb)) {
                           common.showLoader();
                           axios.get('./lib/Processing.php?type=4&db='+db+'&tb='+tb)
                               .then(function (response) {
                                   let data = response.data;
                                   if (data.success) {
                                       common.showSuccess(data.msg);
                                       app.loadTbTable();
                                   } else {
                                       common.showError(data.msg);
                                   }
                               })
                               .catch(function (error) {
                                   common.showError(error);
                               })
                               .finally(function () {
                                   common.hideLoader();
                               });
                       }
                    });
                },

            },
            watch: {
                dbName: function (val) {
                    this.dbName = val;
                    app.loadTbTable();
                    window.history.replaceState({},'','./new-delete-table.php?db=' + app.dbName);
                }
            },
            mounted: function () {
                this.$nextTick(function () {
                    tbTable = new Handsontable(document.getElementById('showDetail'), {
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
                    createTable = new Handsontable(document.getElementById('create'), {
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
                    app.loadTbTable();
                    setInterval(function () {
                        app.rebindClickDelete();
                    }, 30);
                });
            }
        });
    </script>
</body>
</html>
<?php $con->close();?>