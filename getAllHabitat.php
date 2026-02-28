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
    // If this endpoint should be public/admin-only, keep auth but don't filter by user.
    // Remove this line if you truly want it public.
    getAuthenticatedUserId($db_con);

    $sql = "
       SELECT
            u.firstname AS name,
            u.lastname AS lastname,
            h.id AS habitat_id,
            h.id_user AS id_user,
            h.floor AS habitat_floor,
            h.area AS habitat_area,
        
            a.id AS appliance_id,
            ta.name AS appliance_type,
            a.name AS appliance_name,
            a.reference AS appliance_reference,
            a.wattage AS appliance_wattage
        FROM Habitat h
                 LEFT JOIN Appliance a ON a.id_habitat = h.id
                 INNER JOIN appliancetype ta ON ta.id = a.id_type
                 INNER JOIN User u ON u.id = h.id_user
        ORDER BY lastname
    ";

    $stmt = mysqli_prepare($db_con, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $habitatsById = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $hid = (int)$row['habitat_id'];

        if (!isset($habitatsById[$hid])) {
            $habitatsById[$hid] = [
                'id' => $hid,
                'name' => $row['name'],
                'lastname' => $row['lastname'],
                'id_user' => (int)$row['id_user'],
                'floor' => (int)$row['habitat_floor'],
                'area' => (int)$row['habitat_area'],
                'appliances' => []
            ];
        }

        // appliance_id will be NULL when a habitat has no appliances (because LEFT JOIN)
        if ($row['appliance_id'] !== null) {
            $habitatsById[$hid]['appliances'][] = [
                'id' => (int)$row['appliance_id'],
                'type'=> $row['appliance_type'],
                'name' => $row['appliance_name'],
                'reference' => $row['appliance_reference'],
                'wattage' => (int)$row['appliance_wattage']
            ];
        }
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true,
        'data' => array_values($habitatsById)
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Internal server error'
        ]
    ]);
} finally {
    if (isset($db_con) && $db_con instanceof mysqli) {
        mysqli_close($db_con);
    }
}