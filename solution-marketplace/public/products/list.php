<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';

Session::start();

$pageTitle = '상품 목록';

$db = Database::getInstance();

// 검색 및 필터 파라미터
$search = getQuery('search');
$productType = getQuery('type');
$page = getQuery('page', 1);
$itemsPerPage = 12;

// 쿼리 조건 구성
$where = ["p.status = 'active'"];
$params = [];

if ($search) {
    $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($productType && in_array($productType, ['ebook', 'source', 'video'])) {
    $where[] = "p.product_type = ?";
    $params[] = $productType;
}

$whereClause = implode(' AND ', $where);

// 전체 상품 수 조회
$totalItems = $db->fetchOne(
    "SELECT COUNT(*) as count FROM products p
     JOIN sellers s ON p.seller_id = s.id
     WHERE $whereClause",
    $params
)['count'];

// 페이징 정보 계산
$pagination = getPagination($page, $totalItems, $itemsPerPage);

// 상품 목록 조회
$products = $db->fetchAll(
    "SELECT p.*, u.name as seller_name
     FROM products p
     JOIN sellers s ON p.seller_id = s.id
     JOIN users u ON s.user_id = u.id
     WHERE $whereClause
     ORDER BY p.created_at DESC
     LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}",
    $params
);

include __DIR__ . '/../../includes/header.php';
?>

<div class="products-list-container">
    <h2>상품 목록</h2>

    <div class="search-filter-section">
        <form method="GET" action="" class="search-form">
            <div class="filter-group">
                <select name="type" onchange="this.form.submit()">
                    <option value="">전체 상품</option>
                    <option value="ebook" <?php echo $productType === 'ebook' ? 'selected' : ''; ?>>전자책</option>
                    <option value="source" <?php echo $productType === 'source' ? 'selected' : ''; ?>>솔루션 소스</option>
                    <option value="video" <?php echo $productType === 'video' ? 'selected' : ''; ?>>동영상 강의</option>
                </select>
            </div>
            <div class="search-group">
                <input type="text" name="search" placeholder="상품명 또는 설명으로 검색"
                       value="<?php echo escape($search ?? ''); ?>">
                <button type="submit" class="btn btn-primary">검색</button>
            </div>
        </form>
    </div>

    <div class="results-info">
        <p>총 <?php echo number_format($totalItems); ?>개의 상품</p>
    </div>

    <?php if (empty($products)): ?>
        <p class="no-data">검색 결과가 없습니다.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
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
                        <span class="product-type">
                            <?php
                            $typeLabels = ['ebook' => '전자책', 'source' => '솔루션 소스', 'video' => '동영상 강의'];
                            echo $typeLabels[$product['product_type']] ?? $product['product_type'];
                            ?>
                        </span>
                        <h3><?php echo escape($product['title']); ?></h3>
                        <p class="product-description">
                            <?php echo escape(truncate($product['description'], 100)); ?>
                        </p>
                        <p class="seller-name">by <?php echo escape($product['seller_name']); ?></p>
                        <div class="product-stats">
                            <span>조회 <?php echo number_format($product['view_count']); ?></span>
                            <span>다운로드 <?php echo number_format($product['download_count']); ?></span>
                        </div>
                        <div class="product-footer">
                            <span class="price"><?php echo formatPrice($product['price']); ?></span>
                            <a href="/solution-marketplace/public/products/detail.php?id=<?php echo $product['id']; ?>"
                               class="btn btn-sm">상세보기</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['has_prev']): ?>
                    <a href="?page=<?php echo $page - 1; ?>&type=<?php echo $productType; ?>&search=<?php echo $search; ?>"
                       class="btn">이전</a>
                <?php endif; ?>

                <span class="page-info">
                    <?php echo $page; ?> / <?php echo $pagination['total_pages']; ?>
                </span>

                <?php if ($pagination['has_next']): ?>
                    <a href="?page=<?php echo $page + 1; ?>&type=<?php echo $productType; ?>&search=<?php echo $search; ?>"
                       class="btn">다음</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
