<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
requireAdmin();

$pageTitle = '관리자 대시보드';

$db = Database::getInstance();

// 통계 조회
$stats = [
    'total_users' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE user_type = 'user'")['count'],
    'total_sellers' => $db->fetchOne("SELECT COUNT(*) as count FROM sellers WHERE seller_status = 'approved'")['count'],
    'pending_sellers' => $db->fetchOne("SELECT COUNT(*) as count FROM sellers WHERE seller_status = 'pending'")['count'],
    'total_products' => $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'],
    'total_orders' => $db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE order_status = 'completed'")['count'],
    'total_revenue' => $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE order_status = 'completed'")['total']
];

// 최근 주문
$recentOrders = $db->fetchAll(
    "SELECT o.*, u.name as user_name, u.email
     FROM orders o
     JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC
     LIMIT 10"
);

// 승인 대기 셀러
$pendingSellers = $db->fetchAll(
    "SELECT s.*, u.name, u.email
     FROM sellers s
     JOIN users u ON s.user_id = u.id
     WHERE s.seller_status = 'pending'
     ORDER BY s.created_at DESC
     LIMIT 5"
);

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-dashboard">
    <h2>관리자 대시보드</h2>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>일반 회원</h3>
            <p class="stat-value"><?php echo number_format($stats['total_users']); ?>명</p>
        </div>
        <div class="stat-card">
            <h3>승인된 셀러</h3>
            <p class="stat-value"><?php echo number_format($stats['total_sellers']); ?>명</p>
        </div>
        <div class="stat-card">
            <h3>대기중인 셀러</h3>
            <p class="stat-value highlight"><?php echo number_format($stats['pending_sellers']); ?>명</p>
        </div>
        <div class="stat-card">
            <h3>전체 상품</h3>
            <p class="stat-value"><?php echo number_format($stats['total_products']); ?>개</p>
        </div>
        <div class="stat-card">
            <h3>총 주문</h3>
            <p class="stat-value"><?php echo number_format($stats['total_orders']); ?>건</p>
        </div>
        <div class="stat-card">
            <h3>총 매출</h3>
            <p class="stat-value"><?php echo formatPrice($stats['total_revenue']); ?></p>
        </div>
    </div>

    <div class="admin-sections">
        <div class="admin-section">
            <h3>승인 대기 셀러
                <?php if ($stats['pending_sellers'] > 0): ?>
                    <span class="badge"><?php echo $stats['pending_sellers']; ?></span>
                <?php endif; ?>
            </h3>

            <?php if (empty($pendingSellers)): ?>
                <p class="no-data">승인 대기중인 셀러가 없습니다.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>이름</th>
                            <th>이메일</th>
                            <th>사업자명</th>
                            <th>신청일</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingSellers as $seller): ?>
                            <tr>
                                <td><?php echo escape($seller['name']); ?></td>
                                <td><?php echo escape($seller['email']); ?></td>
                                <td><?php echo escape($seller['business_name']); ?></td>
                                <td><?php echo formatDate($seller['created_at'], 'Y-m-d'); ?></td>
                                <td>
                                    <a href="/solution-marketplace/admin/sellers.php?id=<?php echo $seller['id']; ?>"
                                       class="btn btn-sm">상세보기</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="/solution-marketplace/admin/sellers.php" class="btn btn-secondary">
                    전체 셀러 관리
                </a>
            <?php endif; ?>
        </div>

        <div class="admin-section">
            <h3>최근 주문</h3>

            <?php if (empty($recentOrders)): ?>
                <p class="no-data">주문 내역이 없습니다.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>주문번호</th>
                            <th>구매자</th>
                            <th>금액</th>
                            <th>상태</th>
                            <th>주문일</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?php echo escape($order['order_number']); ?></td>
                                <td><?php echo escape($order['user_name']); ?></td>
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
                                <td><?php echo formatDate($order['created_at'], 'Y-m-d H:i'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-quick-links">
        <a href="/solution-marketplace/admin/sellers.php" class="btn btn-primary">셀러 관리</a>
        <a href="/solution-marketplace/admin/settlements.php" class="btn btn-primary">정산 관리</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
