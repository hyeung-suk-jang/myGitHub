<?php
require_once 'php/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$cart_items = get_cart_items($_SESSION['user_id']);
$total = array_sum(array_map(fn($item) => $item['discount_price'] ?: $item['price'], $cart_items));
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>장바구니 - DevBooks</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .cart-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            margin: 30px 0;
            box-shadow: var(--shadow);
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .cart-table th {
            background: var(--light-bg);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }
        .cart-table td {
            padding: 20px 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .cart-item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .cart-item-cover {
            width: 80px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 8px;
            flex-shrink: 0;
        }
        .cart-summary {
            background: var(--light-bg);
            padding: 30px;
            border-radius: 12px;
            margin-top: 30px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            border-top: 2px solid var(--border-color);
            padding-top: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="cart-container">
            <h1 style="margin-bottom: 30px;">🛒 장바구니</h1>

            <?php if (empty($cart_items)): ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <p style="font-size: 1.2rem; color: var(--text-light); margin-bottom: 20px;">
                        장바구니가 비어있습니다
                    </p>
                    <a href="books.php" class="btn btn-primary">전자책 둘러보기</a>
                </div>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>전자책</th>
                            <th>카테고리</th>
                            <th>가격</th>
                            <th>삭제</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr class="cart-item" data-cart-id="<?= $item['cart_id'] ?>" data-ebook-id="<?= $item['id'] ?>" data-price="<?= $item['discount_price'] ?: $item['price'] ?>">
                                <td>
                                    <div class="cart-item-info">
                                        <div class="cart-item-cover"></div>
                                        <div>
                                            <h3 style="margin-bottom: 5px;">
                                                <a href="book.php?id=<?= $item['id'] ?>" style="color: var(--text-primary); text-decoration: none;">
                                                    <?= htmlspecialchars($item['title']) ?>
                                                </a>
                                            </h3>
                                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                                <?= htmlspecialchars($item['author_name'] ?? 'Unknown') ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge">
                                        <?= htmlspecialchars($item['category_name'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="font-size: 1.2rem; color: var(--primary-color);">
                                        <?= format_price($item['discount_price'] ?: $item['price']) ?>
                                    </strong>
                                    <?php if ($item['discount_price']): ?>
                                        <br>
                                        <span style="text-decoration: line-through; color: var(--text-light); font-size: 0.9rem;">
                                            <?= format_price($item['price']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-outline remove-cart-item" data-cart-id="<?= $item['cart_id'] ?>" style="padding: 8px 16px; background: var(--danger-color); color: white; border: none;">
                                        삭제
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>상품 개수:</span>
                        <strong><?= count($cart_items) ?>개</strong>
                    </div>
                    <div class="summary-row summary-total">
                        <span>총 결제금액:</span>
                        <span class="cart-total"><?= format_price($total) ?></span>
                    </div>

                    <button id="checkout-btn" class="btn btn-success btn-large" style="width: 100%; margin-top: 20px;">
                        💳 결제하기
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
