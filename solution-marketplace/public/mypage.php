<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
requireLogin();

$pageTitle = '마이페이지';

$db = Database::getInstance();
$userId = Session::getUserId();

// 사용자 정보
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

// 구매 내역
$orders = $db->fetchAll(
    "SELECT o.*, COUNT(oi.id) as item_count
     FROM orders o
     LEFT JOIN order_items oi ON o.id = oi.order_id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC",
    [$userId]
);

// 구매한 상품
$purchasedProducts = $db->fetchAll(
    "SELECT DISTINCT p.*, oi.created_at as purchased_at
     FROM products p
     JOIN order_items oi ON p.id = oi.product_id
     JOIN orders o ON oi.order_id = o.id
     WHERE o.user_id = ? AND o.order_status = 'completed'
     ORDER BY oi.created_at DESC",
    [$userId]
);

include __DIR__ . '/../includes/header.php';
?>

<div class="mypage-container">
    <h2>마이페이지</h2>

    <div class="user-info-section section">
        <h3>회원 정보</h3>
        <div class="user-info">
            <p><strong>이름:</strong> <?php echo escape($user['name']); ?></p>
            <p><strong>이메일:</strong> <?php echo escape($user['email']); ?></p>
            <p><strong>전화번호:</strong> <?php echo escape($user['phone'] ?: '-'); ?></p>
            <p><strong>회원 유형:</strong>
                <?php
                $typeLabels = ['user' => '일반 회원', 'seller' => '셀러', 'admin' => '관리자'];
                echo $typeLabels[$user['user_type']] ?? $user['user_type'];
                ?>
            </p>
            <p><strong>가입일:</strong> <?php echo formatDate($user['created_at']); ?></p>
        </div>
    </div>

    <div class="purchased-products-section section">
        <h3>구매한 상품</h3>

        <?php if (empty($purchasedProducts)): ?>
            <p class="no-data">구매한 상품이 없습니다.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($purchasedProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-thumbnail">
                            <?php if ($product['thumbnail_path']): ?>
                                <img src="<?php echo escape($product['thumbnail_path']); ?>"
                                     alt="<?php echo escape($product['title']); ?>">
                            <?php else: ?>
                                <div class="no-thumbnail">
                                    <?php
                                    $typeLabels = ['ebook' => '전자책', 'source' => '소스', 'video' => '동영상'];
                                    echo $typeLabels[$product['product_type']] ?? '상품';
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo escape($product['title']); ?></h3>
                            <p class="purchase-date">구매일: <?php echo formatDate($product['purchased_at'], 'Y-m-d'); ?></p>
                            <div class="product-actions">
                                <a href="/solution-marketplace/public/download.php?product_id=<?php echo $product['id']; ?>"
                                   class="btn btn-primary btn-sm">다운로드</a>
                                <a href="/solution-marketplace/public/products/detail.php?id=<?php echo $product['id']; ?>"
                                   class="btn btn-secondary btn-sm">상세보기</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="order-history-section section">
        <h3>주문 내역</h3>

        <?php if (empty($orders)): ?>
            <p class="no-data">주문 내역이 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>상품 수</th>
                        <th>총 금액</th>
                        <th>주문 상태</th>
                        <th>주문일</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo escape($order['order_number']); ?></td>
                            <td><?php echo $order['item_count']; ?>개</td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <?php if ($order['order_status'] === 'completed'): ?>
                                    <span class="badge badge-success">완료</span>
                                <?php elseif ($order['order_status'] === 'pending'): ?>
                                    <span class="badge badge-warning">대기</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?php echo $order['order_status']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
