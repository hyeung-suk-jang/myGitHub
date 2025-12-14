<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();

$code = getQuery('code');
$state = getQuery('state');
$error = getQuery('error');

if ($error) {
    Session::setFlash('error', '네이버 로그인에 실패했습니다.');
    redirect('/solution-marketplace/public/login.php');
}

// State 검증
if ($state !== Session::get('naver_state')) {
    Session::setFlash('error', '잘못된 요청입니다.');
    redirect('/solution-marketplace/public/login.php');
}

if (!$code) {
    Session::setFlash('error', '인증 코드가 없습니다.');
    redirect('/solution-marketplace/public/login.php');
}

// 액세스 토큰 요청
$tokenUrl = 'https://nid.naver.com/oauth2.0/token';
$tokenParams = [
    'grant_type' => 'authorization_code',
    'client_id' => NAVER_CLIENT_ID,
    'client_secret' => NAVER_CLIENT_SECRET,
    'code' => $code,
    'state' => $state
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl . '?' . http_build_query($tokenParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);

if (!isset($tokenData['access_token'])) {
    Session::setFlash('error', '액세스 토큰을 받아오지 못했습니다.');
    redirect('/solution-marketplace/public/login.php');
}

$accessToken = $tokenData['access_token'];

// 사용자 정보 요청
$userInfoUrl = 'https://openapi.naver.com/v1/nid/me';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoResponse, true);

if (!isset($userInfo['response']['id'])) {
    Session::setFlash('error', '사용자 정보를 가져오지 못했습니다.');
    redirect('/solution-marketplace/public/login.php');
}

// SNS 로그인 처리
$response = $userInfo['response'];
$providerId = $response['id'];
$email = $response['email'] ?? 'naver_' . $providerId . '@naver.com';
$name = $response['name'] ?? $response['nickname'] ?? '네이버 사용자';

$result = socialLogin('naver', $providerId, $email, $name);

if ($result['success']) {
    Session::setFlash('success', '네이버 로그인에 성공했습니다.');
    redirect('/solution-marketplace/public/index.php');
} else {
    Session::setFlash('error', $result['message']);
    redirect('/solution-marketplace/public/login.php');
}
