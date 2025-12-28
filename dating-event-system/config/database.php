<?php
// 데이터베이스 연결 설정

// 데이터베이스 설정 (실제 사용 시 환경에 맞게 수정 필요)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dating_event_system');
define('DB_CHARSET', 'utf8mb4');

// 데이터베이스 연결 함수
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("데이터베이스 연결 실패: " . $e->getMessage());
        die("데이터베이스 연결에 실패했습니다.");
    }
}

// JSON 응답 함수
function jsonResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
