<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();
requireLogin();

$pageTitle = '결제 완료';

$orderId = getQuery('order_id');

if (!$orderId) {
    Session::setFlash('error', '주문을 찾을 수 없습니다.');
    redirect('/solution-marketplace/public/index.php');
}

$db = Database::getInstance();
$userId = Session::getUserId();

// 주문 정보 조회
$order = $db->fetchOne(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    [$orderId, $userId]
);

if (!$order) {
    Session::setFlash('error', '주문을 찾을 수 없습니다.');
    redirect('/solution-marketplace/public/index.php');
}

// 주문 상품 조회
$orderItems = $db->fetchAll(
    "SELECT oi.*, p.title, p.product_type, p.file_path
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?",
    [$orderId]
);

include __DIR__ . '/../../includes/header.php';
?>

<div class="payment-complete-container">
    <div class="complete-box">
        <div class="success-icon">✓</div>
        <h2>결제가 완료되었습니다!</h2>
        <p>주문번호: <strong><?php echo escape($order['order_number']); ?></strong></p>
    </div>

    <div class="order-summary">
        <h3>주문 정보</h3>
        <div class="summary-info">
            <div class="info-row">
                <span>주문일시:</span>
                <span><?php echo formatDate($order['created_at']); ?></span>
            </div>
            <div class="info-row">
                <span>결제 금액:</span>
                <span class="price"><?php echo formatPrice($order['total_amount']); ?></span>
            </div>
            <div class="info-row">
                <span>결제 상태:</span>
                <span class="status-completed">결제 완료</span>
            </div>
        </div>
    </div>

    <div class="purchased-items">
        <h3>구매한 상품</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>상품명</th>
                    <th>타입</th>
                    <th>가격</th>
                    <th>다운로드</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?php echo escape($item['title']); ?></td>
                        <td>
                            <?php
                            $typeLabels = ['ebook' => '전자책', 'source' => '솔루션 소스', 'video' => '동영상 강의'];
                            echo $typeLabels[$item['product_type']] ?? $item['product_type'];
                            ?>
                        </td>
                        <td><?php echo formatPrice($item['price']); ?></td>
                        <td>
                            <a href="/solution-marketplace/public/download.php?product_id=<?php echo $item['product_id']; ?>"
                               class="btn btn-sm btn-primary">
                                다운로드
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="complete-actions">
        <a href="/solution-marketplace/public/mypage.php" class="btn btn-secondary">
            구매 내역 보기
        </a>
        <a href="/solution-marketplace/public/index.php" class="btn btn-primary">
            홈으로 가기
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
