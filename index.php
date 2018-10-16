<?php 
if(!array_key_exists('funnysql', $_COOKIE))
	header("location: http://10.242.8.182/funnysql/login");
else
    echo '恭喜，成功进入！';