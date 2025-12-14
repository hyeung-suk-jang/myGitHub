<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

Session::start();
requireLogin();

$pageTitle = '결제하기';

$db = Database::getInstance();
$userId = Session::getUserId();

$fromCart = getPost('from_cart') || getQuery('from_cart');
$singleProductId = getQuery('product_id');

$orderItems = [];
$totalAmount = 0;

if ($fromCart) {
    // 장바구니에서 결제
    $cartItems = $db->fetchAll(
        "SELECT c.id as cart_id, p.*, s.id as seller_id
         FROM cart c
         JOIN products p ON c.product_id = p.id
         JOIN sellers s ON p.seller_id = s.id
         WHERE c.user_id = ? AND p.status = 'active'",
        [$userId]
    );

    if (empty($cartItems)) {
        Session::setFlash('error', '장바구니가 비어있습니다.');
        redirect('/solution-marketplace/public/cart.php');
    }

    $orderItems = $cartItems;
} elseif ($singleProductId) {
    // 단일 상품 결제
    $product = $db->fetchOne(
        "SELECT p.*, s.id as seller_id
         FROM products p
         JOIN sellers s ON p.seller_id = s.id
         WHERE p.id = ? AND p.status = 'active'",
        [$singleProductId]
    );

    if (!$product) {
        Session::setFlash('error', '상품을 찾을 수 없습니다.');
        redirect('/solution-marketplace/public/products/list.php');
    }

    $orderItems = [$product];
} else {
    Session::setFlash('error', '잘못된 요청입니다.');
    redirect('/solution-marketplace/public/products/list.php');
}

foreach ($orderItems as $item) {
    $totalAmount += $item['price'];
}

// POST 요청 처리 (결제 실행)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('confirm_payment')) {
    $db->beginTransaction();

    try {
        // 주문 생성
        $orderNumber = generateOrderNumber();
        $orderId = $db->insert(
            "INSERT INTO orders (user_id, order_number, total_amount, order_status)
             VALUES (?, ?, ?, 'pending')",
            [$userId, $orderNumber, $totalAmount]
        );

        // 주문 상품 추가
        foreach ($orderItems as $item) {
            $db->insert(
                "INSERT INTO order_items (order_id, product_id, seller_id, price)
                 VALUES (?, ?, ?, ?)",
                [$orderId, $item['id'], $item['seller_id'], $item['price']]
            );
        }

        // 결제 정보 생성 (간단한 시뮬레이션)
        $paymentId = $db->insert(
            "INSERT INTO payments (order_id, payment_method, payment_status, paid_amount, paid_at)
             VALUES (?, 'card', 'completed', ?, NOW())",
            [$orderId, $totalAmount]
        );

        // 주문 상태 업데이트
        $db->execute(
            "UPDATE orders SET order_status = 'completed' WHERE id = ?",
            [$orderId]
        );

        // 장바구니에서 구매한 경우 장바구니 비우기
        if ($fromCart) {
            $db->execute(
                "DELETE FROM cart WHERE user_id = ?",
                [$userId]
            );
        }

        // 상품 다운로드 수 증가
        foreach ($orderItems as $item) {
            $db->execute(
                "UPDATE products SET download_count = download_count + 1 WHERE id = ?",
                [$item['id']]
            );
        }

        $db->commit();

        Session::setFlash('success', '결제가 완료되었습니다!');
        redirect('/solution-marketplace/public/payment/complete.php?order_id=' . $orderId);
    } catch (Exception $e) {
        $db->rollback();
        $errors = ['결제 처리 중 오류가 발생했습니다: ' . $e->getMessage()];
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="checkout-container">
    <h2>결제하기</h2>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="checkout-content">
        <div class="order-items-section">
            <h3>주문 상품</h3>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>상품명</th>
                        <th>타입</th>
                        <th>가격</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo escape($item['title']); ?></td>
                            <td>
                                <?php
                                $typeLabels = ['ebook' => '전자책', 'source' => '솔루션 소스', 'video' => '동영상 강의'];
                                echo $typeLabels[$item['product_type']] ?? $item['product_type'];
                                ?>
                            </td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="payment-section">
            <div class="payment-box">
                <h3>결제 정보</h3>

                <div class="payment-summary">
                    <div class="summary-row">
                        <span>상품 개수:</span>
                        <span><?php echo count($orderItems); ?>개</span>
                    </div>
                    <div class="summary-row total">
                        <span>총 결제 금액:</span>
                        <span class="total-price"><?php echo formatPrice($totalAmount); ?></span>
                    </div>
                </div>

                <form method="POST" action="" class="payment-form">
                    <input type="hidden" name="confirm_payment" value="1">
                    <?php if ($fromCart): ?>
                        <input type="hidden" name="from_cart" value="1">
                    <?php endif; ?>

                    <div class="payment-notice">
                        <p>* 이 사이트는 데모입니다. 실제 결제가 이루어지지 않습니다.</p>
                        <p>* 결제 버튼을 클릭하면 즉시 구매가 완료됩니다.</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large btn-block">
                        <?php echo formatPrice($totalAmount); ?> 결제하기
                    </button>
                </form>

                <div class="payment-methods">
                    <p class="payment-method-label">결제 수단</p>
                    <div class="methods">
                        <span class="method active">신용카드</span>
                        <span class="method">계좌이체</span>
                        <span class="method">간편결제</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
