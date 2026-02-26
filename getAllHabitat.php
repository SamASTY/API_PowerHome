<?php
// http://localhost/powerhome_server/getAllHabitats.php
global $db_con;

require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Method not allowed'
        ]
    ]);
    exit;
}

try {
    // Auth (required)
    $user_id = getAuthenticatedUserId($db_con);

    $stmt = mysqli_prepare($db_con, "SELECT id, floor, area FROM Habitat");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $habitats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // ensure numeric fields are numbers in JSON (optional but nice)
        $row['id'] = (int)$row['id'];
        $row['floor'] = (int)$row['floor'];
        $row['area'] = (int)$row['area'];
        $habitats[] = $row;
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true,
        'data' => $habitats
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Internal server error'
        ]
        // Don't expose $e->getMessage() in production
    ]);
} finally {
    if (isset($db_con) && $db_con instanceof mysqli) {
        mysqli_close($db_con);
    }
}