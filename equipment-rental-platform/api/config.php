<?php
/**
 * 데이터베이스 및 시스템 설정 파일
 */

// CORS 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 에러 리포팅 설정 (개발 환경)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 데이터베이스 설정
define('DB_HOST', 'localhost');
define('DB_NAME', 'equipment_rental');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// 시스템 설정
define('UPLOAD_PATH', '../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);

// 정산 설정
define('PLATFORM_COMMISSION_RATE', 0.20); // 20% 수수료

// JWT 설정 (세션 대신 사용 가능)
define('JWT_SECRET', 'your-secret-key-change-this-in-production');
define('JWT_EXPIRATION', 86400); // 24시간

// 페이지네이션
define('ITEMS_PER_PAGE', 12);

/**
 * 데이터베이스 연결
 */
class Database {
    private $conn = null;

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $this->conn = new PDO($dsn, DB_USER, DB_PASS);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch(PDOException $e) {
                error_log("Connection Error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                exit();
            }
        }
        return $this->conn;
    }
}

/**
 * JSON 응답 헬퍼 함수
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

function sendError($message, $statusCode = 400, $details = null) {
    $response = ['error' => $message];
    if ($details !== null) {
        $response['details'] = $details;
    }
    sendResponse($response, $statusCode);
}

function sendSuccess($data = [], $message = 'Success') {
    sendResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * 파일 업로드 헬퍼 함수
 */
function uploadFile($file, $subFolder = 'equipment') {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        throw new RuntimeException('Invalid file format. Only JPEG, PNG, JPG, WEBP allowed.');
    }

    $uploadDir = UPLOAD_PATH . $subFolder . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = sprintf('%s_%s.%s', uniqid(), time(), $extension);
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $subFolder . '/' . $filename;
}

/**
 * 입력 데이터 검증 및 정제
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * 날짜 유효성 검증
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * 세션 시작 및 사용자 인증 확인
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireAuth() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        sendError('Authentication required', 401);
    }
    return $_SESSION['user_id'];
}

function requireAdmin() {
    startSession();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        sendError('Admin access required', 403);
    }
    return $_SESSION['user_id'];
}

function getCurrentUser() {
    startSession();
    return isset($_SESSION['user_id']) ? [
        'user_id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'] ?? '',
        'username' => $_SESSION['username'] ?? '',
        'user_type' => $_SESSION['user_type'] ?? 'renter'
    ] : null;
}

/**
 * 로그 함수
 */
function logError($message, $context = []) {
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $logMessage .= ' - ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    error_log($logMessage . PHP_EOL, 3, '../logs/error.log');
}

function logActivity($userId, $action, $details = []) {
    $logMessage = date('Y-m-d H:i:s') . " - User $userId - $action";
    if (!empty($details)) {
        $logMessage .= ' - ' . json_encode($details, JSON_UNESCAPED_UNICODE);
    }
    error_log($logMessage . PHP_EOL, 3, '../logs/activity.log');
}

// 로그 디렉토리 생성
if (!file_exists('../logs')) {
    mkdir('../logs', 0755, true);
}
