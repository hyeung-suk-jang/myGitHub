<?php

namespace App\Middleware;

use Core\Security\Security;

class CsrfMiddleware
{
    public function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

            if (!Security::verifyCSRFToken($token)) {
                http_response_code(403);
                echo json_encode(['error' => 'CSRF token validation failed']);
                return false;
            }
        }

        return true;
    }
}
