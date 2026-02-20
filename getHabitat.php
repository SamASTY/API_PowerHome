<?php
//http://localhost/powerhome_server/getHabitat.php
$db_host = "localhost";
$db_uid = "Admin";
$db_pass = "AZERTY123qs";
$db_name = "powerhome_bd";
$db_con = mysqli_connect($db_host, $db_uid, $db_pass, $db_name);
$sql = "SELECT * FROM powerhome_bd.habitat"; //Add WHERE token='X';
$result = mysqli_query($db_con, $sql);
while ($row = mysqli_fetch_assoc($result)) $output[] = $row;
mysqli_close($db_con);
header('Content-Type: application/json');
print(json_encode($output));
http_response_code(200);