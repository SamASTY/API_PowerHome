<?php
// http://localhost/powerhome_server/getAllHabitat.php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Auth check (kept as you already do it)
$user_id = getAuthenticatedUserId($db_con);

$stmt = mysqli_prepare($db_con, "SELECT id, floor, area,id_user FROM Habitat");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$habitats = [];
while ($row = mysqli_fetch_assoc($result)) {
    $habitats[] = $row;
}

mysqli_stmt_close($stmt);

if (count($habitats) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No habitat found']);
    mysqli_close($db_con);
    exit;
}

echo json_encode($habitats);
mysqli_close($db_con);