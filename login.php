<?php
global $db_con;
require_once 'DatabaseConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

$stmt = mysqli_prepare($db_con,
    "SELECT id, firstname, lastname, email, password, token, expired_at FROM User WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Incorrect email or password']);
    mysqli_close($db_con);
    exit;
}

// Reuse a valid token or generate a new one
if (!$user['token'] || strtotime($user['expired_at']) < time()) {
    $token  = bin2hex(random_bytes(32));
    $expire = date('Y-m-d H:i:s', strtotime('+30 days'));
    $stmt = mysqli_prepare($db_con, "UPDATE User SET token = ?, expired_at = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $token, $expire, $user['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $user['token']      = $token;
    $user['expired_at'] = $expire;
}

unset($user['password']);
echo json_encode($user);
mysqli_close($db_con);

