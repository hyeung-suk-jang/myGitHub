<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();
requireLogin();

$cartId = getPost('cart_id');

if (!$cartId) {
    Session::setFlash('error', '잘못된 요청입니다.');
    redirect('/solution-marketplace/public/cart.php');
}

$db = Database::getInstance();
$userId = Session::getUserId();

// 본인의 장바구니 항목인지 확인
$cartItem = $db->fetchOne(
    "SELECT id FROM cart WHERE id = ? AND user_id = ?",
    [$cartId, $userId]
);

if (!$cartItem) {
    Session::setFlash('error', '장바구니 항목을 찾을 수 없습니다.');
    redirect('/solution-marketplace/public/cart.php');
}

// 삭제 처리
$result = $db->execute(
    "DELETE FROM cart WHERE id = ? AND user_id = ?",
    [$cartId, $userId]
);

if ($result) {
    Session::setFlash('success', '장바구니에서 삭제되었습니다.');
} else {
    Session::setFlash('error', '삭제에 실패했습니다.');
}

redirect('/solution-marketplace/public/cart.php');
