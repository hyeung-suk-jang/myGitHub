<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>쇼핑몰</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="container">
        <h1>상품 목록</h1>

        <div class="product-grid">
            <?php foreach ($products['data'] as $product): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="description"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                    <p class="price"><?= number_format($product['price']) ?>원</p>
                    <a href="/shop/<?= $product['id'] ?>" class="btn">상세보기</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $products['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $products['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
