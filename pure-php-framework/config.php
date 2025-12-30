<?php
/**
 * 순수 PHP 프레임워크 설정 파일
 * Composer나 autoload 없이 순수하게 동작
 */

// 에러 리포팅 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 기본 설정
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// 데이터베이스 설정
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// 앱 설정
define('APP_NAME', '순수 PHP 프레임워크');
define('APP_URL', 'http://localhost');
define('DEBUG_MODE', true);

// 세션 설정
define('SESSION_NAME', 'pure_php_session');
define('SESSION_LIFETIME', 3600); // 1시간

// 보안 설정
define('CSRF_TOKEN_NAME', 'csrf_token');
define('HASH_ALGO', 'sha256');

// 업로드 설정
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// 페이지네이션
define('ITEMS_PER_PAGE', 20);

// 타임존
date_default_timezone_set('Asia/Seoul');
