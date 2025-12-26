<?php
require_once 'functions.php';
header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$filters = ['search' => $query];
$results = get_ebooks($filters);

// 최대 10개 결과만 반환
$results = array_slice($results, 0, 10);

echo json_encode($results);
?>
