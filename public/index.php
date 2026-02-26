<?php
/**
 * Front controller – routes kebab-case REST-like paths to the existing handlers.
 *
 * Route map (old file -> new route):
 *   login.php        POST  /auth/login
 *   register.php     POST  /auth/register
 *   updateProfile.php PUT  /user/profile
 *   getAppliances.php GET|POST|DELETE /appliances
 *   bookings.php      GET|POST|DELETE /bookings
 *   getConsumption.php GET /consumption
 *   getHabitat.php    GET /habitat
 *   getResidents.php  GET /residents
 *   getTimeSlots.php  GET /time-slots
 *
 * Authentication is enforced inside each handler (via auth_helper.php) except
 * for /auth/login and /auth/register which are intentionally public.
 */

// Centralised JSON error handler so no bare PHP errors/warnings leak as HTML.
set_error_handler(function (int $errno, string $errstr): bool {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    error_log("PHP error [$errno]: $errstr");
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    exit;
});

// Change working directory to the project root so that every handler's
// relative `require_once 'DatabaseConnection.php'` etc. still resolves.
chdir(__DIR__ . '/..');

// --- Routing ------------------------------------------------------------------

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Strip the script-directory prefix so the router works whether the app is
// deployed at the web-root or under a sub-path (e.g. /public/).
$base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path   = '/' . ltrim(substr($uri, strlen($base)), '/');
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Routes: "METHOD /path" => handler file (relative to project root).
 * Handlers with multiple HTTP methods share the same file and handle the
 * method check internally (same behaviour as before).
 */
$routes = [
    'POST /auth/login'      => 'login.php',
    'POST /auth/register'   => 'register.php',
    'PUT /user/profile'     => 'updateProfile.php',
    'GET /appliances'       => 'getAppliances.php',
    'POST /appliances'      => 'getAppliances.php',
    'DELETE /appliances'    => 'getAppliances.php',
    'GET /bookings'         => 'bookings.php',
    'POST /bookings'        => 'bookings.php',
    'DELETE /bookings'      => 'bookings.php',
    'GET /consumption'      => 'getConsumption.php',
    'GET /habitat'          => 'getHabitat.php',
    'GET /residents'        => 'getResidents.php',
    'GET /time-slots'       => 'getTimeSlots.php',
];

$key = $method . ' ' . $path;

if (!isset($routes[$key])) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit;
}

require $routes[$key];
