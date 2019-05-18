<?php
    error_reporting(0);
    include("./lib/settings.php");

    session_start([
        "gc_maxlifetime"=> 60 * 60 * 24 * 7,
        "cookie_lifetime"=> 60 * 60 * 24 * 7
    ]);
    setcookie("PHPSESSID", session_id(), time() + 60 * 60 * 24 * 7, "/");
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo $PAGE_TITLE_LOGIN;?></title>
	<link rel="shortcut icon" href="<?php echo $PAGE_ICON;?>">
    <link rel="stylesheet" href="./lib/google-fonts/spicyrice/css.css">

</head>
<?php  session_destroy(); ?>
<style>
    @keyframes fadeInUp{
        0%{
            opacity:0;
            transform:translateY(20px)
        }
        100%{
            opacity:1;
            transform:translateY(0)
        }
    }
    *{
        box-sizing:border-box
    }
    body{
        background:url("./res/bgImage.jpg") no-repeat fixed center ;
        background-size:cover;
        width:100%
    }
    .main{
        position:absolute;
        margin:auto;
        top:0;
        left:0;
        bottom:0;
        right:0;
        height:500px;
        text-align:center;
    }
    a{
        text-decoration:none;
        color:black;
    }
    header{
        width:100%;
        text-align:center;
        font-size:65px;
        font-family: 'Spicy Rice', cursive;
        margin-bottom:90px;
    }
    form{
        max-width:360px;
        margin:0 auto;

    }
    .input{
        width:100%;
    }
    input{
        height:40px;
        width:100%;
        border:0;
        font-size:12px;
        background:rgba(255,255,255,.7);
        padding:8px 12px;
        margin-bottom:10px
    }
    input[type='submit']{
        cursor: pointer;
        background:#f5bc22;
        font-size: 16px;
        outline: none;
    }
    input[type='submit']:hover {
        background: #ff7300;
    }
    input[type='submit']:active{
        background:red
    }
    input[type='focus']:focus{
        background:blue
    }
    #msg{
        text-align:center;
        color: #ffffff;
        font-size:14px;
        z-index:999;
        position: fixed;
        padding: 10px;
        top:0;
        left: 0;
        right: 0;
        margin: 0 auto;
        width: 500px;
    }
    #msg-close {
        float: right;
        color: #eaeaea;
        cursor: pointer;
    }
    #msg-close:hover {
        color: #dedede;
    }
    #msg-close:active {
        color: #ffffff;
    }

    .msgShow{
        animation-name:fadeInUp;
        animation-duration:1s;
    }

    .errorMsg{
        background: red;
    }


</style>
<body>
    <div id="app">
        <div id="msg" v-show="isShowError" style="display: none;" :class="{'msgShow':isShowError, 'errorMsg':isShowError}">
            <span id="msg-body">{{ message }}</span>
            <span id="msg-close" style="cursor: pointer;" @click="closeError">X</span>
        </div>
        <div class="main">
            <header class="header"><a href="./"><?php echo $NAME;?></a></header>
            <form @submit.prevent="submit">
                <input type="text" name="host" placeholder="IP地址" class="input" v-model="host">
                <input type="number" name="port" placeholder="端口" class="input" v-model="port">
                <input type="text" name="userName" placeholder="用户名" class="input" v-model="userName">
                <input type="password" name="password" placeholder="密码" class="input" v-model="password">
                <input type="submit" class="submit" value="连接">
            </form>
        </div>
    </div>

    <script src="https://cdn.bootcss.com/vue/2.6.10/vue.min.js"></script>
    <script src="https://cdn.bootcss.com/axios/0.19.0-beta.1/axios.min.js"></script>
	<script src="./lib/jquery.min.js"></script>
	<script>
        let vm = new Vue({
            el: "#app",
            data: {
                isShowError: false,
                message: null,

                host: null,
                port: 3306,
                userName: null,
                password: null,
                focusHost: true
            },
            methods: {
                showError: function (error) {
                    vm.message = error;
                    vm.isShowError = true;
                    setTimeout(function () {
                        vm.closeError();
                    }, 1500)
                },
                closeError: function () {
                    vm.isShowError = false;
                    vm.message = null;
                },
                submit: function () {
                    if (!vm.host) {
                        vm.showError("IP地址不能为空！");
                        vm.elFocus("input[name='host']");
                    } else {
                        const patternHost = /^((25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d))|localhost$/;
                        if (!patternHost.test(vm.host)) {
                            vm.showError("IP地址格式错误，请重新输入!");
                            vm.elFocus("input[name='host']").elSelect("input[name='host']");
                        } else {
                            if (!vm.port) {
                                vm.showError("端口不能为空！");
                                vm.elFocus("input[name='port']");
                            } else {
                                const patternPort = /^\d|[1-9]\d{1,3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5]$/g;
                                if (!patternPort.test(vm.port)) {
                                    vm.showError("端口错误，请重新输入!");
                                    vm.elFocus("input[name='port']").elSelect("input[name='port']");
                                } else {
                                    if (!vm.userName) {
                                        vm.showError("用户名不能为空！");
                                    } else {
                                        let params = new URLSearchParams();
                                        params.append("type", "1");
                                        params.append("host", vm.host);
                                        params.append("port", vm.port);
                                        params.append("userName", vm.userName);
                                        params.append("password", vm.password);
                                        axios({
                                            method: "POST",
                                            url: "./lib/Processing.php",
                                            data: params.toString()
                                        }).then(function (response) {
                                            let data = response.data;
                                            if (data.success) {
                                                window.location.href = ".";
                                            } else {
                                                vm.showError(data.msg);
                                            }
                                        }).catch(function (error) {
                                            vm.showError(error);
                                        })

                                    }
                                }
                            }
                        }
                    }
                },
                elFocus: function (el) {
                    document.querySelector(el).focus();
                    return vm;
                },
                elSelect: function (el) {
                    document.querySelector(el).select();
                    return vm;
                }
            },
            created: function() {
                this.$nextTick(function () {
                    vm.elFocus("input[name='host']");
                })
            },
        });

	</script>
</body>
</html>