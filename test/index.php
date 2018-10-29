<?php
include('../lib/settings.php');
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
    <link href="<?php echo $path?>lib/handsontable/handsontable.full.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<style>
    body {
        background-color: #eee;
    }
    .nav {
        display: block;
        font: 13px Helvetica, Tahoma, serif;
        margin: 0;
        padding: 0;
    }
    .nav li {
        display: inline-block;
        list-style: none;
    }
    .nav .button-dropdown {
        position: relative;
    }
    .nav li a{
        display: block;
        color: #333;
        background-color: #fff;
        padding: 10px 20px;
        text-decoration: none;
    }

    .nav li a span {
        display: inline-block;
        margin-left: 5px;
        font-size: 10px;
        color: #999;
    }

    .nav li a:hover, .nav li a.dropdown-toggle.active {
        background-color: #289dcc;
        color: #fff;
    }

    .nav li a:hover span, .nav li a.dropdown-toggle.active span {
        color: #fff;
    }

    .nav li .dropdown-menu {
        display: none;
        position: absolute;
        left: 0;
        padding: 0;
        margin: 0;
        margin-top: 3px;
        text-align: left;
    }

    .nav li .dropdown-menu.active {
        display: block;
    }

    .nav li .dropdown-menu a {
        width: 150px;
    }

</style>
<body>
<div class="head">

    <ul class="nav">
        <li><a href="javascript:void(0)" class="fa fa-home">&nbsp;&nbsp;概述</a></li>
        <li><a href="javascript:void(0)" class="fa fa-database">&nbsp;&nbsp;数据库</a></li>
        <li class="button-dropdown">
            <a href="javascript:void(0)" class="dropdown-toggle">
                数据表 <span>▼</span>
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="javascript:void(0)">
                        Drop Item 1
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        Drop Item 2
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        Drop Item 3
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)">
                No DropDown
            </a>
        </li>
        <li><a href="javascript:void(0)" class="fa fa-sign-out">&nbsp;&nbsp;离开</a></li>
    </ul>
</div>
<script src="<?php echo $path;?>lib/jquery.min.js"></script>
<script>
    $(document).ready(function (e) {

        $(".dropdown-toggle").click(function () {
            let t = $(".dropdown-menu").is(":hidden");
            $(".dropdown-menu").hide();
            $(".dropdown-toggle").removeClass("active");
            if (t) {
                $(this).addClass("active");
                $(".dropdown-menu").toggle();
            }
        });
        $(document).bind('click',function (t) {
            console.log();
            if(!$(t.target).parents().hasClass('button-dropdown')) {
                $('.dropdown-menu').hide();
                $('.dropdown-toggle').removeClass("active");
            }

        });
    });
</script>
</body>
</html>