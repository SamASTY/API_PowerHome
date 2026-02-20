<?php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

getAuthenticatedUserId($db_con);

$result = mysqli_query($db_con,
    "SELECT u.id, u.firstname, u.lastname, u.email,
            h.id AS habitat_id, h.floor, h.area
     FROM User u
     LEFT JOIN Habitat h ON h.id_user = u.id
     ORDER BY u.lastname, u.firstname");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db_con)]);
    mysqli_close($db_con);
    exit;
}

$residents = [];
while ($row = mysqli_fetch_assoc($result)) {
    $residents[] = $row;
}

echo json_encode($residents);
mysqli_close($db_con);
