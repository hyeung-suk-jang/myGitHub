<?php
/**
 * 데이터베이스 설정 파일
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'solution_marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// 에러 리포팅 설정 (개발 환경)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 타임존 설정
date_default_timezone_set('Asia/Seoul');

// 세션 설정
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // HTTPS 사용시 1로 변경

// 파일 업로드 설정
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024); // 100MB
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

// SNS 로그인 설정 (실제 키로 변경 필요)
define('KAKAO_CLIENT_ID', 'your_kakao_client_id');
define('KAKAO_REDIRECT_URI', 'http://localhost/solution-marketplace/public/api/auth/kakao-callback.php');

define('NAVER_CLIENT_ID', 'your_naver_client_id');
define('NAVER_CLIENT_SECRET', 'your_naver_client_secret');
define('NAVER_REDIRECT_URI', 'http://localhost/solution-marketplace/public/api/auth/naver-callback.php');

define('GOOGLE_CLIENT_ID', 'your_google_client_id');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret');
define('GOOGLE_REDIRECT_URI', 'http://localhost/solution-marketplace/public/api/auth/google-callback.php');

// 정산 수수료율 (%)
define('COMMISSION_RATE', 10.00);
