<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';

Session::start();

$productId = getQuery('id');

if (!$productId) {
    Session::setFlash('error', '상품을 찾을 수 없습니다.');
    redirect('/solution-marketplace/public/products/list.php');
}

$db = Database::getInstance();

// 상품 정보 조회
$product = $db->fetchOne(
    "SELECT p.*, u.name as seller_name, s.business_name
     FROM products p
     JOIN sellers s ON p.seller_id = s.id
     JOIN users u ON s.user_id = u.id
     WHERE p.id = ? AND p.status = 'active'",
    [$productId]
);

if (!$product) {
    Session::setFlash('error', '상품을 찾을 수 없습니다.');
    redirect('/solution-marketplace/public/products/list.php');
}

// 조회수 증가
$db->execute(
    "UPDATE products SET view_count = view_count + 1 WHERE id = ?",
    [$productId]
);

// 구매 여부 확인
$hasPurchased = false;
if (Session::isLoggedIn()) {
    $purchased = $db->fetchOne(
        "SELECT oi.id FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'completed'",
        [Session::getUserId(), $productId]
    );
    $hasPurchased = !empty($purchased);
}

$pageTitle = $product['title'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="product-detail-container">
    <div class="product-detail">
        <div class="product-image-section">
            <?php if ($product['thumbnail_path']): ?>
                <img src="<?php echo escape($product['thumbnail_path']); ?>"
                     alt="<?php echo escape($product['title']); ?>"
                     class="product-image">
            <?php else: ?>
                <div class="no-image">
                    <?php
                    $typeLabels = ['ebook' => '전자책', 'source' => '솔루션 소스', 'video' => '동영상 강의'];
                    echo $typeLabels[$product['product_type']] ?? '상품';
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info-section">
            <div class="product-type-badge">
                <?php
                $typeLabels = ['ebook' => '전자책', 'source' => '솔루션 소스', 'video' => '동영상 강의'];
                echo $typeLabels[$product['product_type']] ?? $product['product_type'];
                ?>
            </div>

            <h1><?php echo escape($product['title']); ?></h1>

            <div class="seller-info">
                <p><strong>판매자:</strong> <?php echo escape($product['business_name']); ?></p>
            </div>

            <div class="product-stats">
                <span>조회 <?php echo number_format($product['view_count']); ?></span>
                <span>다운로드 <?php echo number_format($product['download_count']); ?></span>
            </div>

            <div class="product-price">
                <span class="price-label">가격</span>
                <span class="price"><?php echo formatPrice($product['price']); ?></span>
            </div>

            <div class="product-actions">
                <?php if ($hasPurchased): ?>
                    <a href="/solution-marketplace/public/download.php?product_id=<?php echo $product['id']; ?>"
                       class="btn btn-success btn-large">
                        다운로드하기
                    </a>
                    <p class="purchased-notice">이미 구매하신 상품입니다.</p>
                <?php else: ?>
                    <?php if (Session::isLoggedIn()): ?>
                        <form method="POST" action="/solution-marketplace/api/cart/add.php" class="add-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-secondary btn-large">
                                장바구니에 담기
                            </button>
                        </form>
                        <a href="/solution-marketplace/public/payment/checkout.php?product_id=<?php echo $product['id']; ?>"
                           class="btn btn-primary btn-large">
                            바로 구매하기
                        </a>
                    <?php else: ?>
                        <p class="login-notice">구매하려면 <a href="/solution-marketplace/public/login.php">로그인</a>이 필요합니다.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="product-description-section">
        <h2>상품 설명</h2>
        <div class="description-content">
            <?php echo nl2br(escape($product['description'])); ?>
        </div>
    </div>

    <div class="related-products-section">
        <h2>같은 카테고리의 다른 상품</h2>
        <?php
        $relatedProducts = $db->fetchAll(
            "SELECT p.*, u.name as seller_name
             FROM products p
             JOIN sellers s ON p.seller_id = s.id
             JOIN users u ON s.user_id = u.id
             WHERE p.product_type = ? AND p.id != ? AND p.status = 'active'
             ORDER BY RAND()
             LIMIT 4",
            [$product['product_type'], $productId]
        );
        ?>

        <?php if (empty($relatedProducts)): ?>
            <p class="no-data">관련 상품이 없습니다.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $related): ?>
                    <div class="product-card">
                        <div class="product-thumbnail">
                            <?php if ($related['thumbnail_path']): ?>
                                <img src="<?php echo escape($related['thumbnail_path']); ?>"
                                     alt="<?php echo escape($related['title']); ?>">
                            <?php else: ?>
                                <div class="no-thumbnail">
                                    <?php echo $typeLabels[$related['product_type']] ?? '상품'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo escape($related['title']); ?></h3>
                            <p class="seller-name">by <?php echo escape($related['seller_name']); ?></p>
                            <div class="product-footer">
                                <span class="price"><?php echo formatPrice($related['price']); ?></span>
                                <a href="/solution-marketplace/public/products/detail.php?id=<?php echo $related['id']; ?>"
                                   class="btn btn-sm">상세보기</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
