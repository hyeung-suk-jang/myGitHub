<?php
require_once __DIR__ . '/../../config/database.php';

// 카카오 로그인 URL로 리다이렉트
$kakaoAuthUrl = 'https://kauth.kakao.com/oauth/authorize';
$params = [
    'client_id' => KAKAO_CLIENT_ID,
    'redirect_uri' => KAKAO_REDIRECT_URI,
    'response_type' => 'code'
];

$authUrl = $kakaoAuthUrl . '?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
