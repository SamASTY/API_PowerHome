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
    "SELECT ts.id, ts.begin_time, ts.end_time, ts.max_wattage,
            COALESCE(SUM(a.wattage), 0) AS booked_wattage
     FROM TimeSlot ts
     LEFT JOIN Booking b    ON b.id_time_slot = ts.id
     LEFT JOIN Appliance a  ON a.id = b.id_appliance
     GROUP BY ts.id, ts.begin_time, ts.end_time, ts.max_wattage
     ORDER BY ts.begin_time");

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db_con)]);
    mysqli_close($db_con);
    exit;
}

$time_slots = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['booked_wattage'] = (int) $row['booked_wattage'];
    $row['max_wattage']    = (int) $row['max_wattage'];
    $percentage            = $row['max_wattage'] > 0
        ? ($row['booked_wattage'] / $row['max_wattage']) * 100
        : 0;
    if ($percentage <= 30) {
        $indicator = 'green';
    } elseif ($percentage <= 70) {
        $indicator = 'orange';
    } else {
        $indicator = 'red';
    }
    $row['consumption_percentage'] = round($percentage, 2);
    $row['indicator']              = $indicator;
    $time_slots[]                  = $row;
}

echo json_encode($time_slots);
mysqli_close($db_con);
