<?php
include('./lib/settings.php');
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
if ($tb == null) {
    $isHasTable = "false";
} else {
    $isHasTable = "true";
}

$page = 1;
if(isset($_GET['p']) and !empty($_GET['p']))
    $page = $_GET['p'];

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
    <link rel="stylesheet" href="./lib/handsontable/handsontable.full.min.css">
    <link rel="stylesheet" href="./lib/css.css">
</head>
<style>
    .pageSkip {
        display: inline-block;
        background: transparent url('./res/arrow-left.png') no-repeat -10px -10px;
        text-indent: -999em;
        background-size: 40px;
        opacity: 0.7;
        vertical-align: middle;
        width: 20px;
        height: 20px;
    }
    .pageNext {
        background-image: url('./res/arrow-right.png');
    }
    .changPage {
        margin-top: 10px;
    }
    #createIndex select, #createIndex input {
        width: 30%;
    }
</style>
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
        <a href="./view-edit-table.php" id="nav-table" class="active">
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
            <div class="block-head">选择数据表</div>
            <div class="block-body">
                <select name="databaseName" id="databaseName" title="选择数据库" class="input" v-model="selectedDb">
                    <option :value="db" v-for="db in dbList">{{ db }}</option>

                </select>
                <select name="tableName" id="tableName" :title="isHasTable ? selectedTb : '无数据表'"
                        class="input" v-model="selectedTb" :disabled="!isHasTable">
                    <option :value="tb" v-for="tb in tbList">{{ tb }}</option>
                </select>
                <div style="display: none;" v-show="!isHasTable">
                    <a :href="'./new-delete-table.php?db=' + selectedDb"
                       style=" margin-top: -3px;padding-right: 20px; float: right; font-size: 14px; text-decoration: none"
                        title="创建数据表">创建数据表</a>
                </div>
            </div>
        </div>
        <div class="block" style="margin-top: 30px;">
            <div class="block-head">插入数据</div>
            <div class="block-body">
                <div id="insertTable" v-show="isHasTable"></div>
                <button id="insertAdd" @click="addRow" v-show="isHasTable">添加一行</button>
                <button id="insertDelete" @click="removeRow" v-show="isHasTable">删除一行</button>
                <button id="insertClear" @click="reset" v-show="isHasTable">&nbsp;&nbsp;清空&nbsp;&nbsp;</button>
                <button id="insertSubmit" @click="submitInsert" v-show="isHasTable">&nbsp;&nbsp;提交&nbsp;&nbsp;</button>
                <div v-show="!isHasTable">
                    <span>Nothing to do!</span>
                </div>
            </div>
        </div>
    </div>
    <div class="right">
        <div class="block">
            <div class="block-head" id="blockRight1-head">
                <span>{{ selectedTb ? selectedTb : '无数据表' }}</span>
                <img id="refresh" src="./res/refresh.png" width="16px"
                     height="16px" title="刷新" @click="refresh"/>
            </div>
            <div class="block-body">
                <div class="dataTable" id="recordTable" v-show="isHasTable && isHasRecord" style="display: none;"></div>
                <div class="changPage" v-show="isHasTable && isHasRecord">
                    <a href="javascript:void(0)" class="pageSkip" id="pagePrev" @click="goPrev"> </a>

                    <input type="number" maxlength="10" title="Page" style="display: none; font-size: 16px; width: 80px; cursor: text"
                           id="inputPage" @mouseout="hidePageInput" @keydown.enter="goPage" v-model="inputPage">
                    <label id="pageInfo" style="font-size: 16px ;width: 60px;cursor: pointer"
                           @click="showPageInput">
                        {{ page }}/{{ totalPage }}
                    </label>
                    <a href="javascript:void(0)" class="pageSkip pageNext" id="pageNext" @click="goNext"> </a>
                </div>
                <div v-if="!(isHasTable && isHasRecord)">
                    <span>Nothing to See!</span>
                </div>
        </div>
    </div>
</div>

    <div class="more" id="more" @mouseover="moreMouseOver">&nbsp;&nbsp;更多操作&nbsp;&nbsp;</div>
    <div class="more" id="more-info" @mouseleave="moreMouseLeave">
        <a href="./new-delete-table.php" id="newDelete" class="subMore"> 新建/删除 </a>
        <a href="./view-edit-table.php" id="viewEdit"  class="subMore"> 查看/编辑 </a>
    </div>

    <div class="block" id="popUpEdit" style="display: none;"  v-show="isShowEdit">
        <div class="block-head">
            <span id="editTitle">编辑数据</span>
            <span id="editClose" @click="closeEdit">X</span>
        </div>
        <div class="block-body" id="popUpEdit-body">
            <div id="editTable"></div>
            <button id="editSubmit" @click="submitEdit">确定</button>
            <button @click="closeEdit">取消</button>
        </div>
    </div>
</div>
<script src="./lib/jquery.min.js"></script>
<script src="./lib/vue.min.js"></script>
<script src="./lib/axios.min.js"></script>
<script src="./lib/handsontable/handsontable.full.min.js"></script>
<script src="./lib/js.js"></script>
<script>
    let insertTable;
    let recordTable;
    let editTable;
    let app = new Vue({
        el: "#app",
        data: {
            dbList: <?php echo json_encode($databases);?>,
            tbList: <?php echo json_encode($tables);?>,
            selectedDb: <?php echo "'$dba'"?>,
            selectedTb: <?php echo "'$tb'"?>,
            page: <?php echo $page;?>,
            totalPage: 1,
            isHasTable: <?php echo $isHasTable;?>,
            isHasRecord: true,
            inputPage: null,
            isShowEdit: false
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
            loadRecord: function () {
                app.isHasRecord = true;
                let db = app.selectedDb;
                let tb = app.selectedTb;
                let page = app.page;
                if (app.isHasTable) {
                    common.showLoader();
                    // recordTable.loadData(['','','',false,false]);
                    axios.get('./lib/Processing.php?type=7&db='+db + '&tb=' + tb + '&p='+ page)
                        .then(function (response) {
                            let data = response.data;
                            if (data.success) {
                                if (data.totalPage === 0)  {
                                    app.isHasRecord = false;
                                    insertTable.updateSettings({
                                        colHeaders: data.colHeaders.slice(2,),
                                        columns: data.columns.slice(2,),
                                    });
                                } else {
                                    recordTable.loadData(data.data);
                                    recordTable.updateSettings({
                                        colHeaders: data.colHeaders,
                                        columns: data.columns,
                                    });
                                    insertTable.updateSettings({
                                        colHeaders: data.colHeaders.slice(2,),
                                        columns: data.columns.slice(2,),
                                    });
                                    editTable.updateSettings({
                                        colHeaders: data.colHeaders.slice(2,),
                                        columns: data.columns.slice(2,),
                                    });

                                    app.page = data.currentPage;
                                    app.totalPage = data.totalPage;
                                }
                            } else {
                                common.showError(data.msg);
                                app.isHasRecord = false;
                            }
                        })
                        .catch(function (error) {
                            common.showError(error);
                            app.isHasRecord = false;
                        })
                        .then(function () {
                            common.hideLoader();
                        })
                }

            },
            showPageInput: function () {
                app.inputPage = null;
                $('#inputPage').show().focus();
                $('#pageInfo').hide();
            },
            hidePageInput: function () {
                $('#inputPage').hide().val('');
                $('#pageInfo').show();
            },
            goPage: function () {
                let inputPage = parseInt(app.inputPage);
                if (inputPage <= app.totalPage && inputPage >= 1) {
                    app.page = inputPage;
                    app.loadRecord();
                } else {
                    common.showError("超出范围！");
                }
            },
            goPrev: function () {
                let page = parseInt(app.page) - 1;
                if (page <= app.totalPage && page >= 1) {
                    app.page = page;
                    app.loadRecord();
                } else {
                    common.showError("超出范围！");
                }
            },
            goNext: function () {
                let page = parseInt(app.page) + 1;
                if (page <= app.totalPage && page >= 1) {
                    app.page = page;
                    app.loadRecord();
                } else {
                    common.showError("超出范围！");
                }
            },
            refresh: function () {
                // !@# 如果数据库无表，或表无记录，而在其他平台操作使得数据库出现表，或表出现记录则可能无法呈现。
                app.loadRecord();
            },
            rebindClickDelete: function () {
                $('.deleteData').unbind('click').click(function () {
                    let row = recordTable.getDataAtRow(parseInt($(this).attr('column')));
                    let i = 2, db = app.selectedDb, tb = app.selectedTb, page = parseInt(app.page),
                        condition = '', colHeaders = recordTable.getColHeader();
                    while (i < colHeaders.length) {
                        if(row[i] !== null && row[1] !== '') {
                            condition += colHeaders[i] + "='" + row[i] + "' and ";
                        }
                        i ++;
                    }
                    axios.get( './lib/Processing.php?type=8&db='+db + '&tb=' + tb + '&p='+ page + '&condition='+ condition)
                        .then(function (response) {
                            let data = response.data;
                            if (data.success) {
                                app.loadRecord(); // !@# 可能会产生错误。如：删除该记录后当前页表空！
                            } else {
                                common.showError(data.msg);
                            }
                        })
                        .catch(function (error) {
                            common.showError(error);
                        })
                });
            },
            rebindClickEdit: function () {
                $('.editData').unbind('click').click(function () {
                    let row = $(this).attr('row');
                    let load = [];
                    load.push(recordTable.getDataAtRow(parseInt(row)).slice(2,));
                    editTable.loadData(load);
                    $('#popUpEdit').attr('row',row);
                    app.isShowEdit = true;
                    $('#fullScreen').show();
                });
            },
            closeEdit: function () {
                $("#fullScreen").hide();
                app.isShowEdit = false;
            },
            submitEdit: function () {
                let beforeChange = recordTable.getDataAtRow(parseInt($("#popUpEdit").attr("row"))).slice(2,);
                let afterChange = editTable.getDataAtRow(0);
                if(JSON.stringify(beforeChange) === JSON.stringify(afterChange)) {
                    common.showError("记录并未修改！");
                } else {
                    let params = new URLSearchParams();
                    params.append("type", '4');
                    params.append("db", app.selectedDb);
                    params.append("tb", app.selectedTb);
                    params.append("beforeChange", JSON.stringify(beforeChange));
                    params.append("afterChange", JSON.stringify(afterChange));
                    axios.post('./lib/Processing.php', params.toString())
                        .then(function (response) {
                            let data = response.data;
                            if (data.success) {
                                app.closeEdit();
                                common.showSuccess(data.msg);
                                app.loadRecord();
                            } else {
                                common.showError(data.msg);
                            }
                        })
                }
            },
            addRow: function () {
                insertTable.alter("insert_row", insertTable.countRows());
            },
            removeRow: function () {
                if (insertTable.countRows() === 20) {
                    common.showError('😁 不能再删除了！');
                } else {
                    insertTable.alter("remove_row", insertTable.countRows() - 1);
                }
            },
            reset: function () {
                insertTable.loadData(['','','',false,false]);
            },
            submitInsert: function () {
                let data = [], temp = insertTable.getData();
                for (let row = 0; row < insertTable.countRows(); row ++ ) {
                    for (let column = 0; column < insertTable.countCols(); column ++) {
                        if (temp[row][column]) {
                            data.push(temp[row]);
                            break;
                        }
                    }
                }

                if (data.length === 0) {
                    common.showError("啥都没填，就别提交了！");
                } else {
                    let params = new URLSearchParams();
                    params.append("type", "3");
                    params.append("db", app.selectedDb);
                    params.append("tb", app.selectedTb);
                    params.append("data", JSON.stringify(data));
                    axios.post("./lib/Processing.php", params.toString())
                        .then(function (response) {
                            let data = response.data;
                            if (data.success) {
                                common.showSuccess(data.msg);
                                app.loadRecord();
                                app.reset();
                            } else {
                                common.showError(data.msg);
                            }
                        })
                        .catch(function (error) {
                            common.showError(error);
                        })
                }
            }
        },
        watch: {
            selectedDb: function (val) {
                this.selectedDb = val;
                axios.get("./lib/api/GetTableList.php?db=" + val)
                    .then(function (response) {
                        let data = response.data;
                        if (data.length !== 0) {
                            app.page = 1;
                            app.tbList = data;
                            app.selectedTb = data[0];
                            app.isHasTable = true;
                            app.loadRecord();
                            window.history.replaceState({},'','./view-edit-table.php?db='
                                + app.selectedDb + "&tb=" + app.selectedTb);
                        } else {
                            app.tbList = null;
                            app.selectedTb = null;
                            app.isHasTable = false;
                            window.history.replaceState({},'','./view-edit-table.php?db=' + app.selectedDb);
                        }
                    })
            },
            selectedTb: function (val) {
                this.selectedTb = val;
                if (val) {
                    app.page = 1;
                    app.loadRecord();
                    window.history.replaceState({}, '', './view-edit-table.php?db=' + app.selectedDb + "&tb=" + app.selectedTb);
                }
            },
        },
        mounted: function () {
            this.$nextTick(function () {
                insertTable = new Handsontable(document.getElementById('insertTable'), {
                    fillHandle: false,
                    stretchH: 'all',
                    colHeaders: true,
                    minRows: 20,
                    height: 505,
                    manualColumnResize: true,
                });
                recordTable = new Handsontable(document.getElementById('recordTable'),{
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
                editTable = new Handsontable(document.getElementById('editTable'), {
                   fillHandle: false,
                   manualColumnResize: true,
                   colHeaders: true,
                   stretchH: 'all',
                   maxRows: 1,
                   height: 70,
                });
                app.loadRecord();
                setInterval(function () {
                    app.rebindClickDelete();
                    app.rebindClickEdit()
                }, 100)
            });
        }
    });

</script>
</body>
</html>
<?php $con->close();?>