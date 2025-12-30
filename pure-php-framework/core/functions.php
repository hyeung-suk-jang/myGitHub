<?php
/**
 * 공통 함수 모음
 */

/**
 * 안전한 출력 (XSS 방지)
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 세션 시작
 */
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

/**
 * 세션 값 가져오기
 */
function session_get($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * 세션 값 설정
 */
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * 세션 값 삭제
 */
function session_delete($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * 세션 전체 삭제
 */
function session_destroy_all() {
    session_destroy();
    $_SESSION = [];
}

/**
 * CSRF 토큰 생성
 */
function csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * CSRF 토큰 검증
 */
function csrf_verify($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * 리다이렉트
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * JSON 응답
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * POST 데이터 가져오기
 */
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * GET 데이터 가져오기
 */
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * 현재 URL 가져오기
 */
function current_url() {
    return $_SERVER['REQUEST_URI'];
}

/**
 * 현재 메서드 가져오기
 */
function request_method() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * POST 요청 여부
 */
function is_post() {
    return request_method() === 'POST';
}

/**
 * GET 요청 여부
 */
function is_get() {
    return request_method() === 'GET';
}

/**
 * 로그인 여부 확인
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * 로그인 필수 체크
 */
function require_login() {
    if (!is_logged_in()) {
        redirect('/login');
    }
}

/**
 * 비밀번호 해시
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * 비밀번호 검증
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 랜덤 문자열 생성
 */
function random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * 파일 확장자 가져오기
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * 파일 업로드 검증
 */
function validate_upload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 실패'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => '파일 크기 초과'];
    }

    $ext = get_file_extension($file['name']);
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => '허용되지 않는 파일 형식'];
    }

    return ['success' => true];
}

/**
 * 파일 업로드 처리
 */
function upload_file($file, $directory = '') {
    $validation = validate_upload($file);
    if (!$validation['success']) {
        return $validation;
    }

    $ext = get_file_extension($file['name']);
    $filename = random_string() . '.' . $ext;
    $upload_dir = UPLOAD_PATH . ($directory ? '/' . $directory : '');

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filepath = $upload_dir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }

    return ['success' => false, 'message' => '파일 저장 실패'];
}

/**
 * 디버그 출력
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

/**
 * 날짜 포맷
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * URL 생성
 */
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Asset URL 생성
 */
function asset($path) {
    return url('public/' . ltrim($path, '/'));
}
