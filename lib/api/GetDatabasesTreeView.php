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
$search = array('\/','\"','"{','}"','"[',']"');
$replace = array('/','"','{','}','[',']');
$output = array();
foreach ($databaseName as $value)
{
    $children = array();
    $result = $con->query(sprintf('SHOW TABLES FROM %s', $value));
    while($row = $result->fetch_assoc())
    {
        $name = $row[sprintf('Tables_in_%s', $value)];

        $temp = json_encode(array('text'=> $name,'icon'=> './res/table.ico','a_attr'=> json_encode(array('id'=> 'table-'.$name))));
        $temp = str_replace($search,$replace, $temp);
        array_push($children, $temp);

    }
    $temp = json_encode($children, JSON_UNESCAPED_SLASHES);
    $temp = str_replace($search,$replace, $temp);

    $result->free_result();
//    $result = json_encode(array('text'=> $value, 'icon'=>'./res/database.ico', 'a_attr'=> json_encode(array('id'=> 'database-'.$value)), 'children'=>json_encode($children)));
//    echo str_replace('\\', '', $result);


    $temp = json_encode(array('text'=> $value, 'icon'=>'./res/database.ico', 'a_attr'=> json_encode(array('id'=> 'database-'.$value)), 'children'=>$temp), JSON_UNESCAPED_SLASHES);
    $temp = str_replace($search,$replace, $temp);
    array_push($output,$temp);
}

echo str_replace($search,$replace, json_encode($output));