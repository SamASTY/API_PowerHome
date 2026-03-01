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
try {
    getAuthenticatedUserId($db_con);

    $input = json_decode(file_get_contents('php://input'), true);
    $id = trim($input['id'] ?? '');

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'User identifier is required']);
        exit;
    }

    $stmt = mysqli_prepare($db_con,
        "SELECT u.id as user_id, u.firstname as user_firstname,
       u.lastname as user_lastname, u.email as user_email,
       u.phoneNumber as user_phone_number, u.phoneCode as user_phone_code,
            h.id AS habitat_id, h.floor as habitat_floor, h.area as habitat_area
     FROM User u
     LEFT JOIN Habitat h ON h.id_user = u.id
     where u.id = ?");

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

    mysqli_stmt_close($stmt);
} catch
(Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'erreur' => $e->getMessage()
        ]
    ]);
} finally {
    if (isset($db_con) && $db_con instanceof mysqli) {
        mysqli_close($db_con);
    }
}