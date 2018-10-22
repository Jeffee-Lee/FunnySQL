<?php
$con_info = explode(',', $_COOKIE['funnysql']);
$con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
$result = $con->query('SHOW DATABASES;');
$databaseName = array();
while($row = $result->fetch_assoc())
{
    $databaseName[$row['Database']] = '';
}
$result->close();
$sql = 'SELECT DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE schema_name = "%s"';
foreach (array_keys($databaseName) as $key) {
    $result = $con->query(sprintf($sql, $key));

    if($row = $result->fetch_assoc())
        $databaseName[$key] = $row['DEFAULT_COLLATION_NAME'];
}
$output = array();
foreach ($databaseName as $key => $value) {
    array_push($output, array(false,$key, $value));
}
echo json_encode(array('data' => $output));
$con->close();