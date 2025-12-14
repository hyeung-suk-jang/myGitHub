<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();
requireLogin();

$productId = getPost('product_id');

if (!$productId) {
    Session::setFlash('error', '상품을 선택해주세요.');
    redirect($_SERVER['HTTP_REFERER'] ?? '/solution-marketplace/public/products/list.php');
}

$db = Database::getInstance();
$userId = Session::getUserId();

// 상품 존재 확인
$product = $db->fetchOne(
    "SELECT * FROM products WHERE id = ? AND status = 'active'",
    [$productId]
);

if (!$product) {
    Session::setFlash('error', '상품을 찾을 수 없습니다.');
    redirect('/solution-marketplace/public/products/list.php');
}

// 이미 구매한 상품인지 확인
$purchased = $db->fetchOne(
    "SELECT oi.id FROM order_items oi
     JOIN orders o ON oi.order_id = o.id
     WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'completed'",
    [$userId, $productId]
);

if ($purchased) {
    Session::setFlash('error', '이미 구매한 상품입니다.');
    redirect('/solution-marketplace/public/products/detail.php?id=' . $productId);
}

// 이미 장바구니에 있는지 확인
$existing = $db->fetchOne(
    "SELECT id FROM cart WHERE user_id = ? AND product_id = ?",
    [$userId, $productId]
);

if ($existing) {
    Session::setFlash('info', '이미 장바구니에 있는 상품입니다.');
} else {
    // 장바구니에 추가
    $result = $db->insert(
        "INSERT INTO cart (user_id, product_id) VALUES (?, ?)",
        [$userId, $productId]
    );

    if ($result) {
        Session::setFlash('success', '장바구니에 추가되었습니다.');
    } else {
        Session::setFlash('error', '장바구니 추가에 실패했습니다.');
    }
}

redirect('/solution-marketplace/public/cart.php');
