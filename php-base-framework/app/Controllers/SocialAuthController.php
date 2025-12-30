<?php

namespace App\Controllers;

use App\Models\User;
use Core\Utilities\OAuth;

class SocialAuthController
{
    private $userModel;
    private $oauthConfig;

    public function __construct()
    {
        $this->userModel = new User();
        $this->oauthConfig = require __DIR__ . '/../../config/oauth.php';
    }

    public function redirectToProvider($provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'kakao'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid provider']);
            return;
        }

        $oauth = new OAuth($provider, $this->oauthConfig);
        $authUrl = $oauth->getAuthorizationUrl();

        header('Location: ' . $authUrl);
        exit;
    }

    public function handleProviderCallback($provider)
    {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        if (empty($code)) {
            http_response_code(400);
            echo json_encode(['error' => 'Authorization code not provided']);
            return;
        }

        $oauth = new OAuth($provider, $this->oauthConfig);

        if (!$oauth->verifyState($state)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid state parameter']);
            return;
        }

        try {
            $tokenData = $oauth->getAccessToken($code);
            $accessToken = $tokenData['access_token'];

            $userData = $oauth->getUserInfo($accessToken);

            $user = $this->userModel->findOrCreateFromProvider($provider, $userData);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: /dashboard');
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'OAuth authentication failed']);
        }
    }
}
