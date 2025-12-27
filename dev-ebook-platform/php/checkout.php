<?php
require_once 'functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

$items = $_POST['items'] ?? [];

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => '장바구니가 비어있습니다.']);
    exit;
}

$db = getDB();

try {
    $db->beginTransaction();

    // 주문 번호 생성
    $order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

    // 총 금액 계산
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += floatval($item['price']);
    }

    // 주문 생성
    $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $order_number, $total_amount]);
    $order_id = $db->lastInsertId();

    // 주문 상세 추가
    $stmt = $db->prepare("INSERT INTO order_items (order_id, ebook_id, price) VALUES (?, ?, ?)");
    foreach ($items as $item) {
        $stmt->execute([$order_id, $item['ebook_id'], $item['price']]);
    }

    $db->commit();

    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => '주문 생성 중 오류가 발생했습니다.']);
}
?>
