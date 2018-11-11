<?php
$con_info = json_decode(base64_decode($_COOKIE['session']));
$con = new mysqli($con_info->host,$con_info->userName, $con_info->password,'',$con_info->port);

$result = $con->query('SHOW DATABASES;');
$databaseName = array();
while($row = $result->fetch_assoc())
{
    array_push($databaseName, $row['Database']);
}
$result-> free_result();
$output = array();
foreach ($databaseName as $value)
{
    $children = array();
    $result = $con->query(sprintf('SHOW TABLES FROM %s', $value));
    while($row = $result->fetch_assoc())
    {
        $name = $row[sprintf('Tables_in_%s', $value)];

        $temp = array('text'=> $name,'icon'=> './res/table-tree.png','a_attr'=> array('id'=> 'table-'.$value.'-'.$name));
        array_push($children, $temp);

    }
    $result->free_result();


    $temp = array('text'=> $value, 'icon'=>'./res/database-tree.png', 'a_attr'=> array('id'=> 'database-'.$value), 'children'=>$children);
    array_push($output,$temp);
}

echo json_encode($output);