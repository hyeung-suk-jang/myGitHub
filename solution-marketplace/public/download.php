<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
requireLogin();

$productId = getQuery('product_id');

if (!$productId) {
    Session::setFlash('error', '상품을 선택해주세요.');
    redirect('/solution-marketplace/public/index.php');
}

$db = Database::getInstance();
$userId = Session::getUserId();

// 구매 여부 확인
$orderItem = $db->fetchOne(
    "SELECT oi.*, p.title, p.file_path
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.id
     JOIN products p ON oi.product_id = p.id
     WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'completed'
     LIMIT 1",
    [$userId, $productId]
);

if (!$orderItem) {
    Session::setFlash('error', '구매하지 않은 상품입니다.');
    redirect('/solution-marketplace/public/products/detail.php?id=' . $productId);
}

// 다운로드 이력 확인 및 업데이트
$download = $db->fetchOne(
    "SELECT * FROM downloads WHERE user_id = ? AND product_id = ? AND order_item_id = ?",
    [$userId, $productId, $orderItem['id']]
);

if ($download) {
    // 다운로드 카운트 증가
    $db->execute(
        "UPDATE downloads
         SET download_count = download_count + 1, last_downloaded_at = NOW()
         WHERE id = ?",
        [$download['id']]
    );
} else {
    // 첫 다운로드 기록
    $db->insert(
        "INSERT INTO downloads (user_id, product_id, order_item_id, download_count, first_downloaded_at, last_downloaded_at)
         VALUES (?, ?, ?, 1, NOW(), NOW())",
        [$userId, $productId, $orderItem['id']]
    );
}

// 파일 다운로드
$filePath = $_SERVER['DOCUMENT_ROOT'] . $orderItem['file_path'];
$fileName = basename($orderItem['file_path']);

if (file_exists($filePath)) {
    downloadFile($filePath, $fileName);
} else {
    Session::setFlash('error', '파일을 찾을 수 없습니다. 판매자에게 문의해주세요.');
    redirect('/solution-marketplace/public/products/detail.php?id=' . $productId);
}
