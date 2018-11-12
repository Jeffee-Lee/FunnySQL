<?php
include ("../settings.php");
date_default_timezone_set('PRC');
if(isset($_GET['db']) && !empty($_GET['db'])) {
    $db = $_GET['db'];
    $con_info = json_decode(base64_decode($_COOKIE['session']));
    $user = $con_info->userName;
    $host = $con_info->host;
    $port = $con_info->port;
    $password = $con_info->password;
    $fileName = "Backup_{$db}_".date("Y_m_d_H_i_s").'.sql';
    $cmd = "mysqldump.exe --user=$user --host=$host --port=$port --password=$password $db";

    header( "Content-Type: application/octet-stream");
    header( 'Content-Disposition: attachment; filename="' . $fileName . '"' );
    passthru($cmd);
    exit(0);

}