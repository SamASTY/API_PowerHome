<?php
global $db_con;
require_once 'DatabaseConnection.php';
require_once 'auth_helper.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = preg_replace('/^\/api/', '', $uri);

switch (true) {
    case preg_match('/^\/residents\/?$/', $uri):
        require_once 'residents.php';
        break;
    case preg_match('/^\/appliances\/?$/', $uri):
        require_once 'appliances.php';
        break;
    case preg_match('/^\/bookings\/?$/', $uri):
        require_once 'bookings.php';
        break;
    case preg_match('/^\/consumptions\/?$/', $uri):
        require_once 'consumptions.php';
        break;
    case preg_match('/^\/habitats\/?$/', $uri):
        require_once 'habitats.php';
        break;
    case preg_match('/^\/timeSlots\/?$/', $uri):
        require_once 'timeSlots.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint introuvable']);
}