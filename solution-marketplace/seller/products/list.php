<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();
requireLogin();

$pageTitle = '상품 관리';

$db = Database::getInstance();
$userId = Session::getUserId();

// 셀러 정보 확인
$sellerInfo = getSellerInfo($userId);

if (!$sellerInfo || !isApprovedSeller($userId)) {
    Session::setFlash('error', '승인된 셀러만 접근할 수 있습니다.');
    redirect('/solution-marketplace/seller/index.php');
}

$sellerId = $sellerInfo['id'];

// 상품 목록 조회
$products = $db->fetchAll(
    "SELECT * FROM products WHERE seller_id = ? AND status != 'deleted'
     ORDER BY created_at DESC",
    [$sellerId]
);

include __DIR__ . '/../../includes/header.php';
?>

<div class="seller-products-container">
    <div class="page-header">
        <h2>상품 관리</h2>
        <a href="/solution-marketplace/seller/products/add.php" class="btn btn-primary">
            새 상품 등록
        </a>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <p>등록된 상품이 없습니다.</p>
            <a href="/solution-marketplace/seller/products/add.php" class="btn btn-primary">
                첫 상품 등록하기
            </a>
        </div>
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
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <strong><?php echo escape($product['title']); ?></strong>
                        </td>
                        <td>
                            <?php
                            $typeLabels = ['ebook' => '전자책', 'source' => '솔루션 소스', 'video' => '동영상 강의'];
                            echo $typeLabels[$product['product_type']] ?? $product['product_type'];
                            ?>
                        </td>
                        <td><?php echo formatPrice($product['price']); ?></td>
                        <td><?php echo number_format($product['view_count']); ?></td>
                        <td><?php echo number_format($product['download_count']); ?></td>
                        <td>
                            <?php if ($product['status'] === 'active'): ?>
                                <span class="badge badge-success">활성</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">비활성</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($product['created_at'], 'Y-m-d'); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="/solution-marketplace/public/products/detail.php?id=<?php echo $product['id']; ?>"
                                   class="btn btn-sm" target="_blank">보기</a>
                                <a href="/solution-marketplace/seller/products/edit.php?id=<?php echo $product['id']; ?>"
                                   class="btn btn-sm">수정</a>
                                <a href="/solution-marketplace/seller/products/delete.php?id=<?php echo $product['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('정말 삭제하시겠습니까?');">삭제</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
