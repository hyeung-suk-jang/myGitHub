<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
requireLogin();

$pageTitle = '셀러 대시보드';

$db = Database::getInstance();
$userId = Session::getUserId();

// 셀러 정보 조회
$sellerInfo = getSellerInfo($userId);

if (!$sellerInfo) {
    Session::setFlash('error', '셀러로 등록되지 않았습니다.');
    redirect('/solution-marketplace/seller/register.php');
}

if ($sellerInfo['seller_status'] === 'pending') {
    Session::setFlash('info', '셀러 승인 대기 중입니다. 관리자 승인 후 이용 가능합니다.');
    redirect('/solution-marketplace/public/index.php');
}

if ($sellerInfo['seller_status'] === 'rejected') {
    Session::setFlash('error', '셀러 승인이 거절되었습니다. 사유: ' . $sellerInfo['rejection_reason']);
    redirect('/solution-marketplace/public/index.php');
}

// 셀러 통계 조회
$sellerId = $sellerInfo['id'];

// 총 상품 수
$productCount = $db->fetchOne(
    "SELECT COUNT(*) as count FROM products WHERE seller_id = ? AND status != 'deleted'",
    [$sellerId]
)['count'];

// 총 판매 수
$totalSales = $db->fetchOne(
    "SELECT COUNT(*) as count FROM order_items oi
     JOIN orders o ON oi.order_id = o.id
     WHERE oi.seller_id = ? AND o.order_status = 'completed'",
    [$sellerId]
)['count'];

// 총 매출액
$totalRevenue = $db->fetchOne(
    "SELECT COALESCE(SUM(oi.price), 0) as total FROM order_items oi
     JOIN orders o ON oi.order_id = o.id
     WHERE oi.seller_id = ? AND o.order_status = 'completed'",
    [$sellerId]
)['total'];

// 최근 상품 목록
$recentProducts = $db->fetchAll(
    "SELECT * FROM products WHERE seller_id = ? AND status != 'deleted'
     ORDER BY created_at DESC LIMIT 5",
    [$sellerId]
);

include __DIR__ . '/../includes/header.php';
?>

<div class="seller-dashboard">
    <h2>셀러 대시보드</h2>

    <div class="seller-info-box">
        <h3>셀러 정보</h3>
        <p><strong>사업자명:</strong> <?php echo escape($sellerInfo['business_name']); ?></p>
        <p><strong>승인 상태:</strong>
            <span class="status-approved">승인됨</span>
        </p>
        <p><strong>승인일:</strong> <?php echo formatDate($sellerInfo['approved_at'], 'Y-m-d'); ?></p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>등록 상품</h3>
            <p class="stat-value"><?php echo number_format($productCount); ?>개</p>
        </div>
        <div class="stat-card">
            <h3>총 판매</h3>
            <p class="stat-value"><?php echo number_format($totalSales); ?>건</p>
        </div>
        <div class="stat-card">
            <h3>총 매출</h3>
            <p class="stat-value"><?php echo formatPrice($totalRevenue); ?></p>
        </div>
        <div class="stat-card">
            <h3>예상 정산액</h3>
            <p class="stat-value">
                <?php
                $expectedSettlement = $totalRevenue * (100 - COMMISSION_RATE) / 100;
                echo formatPrice($expectedSettlement);
                ?>
            </p>
            <small>수수료 <?php echo COMMISSION_RATE; ?>% 차감</small>
        </div>
    </div>

    <div class="dashboard-actions">
        <a href="/solution-marketplace/seller/products/add.php" class="btn btn-primary">
            새 상품 등록
        </a>
        <a href="/solution-marketplace/seller/products/list.php" class="btn btn-secondary">
            상품 관리
        </a>
        <a href="/solution-marketplace/seller/sales/index.php" class="btn btn-secondary">
            판매 내역
        </a>
    </div>

    <div class="recent-products">
        <h3>최근 등록한 상품</h3>

        <?php if (empty($recentProducts)): ?>
            <p class="no-data">등록된 상품이 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>상품명</th>
                        <th>타입</th>
                        <th>가격</th>
                        <th>조회수</th>
                        <th>다운로드</th>
                        <th>상태</th>
                        <th>등록일</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProducts as $product): ?>
                        <tr>
                            <td><?php echo escape($product['title']); ?></td>
                            <td>
                                <?php
                                $typeLabels = ['ebook' => '전자책', 'source' => '소스', 'video' => '동영상'];
                                echo $typeLabels[$product['product_type']] ?? $product['product_type'];
                                ?>
                            </td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo number_format($product['view_count']); ?></td>
                            <td><?php echo number_format($product['download_count']); ?></td>
                            <td>
                                <?php if ($product['status'] === 'active'): ?>
                                    <span class="status-active">활성</span>
                                <?php else: ?>
                                    <span class="status-inactive">비활성</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($product['created_at'], 'Y-m-d'); ?></td>
                            <td>
                                <a href="/solution-marketplace/seller/products/edit.php?id=<?php echo $product['id']; ?>"
                                   class="btn btn-sm">수정</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
