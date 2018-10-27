<?php
$con_info = explode(',', $_COOKIE['funnysql']);
$con = new mysqli($con_info[0],$con_info[2], $con_info[3],'',$con_info[1]);
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

        $temp = array('text'=> $name,'icon'=> './res/table.ico','a_attr'=> array('id'=> 'table-'.$value.'-'.$name));
        array_push($children, $temp);

    }
    $result->free_result();


    $temp = array('text'=> $value, 'icon'=>'./res/database.ico', 'a_attr'=> array('id'=> 'database-'.$value), 'children'=>$children);
    array_push($output,$temp);
}

echo json_encode($output);