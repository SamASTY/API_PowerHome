<?php
declare(strict_types=1);

function json_ok($data = null, int $status = 200): void {
    http_response_code($status);
    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $status = 400, string $code = 'BAD_REQUEST', $details = null): void {
    http_response_code($status);
    $payload = [
        'success' => false,
        'error' => [
            'code' => $code,
            'message' => $message
        ]
    ];
    if ($details !== null) {
        $payload['error']['details'] = $details; // remove in production if sensitive
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}