<?php
/**
 * 유틸리티 함수 모음
 */

/**
 * XSS 방지를 위한 HTML 이스케이프
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 리다이렉트
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * JSON 응답 반환
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * 비밀번호 해시
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 비밀번호 검증
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 이메일 유효성 검증
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 파일 업로드 처리
 */
function uploadFile($file, $uploadDir, $allowedTypes = []) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 실패'];
    }

    // 파일 크기 체크
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'message' => '파일 크기가 너무 큽니다.'];
    }

    // 파일 타입 체크
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => '허용되지 않는 파일 형식입니다.'];
    }

    // 업로드 디렉토리 생성
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 파일명 생성 (중복 방지)
    $fileName = uniqid() . '_' . time() . '.' . $fileType;
    $filePath = $uploadDir . $fileName;

    // 파일 이동
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $filePath];
    }

    return ['success' => false, 'message' => '파일 저장 실패'];
}

/**
 * 파일 삭제
 */
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * 날짜 포맷팅
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * 금액 포맷팅
 */
function formatPrice($price) {
    return number_format($price, 0) . '원';
}

/**
 * 주문번호 생성
 */
function generateOrderNumber() {
    return 'ORD' . date('YmdHis') . rand(1000, 9999);
}

/**
 * 페이징 처리
 */
function getPagination($currentPage, $totalItems, $itemsPerPage = 10) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;

    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * URL 파라미터 생성
 */
function buildQueryString($params) {
    return http_build_query($params);
}

/**
 * 파일 다운로드 헤더 설정
 */
function downloadFile($filePath, $fileName) {
    if (!file_exists($filePath)) {
        return false;
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    readfile($filePath);
    exit;
}

/**
 * 썸네일 생성
 */
function createThumbnail($sourcePath, $destPath, $maxWidth = 300, $maxHeight = 300) {
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }

    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $mime = $imageInfo['mime'];

    // 원본 이미지 로드
    switch ($mime) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    // 비율 계산
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = $width * $ratio;
    $newHeight = $height * $ratio;

    // 썸네일 생성
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // 썸네일 저장
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($thumb, $destPath, 90);
            break;
        case 'image/png':
            imagepng($thumb, $destPath, 9);
            break;
        case 'image/gif':
            imagegif($thumb, $destPath);
            break;
    }

    imagedestroy($source);
    imagedestroy($thumb);

    return true;
}

/**
 * 문자열 자르기 (한글 지원)
 */
function truncate($string, $length = 100, $suffix = '...') {
    if (mb_strlen($string) <= $length) {
        return $string;
    }
    return mb_substr($string, 0, $length) . $suffix;
}

/**
 * 안전한 POST 데이터 가져오기
 */
function getPost($key, $default = null) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * 안전한 GET 데이터 가져오기
 */
function getQuery($key, $default = null) {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}
