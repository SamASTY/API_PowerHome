<?php
//http://localhost/powerhome_server/getHabitat.php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user_id = getAuthenticatedUserId($db_con);

$stmt = mysqli_prepare($db_con, "SELECT id, floor, area FROM Habitat WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$habitat = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$habitat) {
    http_response_code(404);
    echo json_encode(['error' => 'No habitat found for this user']);
    mysqli_close($db_con);
    exit;
}

$stmt = mysqli_prepare($db_con,
    "SELECT id, name, reference, wattage FROM Appliance WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appliances = [];
while ($row = mysqli_fetch_assoc($result)) {
    $appliances[] = $row;
}
mysqli_stmt_close($stmt);

$habitat['appliances'] = $appliances;
echo json_encode($habitat);
mysqli_close($db_con);
