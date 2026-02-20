<?php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

$user_id = getAuthenticatedUserId($db_con);
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    $stmt = mysqli_prepare($db_con,
        "SELECT id, name, reference, wattage FROM Appliance WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result     = mysqli_stmt_get_result($stmt);
    $appliances = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $appliances[] = $row;
    }
    mysqli_stmt_close($stmt);
    echo json_encode($appliances);

} elseif ($method === 'POST') {

    $input     = json_decode(file_get_contents('php://input'), true);
    $name      = trim($input['name'] ?? '');
    $reference = trim($input['reference'] ?? '');
    $wattage   = intval($input['wattage'] ?? 0);

    if (empty($name) || empty($reference) || $wattage <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, reference and wattage (> 0) are required']);
        mysqli_close($db_con);
        exit;
    }

    $stmt = mysqli_prepare($db_con,
        "INSERT INTO Appliance (name, reference, wattage, id_user) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssii", $name, $reference, $wattage, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $id = mysqli_insert_id($db_con);
        http_response_code(201);
        echo json_encode([
            'id'        => $id,
            'name'      => $name,
            'reference' => $reference,
            'wattage'   => $wattage,
        ]);
    } else {
        http_response_code(409);
        echo json_encode(['error' => 'Could not add appliance. The reference may already exist.']);
    }
    mysqli_stmt_close($stmt);

} elseif ($method === 'DELETE') {

    $input        = json_decode(file_get_contents('php://input'), true);
    $appliance_id = intval($input['id'] ?? 0);

    if ($appliance_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Appliance id is required']);
        mysqli_close($db_con);
        exit;
    }

    $stmt = mysqli_prepare($db_con,
        "DELETE FROM Appliance WHERE id = ? AND id_user = ?");
    mysqli_stmt_bind_param($stmt, "ii", $appliance_id, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['message' => 'Appliance removed']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Appliance not found or not owned by user']);
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
