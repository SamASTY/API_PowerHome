<?php
/**
 * Validates the Bearer token from the Authorization header.
 * Returns the authenticated user's id on success, or exits with 401 on failure.
 */
function getAuthenticatedUserId($db_con) {
    $headers = getallheaders();
    $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = '';
    if (preg_match('/Bearer\s+(.+)/i', $auth_header, $matches)) {
        $token = trim($matches[1]);
    }

    if (empty($token)) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: no token provided']);
        exit;
    }

    $stmt = mysqli_prepare($db_con, "SELECT id FROM User WHERE token = ? AND expired_at > NOW()");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: invalid or expired token']);
        exit;
    }

    return (int) $user['id'];
}
