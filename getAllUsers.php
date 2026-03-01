<?php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
try {
    getAuthenticatedUserId($db_con);

    $sql = "
SELECT
    h.id        AS habitat_id,
    h.id_user   AS id_user,
    h.floor     AS floor,
    h.area      AS area,

    u.firstname AS name,
    u.lastname  AS lastname

FROM Habitat h
LEFT JOIN User u ON u.id = h.id_user
ORDER BY u.id
";

    $result = mysqli_query($db_con, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => mysqli_error($db_con)]);
        mysqli_close($db_con);
        exit;
    }

    $habitatsById = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $hid = intval($row['habitat_id']);

        if (!isset($habitatsById[$hid])) {
            $habitatsById[$hid] = [
                'id' => $hid,
                'name' => $row['name'] ?? '',
                'lastname' => $row['lastname'] ?? '',
                'id_user' => isset($row['id_user']) ? intval($row['id_user']) : null,
                'floor' => isset($row['floor']) ? intval($row['floor']) : 0,
                'area' => isset($row['area']) ? floatval($row['area']) : 0,
            ];
        }


    }

    $data = array_values($habitatsById);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

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