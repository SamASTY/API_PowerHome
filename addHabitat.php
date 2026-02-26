<?php
//http://localhost/powerhome_server/addHabitat.php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id_user = getAuthenticatedUserId($db_con);


$input = json_decode(file_get_contents('php://input'), true);
$floor = intval($input['floor'] ?? -1);
$area = intval($input['area'] ?? -1);


if ($floor < 0 || $area < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'area and floor are required']);
    mysqli_close($db_con);
    exit;
}

if ($id_user < 0) {
    http_response_code(400);
    echo json_encode(['error' => '$id_user is required']);
    mysqli_close($db_con);
    exit;
}


$stmt = mysqli_prepare(
    $db_con,
    "SELECT COUNT(*) AS cnt
     FROM User u
     INNER JOIN Habitat h ON h.id_user = u.id
     WHERE h.id_user = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$cnt = (int)($row['cnt'] ?? 0);

if ($cnt != 0) { // or: if ($cnt > 0)
    http_response_code(404);
    echo json_encode(['error' => 'existing habitat for this user']);
    mysqli_close($db_con);
    exit;
}

$stmt = mysqli_prepare($db_con,
    "INSERT INTO Habitat (id_user, floor, area) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iii", $id_user, $floor, $area);
mysqli_stmt_execute($stmt);
mysqli_close($db_con);
