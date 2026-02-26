<?php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

$user_id = getAuthenticatedUserId($db_con);
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    $stmt = mysqli_prepare($db_con,
        "SELECT b.id_appliance, b.id_time_slot, b.order_ref, b.booked_at,
                a.name AS appliance_name, a.reference, a.wattage,
                ts.begin_time, ts.end_time, ts.max_wattage
         FROM Booking b
         JOIN Appliance a ON a.id = b.id_appliance
         JOIN Habitat h   ON h.id = a.id_habitat
         JOIN TimeSlot ts ON ts.id = b.id_time_slot
         WHERE h.id_user = ?
         ORDER BY ts.begin_time");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result   = mysqli_stmt_get_result($stmt);
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($stmt);
    echo json_encode($bookings);

} elseif ($method === 'POST') {

    $input        = json_decode(file_get_contents('php://input'), true);
    $appliance_id = intval($input['id_appliance'] ?? 0);
    $time_slot_id = intval($input['id_time_slot'] ?? 0);

    if ($appliance_id <= 0 || $time_slot_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'id_appliance and id_time_slot are required']);
        mysqli_close($db_con);
        exit;
    }

    // Verify appliance belongs to authenticated user
    $stmt = mysqli_prepare($db_con,
        "SELECT a.id FROM Appliance a
         JOIN Habitat h ON h.id = a.id_habitat
         WHERE a.id = ? AND h.id_user = ?");
    mysqli_stmt_bind_param($stmt, "ii", $appliance_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!mysqli_fetch_assoc($result)) {
        http_response_code(403);
        echo json_encode(['error' => 'Appliance not found or not owned by user']);
        mysqli_stmt_close($stmt);
        mysqli_close($db_con);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Verify time slot exists
    $stmt = mysqli_prepare($db_con, "SELECT id FROM TimeSlot WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $time_slot_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!mysqli_fetch_assoc($result)) {
        http_response_code(404);
        echo json_encode(['error' => 'Time slot not found']);
        mysqli_stmt_close($stmt);
        mysqli_close($db_con);
        exit;
    }
    mysqli_stmt_close($stmt);

    $order_ref = 'REF-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    $stmt      = mysqli_prepare($db_con,
        "INSERT INTO Booking (id_appliance, id_time_slot, order_ref) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $appliance_id, $time_slot_id, $order_ref);
    try {
        mysqli_stmt_execute($stmt);
        http_response_code(201);
        echo json_encode(['message' => 'Booking created', 'order_ref' => $order_ref]);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            http_response_code(409);
            echo json_encode(['error' => 'Booking already exists or could not be created']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Booking could not be created']);
        }
    }
    mysqli_stmt_close($stmt);

} elseif ($method === 'DELETE') {

    $input        = json_decode(file_get_contents('php://input'), true);
    $appliance_id = intval($input['id_appliance'] ?? 0);
    $time_slot_id = intval($input['id_time_slot'] ?? 0);

    if ($appliance_id <= 0 || $time_slot_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'id_appliance and id_time_slot are required']);
        mysqli_close($db_con);
        exit;
    }

    $stmt = mysqli_prepare($db_con,
        "DELETE b FROM Booking b
         JOIN Appliance a ON a.id = b.id_appliance
         JOIN Habitat h   ON h.id = a.id_habitat
         WHERE b.id_appliance = ? AND b.id_time_slot = ? AND h.id_user = ?");
    mysqli_stmt_bind_param($stmt, "iii", $appliance_id, $time_slot_id, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['message' => 'Booking cancelled']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Booking not found or not owned by user']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed']);
    }
    mysqli_stmt_close($stmt);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

mysqli_close($db_con);
