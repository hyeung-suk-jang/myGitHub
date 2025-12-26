<?php
require_once 'functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false]);
    exit;
}

$cart_id = $_POST['cart_id'] ?? 0;

if (!$cart_id) {
    echo json_encode(['success' => false]);
    exit;
}

$db = getDB();
$stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$result = $stmt->execute([$cart_id, $_SESSION['user_id']]);

echo json_encode(['success' => $result]);
?>
