<?php
if (isset($_GET['db']) && !empty($_GET['db'])) {
    $dba = $_GET['db'];
    session_start();
    $con = new mysqli($_SESSION["host"], $_SESSION["userName"], $_SESSION["password"], '', $_SESSION["port"]);

    $tables =array();
    $result = $con->query(sprintf('SHOW TABLES FROM `%s`',$dba));
    if ($result) {
        while($row = $result->fetch_assoc()) {
            array_push($tables,$row[sprintf('Tables_in_%s',$dba)]);
        }
        $result->free_result();
        echo json_encode($tables);
    }
}
