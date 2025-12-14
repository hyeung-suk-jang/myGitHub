<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();

$pageTitle = '홈';

// 최신 상품 조회
$db = Database::getInstance();
$latestProducts = $db->fetchAll(
    "SELECT p.*, u.name as seller_name
     FROM products p
     JOIN sellers s ON p.seller_id = s.id
     JOIN users u ON s.user_id = u.id
     WHERE p.status = 'active'
     ORDER BY p.created_at DESC
     LIMIT 6"
);

include __DIR__ . '/../includes/header.php';
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>솔루션 마켓에 오신 것을 환영합니다</h1>
        <p>전자책, 솔루션 소스, 동영상 강의를 한 곳에서</p>
        <a href="/solution-marketplace/public/products/list.php" class="btn btn-primary btn-large">
            상품 둘러보기
        </a>
    </div>
</div>

<div class="section">
    <h2>최신 상품</h2>

    <?php if (empty($latestProducts)): ?>
        <p class="no-data">등록된 상품이 없습니다.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($latestProducts as $product): ?>
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
                            <?php echo escape(truncate($product['description'], 80)); ?>
                        </p>
                        <p class="seller-name">by <?php echo escape($product['seller_name']); ?></p>
                        <div class="product-footer">
                            <span class="price"><?php echo formatPrice($product['price']); ?></span>
                            <a href="/solution-marketplace/public/products/detail.php?id=<?php echo $product['id']; ?>"
                               class="btn btn-sm">상세보기</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="section features">
    <h2>왜 솔루션 마켓인가요?</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <h3>다양한 상품</h3>
            <p>전자책, 소스 코드, 동영상 강의까지 다양한 디지털 상품을 만나보세요.</p>
        </div>
        <div class="feature-card">
            <h3>안전한 결제</h3>
            <p>안전하고 편리한 결제 시스템으로 신뢰할 수 있는 거래를 제공합니다.</p>
        </div>
        <div class="feature-card">
            <h3>셀러 지원</h3>
            <p>누구나 셀러로 등록하여 자신의 지식과 경험을 판매할 수 있습니다.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
