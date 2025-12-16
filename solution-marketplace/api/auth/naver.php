<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

Session::start();

// CSRF 방지를 위한 state 생성
$state = bin2hex(random_bytes(16));
Session::set('naver_state', $state);

// 네이버 로그인 URL로 리다이렉트
$naverAuthUrl = 'https://nid.naver.com/oauth2.0/authorize';
$params = [
    'response_type' => 'code',
    'client_id' => NAVER_CLIENT_ID,
    'redirect_uri' => NAVER_REDIRECT_URI,
    'state' => $state
];

$authUrl = $naverAuthUrl . '?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
