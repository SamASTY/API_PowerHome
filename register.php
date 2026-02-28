<?php
global $db_con;
require_once 'DatabaseConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {

    $input = json_decode(file_get_contents('php://input'), true);
    $firstname = trim($input['firstname'] ?? '');
    $lastname = trim($input['lastname'] ?? '');
    $email = trim($input['email'] ?? '');
    $phoneCode = trim($input['phoneCode'] ?? '');
    $phoneNumber = trim($input['phoneNumber'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($phoneCode) || empty($phoneNumber)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

// Password regex (same as Java - 8+ chars, 1 upper, 1 lower, 1 digit, 1 special)
    $rexPassword = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/';

// Phone regex (same as Java - supports +33 1234567890 formats)
    $rexPhone = '/^[\\+]?[(]?[0-9]{3}[)]?[-\\s\\.]?[0-9]{3}[-\\s\\.]?[0-9]{4,6}$/';

// String regex (letters, spaces, accented chars, hyphen, apostrophe)
    $rexString = '/^([ \\xC0-\\xFFa-zA-Z\'\\-])+$/u'; // 'u' flag for UTF-8


    if (!preg_match($rexPassword, $password)) {
        http_response_code(400);
        echo json_encode(['error' => 'password invalide']);
        exit;
    }
    if (!preg_match($rexPhone, $phoneNumber)) {
        http_response_code(400);
        echo json_encode(['error' => 'phone number invalide']);
        exit;
    }
    if (!preg_match($rexString, $firstname)) {
        http_response_code(400);
        echo json_encode(['error' => 'Firstname invalide']);
        exit;
    }
    if (!preg_match($rexString, $lastname)) {
        http_response_code(400);
        echo json_encode(['error' => 'Lastname invalide']);
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
    $token = bin2hex(random_bytes(32));
    $expire = date('Y-m-d H:i:s', strtotime('+30 days'));

    $stmt = mysqli_prepare($db_con,
        "INSERT INTO User (firstname, lastname, email, password, token, expired_at, phoneCode, phoneNumber) VALUES (?, ?, ?, ?, ?, ?,?,?)");
    mysqli_stmt_bind_param($stmt, "ssssssss", $firstname, $lastname, $email, $hashed_password, $token, $expire, $phoneCode, $phoneNumber);

    if (mysqli_stmt_execute($stmt)) {
        $user_id = mysqli_insert_id($db_con);
        http_response_code(201);

    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed']);
    }
    mysqli_stmt_close($stmt);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'erreur' => $e->getMessage(),
        ]
    ]);
} finally {
    if (isset($db_con) && $db_con instanceof mysqli) {
        mysqli_close($db_con);
    }
}