<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();

$code = getQuery('code');
$error = getQuery('error');

if ($error) {
    Session::setFlash('error', '카카오 로그인에 실패했습니다.');
    redirect('/solution-marketplace/public/login.php');
}

if (!$code) {
    Session::setFlash('error', '인증 코드가 없습니다.');
    redirect('/solution-marketplace/public/login.php');
}

// 액세스 토큰 요청
$tokenUrl = 'https://kauth.kakao.com/oauth/token';
$tokenParams = [
    'grant_type' => 'authorization_code',
    'client_id' => KAKAO_CLIENT_ID,
    'redirect_uri' => KAKAO_REDIRECT_URI,
    'code' => $code
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
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
$userInfoUrl = 'https://kapi.kakao.com/v2/user/me';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoResponse, true);

if (!isset($userInfo['id'])) {
    Session::setFlash('error', '사용자 정보를 가져오지 못했습니다.');
    redirect('/solution-marketplace/public/login.php');
}

// SNS 로그인 처리
$providerId = (string)$userInfo['id'];
$email = $userInfo['kakao_account']['email'] ?? 'kakao_' . $providerId . '@kakao.com';
$name = $userInfo['kakao_account']['profile']['nickname'] ?? '카카오 사용자';

$result = socialLogin('kakao', $providerId, $email, $name);

if ($result['success']) {
    Session::setFlash('success', '카카오 로그인에 성공했습니다.');
    redirect('/solution-marketplace/public/index.php');
} else {
    Session::setFlash('error', $result['message']);
    redirect('/solution-marketplace/public/login.php');
}
