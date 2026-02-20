<?php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user_id = getAuthenticatedUserId($db_con);

$input     = json_decode(file_get_contents('php://input'), true);
$firstname = trim($input['firstname'] ?? '');
$lastname  = trim($input['lastname'] ?? '');
$email     = trim($input['email'] ?? '');
$password  = $input['password'] ?? '';

if (empty($firstname) || empty($lastname) || empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Firstname, lastname and email are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Ensure the new e-mail is not already used by another account
$stmt = mysqli_prepare($db_con, "SELECT id FROM User WHERE email = ? AND id != ?");
mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_fetch_assoc($result)) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already in use']);
    mysqli_stmt_close($stmt);
    mysqli_close($db_con);
    exit;
}
mysqli_stmt_close($stmt);

if (!empty($password)) {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt   = mysqli_prepare($db_con,
        "UPDATE User SET firstname = ?, lastname = ?, email = ?, password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $firstname, $lastname, $email, $hashed, $user_id);
} else {
    $stmt = mysqli_prepare($db_con,
        "UPDATE User SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $firstname, $lastname, $email, $user_id);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['message' => 'Profile updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed']);
}
mysqli_stmt_close($stmt);
mysqli_close($db_con);
