<?php
declare(strict_types=1);

// Always return JSON
header('Content-Type: application/json; charset=utf-8');
header('Accept: application/json');

// Do not show PHP errors as HTML in output
ini_set('display_errors', '0');
ini_set('html_errors', '0');

// (Optional) log errors instead
ini_set('log_errors', '1');

// Convert PHP errors/warnings/notices to exceptions
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false; // respect @ operator / error_reporting
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Catch fatal errors (E_ERROR) and return JSON
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'FATAL_ERROR',
                'message' => 'Internal server error',
                // For debugging only; remove in production:
                'details' => $err,
            ]
        ]);
    }
});