<?php
date_default_timezone_set('PRC');
if(isset($_GET['db']) && !empty($_GET['db']) && isset($_GET['tb']) && !empty($_GET['tb'])) {
    $db = $_GET['db'];
    $tb = $_GET['tb'];
    session_start();
//    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $user =  $_SESSION["userName"];
    $host =  $_SESSION["host"];
    $port =  $_SESSION["port"];
    $password =  $_SESSION["password"];
    $fileName = "Backup_{$db}_{$tb}_".date("Y_m_d_H_i_s").'.sql';
    $cmd = "mysqldump --user=$user --host=$host --port=$port --password=$password $db $tb";

    header( "Content-Type: application/octet-stream");
    header( 'Content-Disposition: attachment; filename="' . $fileName . '"' );
    passthru($cmd);
    exit(0);
}