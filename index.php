<?php
include('./lib/settings.php');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/codemirror.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/addon/hint/show-hint.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/theme/dracula.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/funnysql/lib/codemirror/theme/blackboard-black.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/theme/blackboard.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/theme/3024-day.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/theme/3024-night.min.css" rel="stylesheet">
</head>
<style>
    * {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    .CodeMirror {
        font-size: 16px;
    }

</style>
<body>
    <textarea class="form-control" id="code" name="code"></textarea>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/mode/sql/sql.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/addon/hint/show-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.38.0/addon/hint/sql-hint.min.js"></script>
    <script>
        let editor = CodeMirror.fromTextArea(document.getElementById("code"), {
            mode: {
                name: "text/x-mysql"
            },
            lineNumbers: true,
            matchBrackets:true,
            extraKeys: {"Tab": "autocomplete"},
            theme: '3024-night'
        });
        editor.setSize('800px', '500px');
    </script>
</body>
</html>
