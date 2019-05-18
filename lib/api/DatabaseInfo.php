<?php
/**
 * User: Lee
 * Date: 5/15/2019
 * Time: 5:58 PM
 */
session_start();
$con = new mysqli($_SESSION["host"], $_SESSION["userName"], $_SESSION["password"], '', $_SESSION["port"]);

$output = array();

$output[0] = mysqli_get_host_info($con);
$output[1] = $con->protocol_version;

if($result = $con->query('SELECT USER()')) {
    $output[2] = mysqli_fetch_array($result)['USER()'];
}
$result->free_result();

if($row = $con->query("select @@basedir as basePath from dual;")->fetch_array(MYSQLI_NUM)) {
    $output[3] = $row[0];
}

if($row = $con->query("show global variables like \"%datadir%\";")->fetch_assoc()) {
    $output[4] = $row["Value"];
}

$output[5] = "&nbsp;";

$result = $con->query(' SHOW VARIABLES LIKE  \'char%\';');
$tempName = array('客户端默认字符集：','连接默认字符集：','数据库默认字符集：','文件系统默认字符集：','结果集默认字符集：','服务器默认字符集：','系统默认字符集：');
$tempIndex = 0;
while($row = $result->fetch_assoc()) {
    if($tempIndex == 7)
        break;
    $output[6 + $tempIndex] = $row['Value'];
//    echo '<tr><td>'.$tempName[$tempIndex].'</td><td>'.$row['Value'].'</td><tr>';
    $tempIndex ++;
}
$result->free_result();
echo json_encode($output);