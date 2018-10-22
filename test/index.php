<?php
include('../lib/settings.php');
if(!array_key_exists('funnysql', $_COOKIE))
    header("Location: http://10.242.8.182/funnysql/login");

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
    <link href="https://cdn.bootcss.com/jstree/3.3.5/themes/default/style.min.css" rel="stylesheet">
</head>
<style>
</style>
<body style="background: white;">
<div id="jstree_demo_div"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="https://cdn.bootcss.com/jstree/3.3.5/jstree.min.js"></script>

<script>

    $(document).ready(function(){
        $('#jstree_demo_div').jstree({
            'core': {
                'dblclick_toggle': false,
                'themes': {
                    'variant': 'middle'
                },
                'data': [
                    {
                        'text': 'root',
                        'icon': '../res/table.ico'
                    },
                    {
                        'icon': '../res/mysql.ico',
                        'text' : 'Root node with options',
                        'a_attr': {'id': 'database-'},
                        'children' : [ { 'text' : 'Child 1' }, 'Child 2'],
                    }
                ]
            },
        });
    });
</script>
</body>
</html>
