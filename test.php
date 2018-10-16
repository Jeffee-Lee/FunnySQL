<?php
error_reporting(0);
$success = true;
if(isset($_POST['host']) and !empty($_POST['host']))
    $host = $_POST['host'];
else
    $success = false;
if(isset($_POST['port']) and !empty($_POST['port']))
    $port = $_POST['port'];
else
    $success = false;
if(isset($_POST['userName']) and !empty($_POST['userName']))
    $userName = $_POST['userName'];
else
    $success = false;
if(isset($_POST['password']) and !empty($_POST['password']))
    $password = $_POST['password'];
else
    $success = false;

if($success){
    $con = new mysqli($host, $userName, $password,'',$port);
    if(mysqli_connect_errno()) {
        $success = false;
    }
    $con->close();
}
$msg  = '';
if($success) {
    $msg = $host.','.$port.','.$userName.','.$password;
}
echo json_encode(array('success' => $success, 'msg' => $msg));