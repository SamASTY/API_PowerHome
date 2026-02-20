<?php
$db_host = "localhost";
$db_uid = "Admin";
$db_pass = "AZERTY123qs";
$db_name = "powerhome_bd";
$db_con = mysqli_connect($db_host, $db_uid, $db_pass, $db_name);
if (!$db_con) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
mysqli_set_charset($db_con, 'utf8mb4');
