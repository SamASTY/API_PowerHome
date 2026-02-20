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

$user_id = getAuthenticatedUserId($db_con);

// My habitat consumption
$stmt = mysqli_prepare($db_con,
    "SELECT COALESCE(SUM(a.wattage), 0) AS total_wattage
     FROM Appliance a
     JOIN Habitat h ON h.id = a.id_habitat
     WHERE h.id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result           = mysqli_stmt_get_result($stmt);
$my_consumption   = (int) mysqli_fetch_assoc($result)['total_wattage'];
mysqli_stmt_close($stmt);

// Residence total consumption
$result               = mysqli_query($db_con,
    "SELECT COALESCE(SUM(wattage), 0) AS total_wattage FROM Appliance");
$residence_total      = (int) mysqli_fetch_assoc($result)['total_wattage'];

// Per-habitat breakdown
$result               = mysqli_query($db_con,
    "SELECT h.id AS habitat_id, h.floor, h.area,
            COALESCE(SUM(a.wattage), 0) AS total_wattage
     FROM Habitat h
     LEFT JOIN Appliance a ON a.id_habitat = h.id
     GROUP BY h.id, h.floor, h.area
     ORDER BY h.id");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db_con)]);
    mysqli_close($db_con);
    exit;
}

$habitats            = [];
$max_habitat_wattage = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $row['total_wattage'] = (int) $row['total_wattage'];
    $habitats[]           = $row;
    if ($row['total_wattage'] > $max_habitat_wattage) {
        $max_habitat_wattage = $row['total_wattage'];
    }
}

echo json_encode([
    'my_habitat_wattage'      => $my_consumption,
    'residence_total_wattage' => $residence_total,
    'max_habitat_wattage'     => $max_habitat_wattage,
    'habitats'                => $habitats,
]);

mysqli_close($db_con);
