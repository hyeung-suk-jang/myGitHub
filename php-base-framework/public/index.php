<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Database\Database;
use Core\Routing\Router;

// Load configuration
$dbConfig = require __DIR__ . '/../config/database.php';
$appConfig = require __DIR__ . '/../config/app.php';

// Set timezone
date_default_timezone_set($appConfig['timezone']);

// Initialize database
try {
    Database::getInstance($dbConfig);
} catch (Exception $e) {
    if ($appConfig['debug']) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Service temporarily unavailable");
    }
}

// Initialize router
$router = new Router();

// Load routes
require __DIR__ . '/../routes/web.php';

// Dispatch request
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    $router->dispatch($method, $uri);
} catch (Exception $e) {
    if ($appConfig['debug']) {
        echo "<pre>";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "</pre>";
    } else {
        http_response_code(500);
        echo "Internal Server Error";
    }
}
