<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

Session::start();

// CSRF 방지를 위한 state 생성
$state = bin2hex(random_bytes(16));
Session::set('google_state', $state);

// 구글 로그인 URL로 리다이렉트
$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'state' => $state
];

$authUrl = $googleAuthUrl . '?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
