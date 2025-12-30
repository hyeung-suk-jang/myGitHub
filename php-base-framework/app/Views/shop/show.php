<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="container">
        <div class="product-detail">
            <div class="product-image">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <p class="price"><?= number_format($product['price']) ?>원</p>
                <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <p class="stock">재고: <?= $product['stock'] ?>개</p>

                <form id="addToCartForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <label for="quantity">수량:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button type="submit" class="btn">장바구니 담기</button>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        document.getElementById('addToCartForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/shop/cart/add', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('장바구니에 담았습니다.');
                } else {
                    alert('오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
