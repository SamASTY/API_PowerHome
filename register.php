<?php
global $db_con;
require_once 'DatabaseConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input     = json_decode(file_get_contents('php://input'), true);
$firstname = trim($input['firstname'] ?? '');
$lastname  = trim($input['lastname'] ?? '');
$email     = trim($input['email'] ?? '');
$password  = $input['password'] ?? '';

if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

$stmt = mysqli_prepare($db_con, "SELECT id FROM User WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_fetch_assoc($result)) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already exists']);
    mysqli_stmt_close($stmt);
    mysqli_close($db_con);
    exit;
}
mysqli_stmt_close($stmt);

$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$token  = bin2hex(random_bytes(32));
$expire = date('Y-m-d H:i:s', strtotime('+30 days'));

$stmt = mysqli_prepare($db_con,
    "INSERT INTO User (firstname, lastname, email, password, token, expired_at) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssssss", $firstname, $lastname, $email, $hashed_password, $token, $expire);

if (mysqli_stmt_execute($stmt)) {
    $user_id = mysqli_insert_id($db_con);
    http_response_code(201);
    echo json_encode([
        'id'         => $user_id,
        'firstname'  => $firstname,
        'lastname'   => $lastname,
        'email'      => $email,
        'token'      => $token,
        'expired_at' => $expire,
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed']);
}
mysqli_stmt_close($stmt);
mysqli_close($db_con);
