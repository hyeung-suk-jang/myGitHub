<?php
require_once 'functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

$ebook_id = $_POST['ebook_id'] ?? 0;
$rating = $_POST['rating'] ?? 0;
$code_quality_rating = $_POST['code_quality_rating'] ?? 0;
$content_rating = $_POST['content_rating'] ?? 0;
$comment = clean_input($_POST['comment'] ?? '');

// 유효성 검사
if (!$ebook_id || !$rating) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 입력해주세요.']);
    exit;
}

// 구매 여부 확인
if (!has_purchased($_SESSION['user_id'], $ebook_id)) {
    echo json_encode(['success' => false, 'message' => '구매한 전자책만 리뷰를 작성할 수 있습니다.']);
    exit;
}

// 이미 리뷰를 작성했는지 확인
$db = getDB();
$stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND ebook_id = ?");
$stmt->execute([$_SESSION['user_id'], $ebook_id]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => '이미 리뷰를 작성하셨습니다.']);
    exit;
}

// 리뷰 추가
$stmt = $db->prepare("INSERT INTO reviews (ebook_id, user_id, rating, code_quality_rating, content_rating, comment) VALUES (?, ?, ?, ?, ?, ?)");

if ($stmt->execute([$ebook_id, $_SESSION['user_id'], $rating, $code_quality_rating, $content_rating, $comment])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '리뷰 등록 중 오류가 발생했습니다.']);
}
?>
