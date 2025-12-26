<?php
require_once 'functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['count' => 0]);
    exit;
}

$count = get_cart_count($_SESSION['user_id']);
echo json_encode(['count' => $count]);
?>
