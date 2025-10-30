<?php

// Set UTF-8 encoding for proper character handling
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');

// Suppress deprecation warnings that interfere with CORS headers
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

/*
|--------------------------------------------------------------------------
| Handle CORS First
|--------------------------------------------------------------------------
|
| Handle Cross-Origin Resource Sharing (CORS) for frontend requests
| This must be done before any output to avoid header issues
|
*/

// Set CORS headers immediately
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$app->router->run();