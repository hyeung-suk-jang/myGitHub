<?php
require_once 'functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

$ebook_id = $_POST['ebook_id'] ?? 0;

if (!$ebook_id) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 이미 구매한 전자책인지 확인
if (has_purchased($_SESSION['user_id'], $ebook_id)) {
    echo json_encode(['success' => false, 'message' => '이미 구매한 전자책입니다.']);
    exit;
}

if (add_to_cart($_SESSION['user_id'], $ebook_id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '이미 장바구니에 있습니다.']);
}
?>
