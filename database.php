<?php
include('./lib/settings.php');
session_start([
    "gc_maxlifetime"=> 60 * 60 * 24 * 7,
    "cookie_lifetime"=> 60 * 60 * 24 * 7
]);
if(!array_key_exists('host', $_SESSION))
    header("Location: ./login.php");
//$con_info = json_decode(base64_decode($_COOKIE['session']));
//$con = new mysqli($_SESSION["host"], $_SESSION["userName"], $_SESSION["password"], '', $_SESSION["port"]);
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $PAGE_TITLE_DATABASE;?></title>
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
        <a href="./database.php" id="nav-database" class="active">
            <img src="./res/database.png"  class="icon database icon-inactive" >
            <img src="./res/database_active.png"  class="icon database icon-active">
            &nbsp;数据库
        </a>
        <a href="./new-delete-table.php" id="nav-table">
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
            <div class="block-head">新建数据库</div>
            <div class="block-body">
                <form class="left-form" @submit.prevent="createDb">
                    <input type="text" name="newDatabaseName"  class="input" id="newDatabaseName"
                           title="数据库名" required placeholder="数据库名" v-model="newDbName">
                    <select name="db_collation" title="数据库编码" class="input" v-model="newDbCollation">
                        <optgroup label="gbk" title="GBK Simplified Chinese">
                            <option value="gbk_bin" title="简体中文, 二进制">gbk_bin</option>
                            <option value="gbk_chinese_ci" title="简体中文, 不区分大小写">gbk_chinese_ci</option>
                        </optgroup>
                        <optgroup label="utf8" title="UTF-8 Unicode">
                            <option value="utf8_bin" title="Unicode, 二进制">utf8_bin</option>
                            <option value="utf8_general_ci" title="Unicode, 不区分大小写">utf8_general_ci</option>
                            <option value="utf8_general_mysql500_ci" title="Unicode (MySQL 5.0.0), 不区分大小写">utf8_general_mysql500_ci</option>
                            <option value="utf8_unicode_520_ci" title="Unicode (UCA 5.2.0), 不区分大小写">utf8_unicode_520_ci</option>
                            <option value="utf8_unicode_ci" title="Unicode, 不区分大小写" selected>utf8_unicode_ci</option>
                        </optgroup>
                        <optgroup label="utf8mb4" title="UTF-8 Unicode">
                            <option value="utf8mb4_bin" title="Unicode (UCA 4.0.0), 二进制">utf8mb4_bin</option>
                            <option value="utf8mb4_general_ci" title="Unicode (UCA 4.0.0), 不区分大小写">utf8mb4_general_ci</option>
                            <option value="utf8mb4_unicode_520_ci" title="Unicode (UCA 5.2.0), 不区分大小写">utf8mb4_unicode_520_ci</option>
                            <option value="utf8mb4_unicode_ci" title="Unicode (UCA 4.0.0), 不区分大小写">utf8mb4_unicode_ci</option>
                        </optgroup>
                    </select>
                    <div>
                        <button id="submitCreate" style="margin-bottom: 5px" type="submit">&nbsp;&nbsp;创建&nbsp;&nbsp;</button>
                    </div>

                </form>
            </div>
        </div>
        <div class="block" style="margin-top: 30px">
            <div class="block-head">修改数据库编码</div>
            <div class="block-body">
                <form  class="left-form" @submit.prevent="changeDb">
                    <select name="dbList" title="数据库名" class="input" v-model="changeDbName">
                        <option :value="db" v-for="db in dbList">{{ db }}</option>
                    </select>
                    <select name="change_db_collation" class="input" title="新的数据库编码" v-model="changeDbCollation">
                        <optgroup label="gbk" title="GBK Simplified Chinese">
                            <option value="gbk_bin" title="简体中文, 二进制">gbk_bin</option>
                            <option value="gbk_chinese_ci" title="简体中文, 不区分大小写">gbk_chinese_ci</option>
                        </optgroup>
                        <optgroup label="utf8" title="UTF-8 Unicode">
                            <option value="utf8_bin" title="Unicode, 二进制">utf8_bin</option>
                            <option value="utf8_general_ci" title="Unicode, 不区分大小写">utf8_general_ci</option>
                            <option value="utf8_general_mysql500_ci" title="Unicode (MySQL 5.0.0), 不区分大小写">utf8_general_mysql500_ci</option>
                            <option value="utf8_unicode_520_ci" title="Unicode (UCA 5.2.0), 不区分大小写">utf8_unicode_520_ci</option>
                            <option value="utf8_unicode_ci" title="Unicode, 不区分大小写" selected>utf8_unicode_ci</option>
                        </optgroup>
                        <optgroup label="utf8mb4" title="UTF-8 Unicode">
                            <option value="utf8mb4_bin" title="Unicode (UCA 4.0.0), 二进制">utf8mb4_bin</option>
                            <option value="utf8mb4_general_ci" title="Unicode (UCA 4.0.0), 不区分大小写">utf8mb4_general_ci</option>
                            <option value="utf8mb4_unicode_520_ci" title="Unicode (UCA 5.2.0), 不区分大小写">utf8mb4_unicode_520_ci</option>
                            <option value="utf8mb4_unicode_ci" title="Unicode (UCA 4.0.0), 不区分大小写">utf8mb4_unicode_ci</option>
                        </optgroup>
                    </select>
                    <div>
                        <button id="submitChange" type="submit" style="margin-bottom: 5px" >&nbsp;&nbsp;修改&nbsp;&nbsp;</button>
                    </div>
                </form>

            </div>
        </div>
<!--        <div class="block" style="margin-top: 30px;">-->
<!--            <div class="block-head">备份数据库</div>-->
<!--            <div class="block-body">-->
<!--                <select name="backup_db" title="数据库名" class="input dbList" ></select>-->
<!--                <button id="backup">&nbsp;&nbsp;备份&nbsp;&nbsp;</button>-->
<!--            </div>-->
<!--        </div>-->
    </div>
    <div class="right" >
        <div class="block">
            <div class=" block-head">数据库列表<img id="refresh" src="./res/refresh.png" width="16px" height="16px" title="刷新" @click="loadDbTable"/></div>
            <div class="block-body">
                <div id="databaseTable"></div>
            </div>
        </div>


    </div>
</div>
<script src="./lib/jquery.min.js"></script>
<script src="./lib/handsontable/handsontable.full.min.js"></script>
<script src="./lib/vue.min.js"></script>
<script src="./lib/axios.min.js"></script>
<script src="./lib/js.js"></script>

<script>
    let dbTable;
    let app = new Vue({
        el: "#app",
        data: {
            newDbName: null,
            newDbCollation: "utf8_unicode_ci",
            dbList: null,
            changeDbName: null,
            changeDbCollation: "utf8_unicode_ci",
        },
        methods: {
            createDb: function () {
                if (app.newDbName) {
                    axios.get('./lib/Processing.php?type=1&databaseName=' + app.newDbName
                        + '&collationName=' + app.newDbCollation)
                        .then(function (response) {
                            let data = response.data;
                            if (data.success) {
                                common.showSuccess(data.msg);
                                app.loadDbTable();
                            } else {
                                common.showError(data.msg);
                            }
                        })
                        .catch(function (error) {
                            common.showError(error);
                        })
                }
            },
            changeDb: function () {
                axios.get('./lib/Processing.php?type=11&db=' + app.changeDbName + '&collation=' + app.changeDbCollation)
                    .then(function (response) {
                        let data = response.data;
                        if (data.success) {
                            common.showSuccess(data.msg);
                            app.loadDbTable();
                        } else {
                            common.showError(data.msg);
                        }
                    })
                    .catch(function (error) {
                        common.showError(error);
                    })
            },
            loadDbTable: function () {
                common.showLoader();
                axios.get('./lib/Processing.php?type=9')
                    .then(function (response) {
                        let data = response.data;
                        if (data.success) {
                            dbTable.loadData(data.data);
                            app.changeDbName = data.dbList[0];
                            app.dbList = data.dbList;
                        }
                    })
                    .catch(function (error) {
                        common.showError(error);
                    })
                    .finally(function () {
                        common.hideLoader();
                    })
            },
            rebindClickDelete: function () {
                $('.deleteDatabase').unbind("click").click(function () {
                    let db = $(this).attr('db');
                    if(confirm('确定删除数据库'+ db + '吗？'))
                        axios.get('./lib/Processing.php?type=2&db=' + db)
                            .then(function (response) {
                                let data = response.data;
                                if (data.success) {
                                    common.showSuccess(data.msg);
                                    app.loadDbTable();
                                } else {
                                    common.showError(data.msg);
                                }
                            })
                            .catch(function (error) {
                                common.showError(error);
                            });

                });
            }
        },
        mounted: function () {
            this.$nextTick(function () {
                dbTable = new Handsontable(document.getElementById('databaseTable'), {
                    fillHandle: false,
                    stretchH: 'all',
                    colHeaders: ['', '数据库名', '数据库编码'],
                    rowHeights: 30,
                    columnSorting: {
                        indicator: true
                    },
                    disableVisualSelection: true,
                    columns: [
                        {
                            width: 30,
                            className: 'htCenter htMiddle',
                            renderer: 'html'
                        },
                        {
                            readOnly: true,
                            className: 'htCenter htMiddle',
                            renderer: 'html'
                        },{
                            readOnly: true,
                            className: 'htCenter htMiddle',
                        }
                    ]
                });
                app.loadDbTable();
                setInterval(function () {
                    app.rebindClickDelete();
                },1000)
            });
        },
        updated: function () {

        }
    });
</script>
</body>
</html>
