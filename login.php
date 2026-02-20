<?php
$db_host = "localhost";
$db_uid = "Admin";
$db_pass = "AZERTY123qs";
$db_name = "powerhome_bd";

$db_con = mysqli_connect($db_host, $db_uid, $db_pass, $db_name);
if (!$db_con) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

// Get POST data (assuming from Android app)
$input = json_decode(file_get_contents('php://input'), true);
$email = mysqli_real_escape_string($db_con, $input['email'] ?? '');
$password = mysqli_real_escape_string($db_con, $input['password'] ?? '');


$sql = "SELECT token, expired_at FROM powerhome_bd.user WHERE email='$email' AND password='$password'";
$result = mysqli_query($db_con, $sql);

if (!$result) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($db_con)]);
    exit;
}

$row = mysqli_fetch_assoc($result);
header('Content-Type: application/json');

if (!$row) {
    echo json_encode(['error' => 'incorrect email or password']);
} elseif (!$row['token'] || strtotime($row['expired_at']) < time()) {
    $token = md5(uniqid() . rand(10000, 99999));
    $expire = date('Y-m-d H:i:s', strtotime('+30 days'));
    $update_sql = "UPDATE powerhome_bd.user SET token='$token', expired_at='$expire' WHERE email='$email'";

    if (mysqli_query($db_con, $update_sql)) {
        echo json_encode(['token' => $token, 'expired_at' => $expire]);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
} else {
    echo json_encode($row);
}

mysqli_close($db_con);
?>
