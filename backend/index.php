<?php
// Entry point of the REST API (FlightPHP).
// Local run:  cd backend && composer install && php -S localhost:8080 index.php
ini_set('display_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('Europe/Sarajevo');

// PHP's built-in server only: serve the Swagger page and openapi.json as plain files.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with($path, '/swagger/') && is_file(__DIR__ . $path)) {
        return false;
    }
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/services/ServiceException.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/BrandService.php';
require_once __DIR__ . '/services/VehicleService.php';
require_once __DIR__ . '/services/TestDriveService.php';
require_once __DIR__ . '/services/ReviewService.php';

// Services are created the first time a route asks for them (Flight::userService() ...).
Flight::register('userService', 'UserService');
Flight::register('brandService', 'BrandService');
Flight::register('vehicleService', 'VehicleService');
Flight::register('testDriveService', 'TestDriveService');
Flight::register('reviewService', 'ReviewService');

// Reads the JSON body of a request as an array. Invalid JSON gives a 400 response.
function jsonBody(): array
{
    $body = Flight::request()->getBody();
    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new ServiceException('Request body must be a valid JSON object.', 400);
    }
    return $data;
}

// Errors: a ServiceException carries its own HTTP status and message (400, 404, 503).
// Any other error (for example a database error) becomes a generic 500 without
// stack trace, SQL or file paths. Milestone 4 adds error logging in middleware.
Flight::map('error', function (Throwable $error) {
    if ($error instanceof ServiceException) {
        $response = ['error' => $error->getMessage()];
        if (!empty($error->getDetails())) {
            $response['details'] = $error->getDetails();
        }
        Flight::json($response, $error->getCode());
        return;
    }
    Flight::json(['error' => 'Internal server error.'], 500);
});

Flight::map('notFound', function () {
    Flight::json(['error' => 'Route not found.'], 404);
});

require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/BrandRoutes.php';
require_once __DIR__ . '/routes/VehicleRoutes.php';
require_once __DIR__ . '/routes/TestDriveRoutes.php';
require_once __DIR__ . '/routes/ReviewRoutes.php';

Flight::start();
