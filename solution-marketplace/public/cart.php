<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
requireLogin();

$pageTitle = '장바구니';

$db = Database::getInstance();
$userId = Session::getUserId();

// 장바구니 상품 조회
$cartItems = $db->fetchAll(
    "SELECT c.id as cart_id, p.*, u.name as seller_name
     FROM cart c
     JOIN products p ON c.product_id = p.id
     JOIN sellers s ON p.seller_id = s.id
     JOIN users u ON s.user_id = u.id
     WHERE c.user_id = ? AND p.status = 'active'
     ORDER BY c.created_at DESC",
    [$userId]
);

// 총 금액 계산
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price'];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="cart-container">
    <h2>장바구니</h2>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <p>장바구니가 비어있습니다.</p>
            <a href="/solution-marketplace/public/products/list.php" class="btn btn-primary">
                상품 둘러보기
            </a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>상품</th>
                        <th>타입</th>
                        <th>판매자</th>
                        <th>가격</th>
                        <th>삭제</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-item-info">
                                    <?php if ($item['thumbnail_path']): ?>
                                        <img src="<?php echo escape($item['thumbnail_path']); ?>"
                                             alt="<?php echo escape($item['title']); ?>"
                                             class="cart-item-thumbnail">
                                    <?php endif; ?>
                                    <div>
                                        <h4><?php echo escape($item['title']); ?></h4>
                                        <p class="item-description"><?php echo escape(truncate($item['description'], 60)); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $typeLabels = ['ebook' => '전자책', 'source' => '솔루션 소스', 'video' => '동영상 강의'];
                                echo $typeLabels[$item['product_type']] ?? $item['product_type'];
                                ?>
                            </td>
                            <td><?php echo escape($item['seller_name']); ?></td>
                            <td class="item-price"><?php echo formatPrice($item['price']); ?></td>
                            <td>
                                <form method="POST" action="/solution-marketplace/api/cart/remove.php" class="remove-form">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">삭제</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-summary">
            <div class="summary-box">
                <h3>주문 요약</h3>
                <div class="summary-row">
                    <span>상품 개수:</span>
                    <span><?php echo count($cartItems); ?>개</span>
                </div>
                <div class="summary-row total">
                    <span>총 결제 금액:</span>
                    <span class="total-price"><?php echo formatPrice($totalAmount); ?></span>
                </div>
                <form method="POST" action="/solution-marketplace/public/payment/checkout.php">
                    <input type="hidden" name="from_cart" value="1">
                    <button type="submit" class="btn btn-primary btn-large btn-block">
                        구매하기
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
