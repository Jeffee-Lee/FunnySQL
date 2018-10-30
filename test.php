<?php
error_reporting(0);
include "./lib/settings.php";
$success = true;

function getPost($name, &$status) {
    if(isset($_POST[$name]) and !empty($_POST[$name]))
        return $_POST[$name];
    else
        $status = false;
    return null;
}

$host = getPost('host', $success);
$port = getPost('port', $success);
$userName = getPost('userName', $success);
$password = $_POST['password'];

$msg  = '';

if($success){
    $con = new mysqli($host, $userName, $password,'',$port);
    if($con->connect_errno) {
        $success = false;
        $msg = $con->connect_error;
    }
    $con->close();
}
if($success) {
    $msg = $host.','.$port.','.$userName.','.$password;
    setcookie('funnysql',$msg,time()+60*60*24,$path);
}
echo json_encode(array('success' => $success, 'msg' => $msg));