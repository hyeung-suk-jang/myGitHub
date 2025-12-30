<?php
/**
 * 토스페이먼츠 결제 연동 API
 * https://docs.tosspayments.com/
 */

require_once 'config.php';

// 토스페이먼츠 설정
define('TOSS_CLIENT_KEY', getenv('TOSS_CLIENT_KEY') ?: 'test_ck_D5GePWvyJnrK0W0k6q8gLzN97Eoq'); // 테스트 키
define('TOSS_SECRET_KEY', getenv('TOSS_SECRET_KEY') ?: 'test_sk_zXLkKEypNArWmo50nX3lmeaxYG5R'); // 테스트 키
define('TOSS_API_URL', 'https://api.tosspayments.com/v1');

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'prepare':
        if ($method === 'POST') {
            preparePayment($db, $input);
        }
        break;

    case 'confirm':
        if ($method === 'POST') {
            confirmPayment($db, $input);
        }
        break;

    case 'cancel':
        if ($method === 'POST') {
            cancelPayment($db, $input);
        }
        break;

    case 'get-client-key':
        getClientKey();
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 결제 준비 (주문 정보 생성)
 */
function preparePayment($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;

        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $db->beginTransaction();

        // 대여 정보 조회
        $stmt = $db->prepare("
            SELECT renter_id, rental_fee, deposit_amount, payment_status
            FROM rentals
            WHERE rental_id = ?
        ");
        $stmt->execute([$rentalId]);
        $rental = $stmt->fetch();

        if (!$rental) {
            throw new Exception('Rental not found');
        }

        if ($rental['renter_id'] != $userId) {
            throw new Exception('Unauthorized');
        }

        if ($rental['payment_status'] !== 'unpaid') {
            throw new Exception('Payment already processed');
        }

        $totalAmount = $rental['rental_fee'] + $rental['deposit_amount'];
        $orderId = 'ORDER_' . $rentalId . '_' . time();
        $orderName = "장비 대여 (Rental #$rentalId)";

        // 결제 정보 저장
        $stmt = $db->prepare("
            INSERT INTO payments (
                rental_id, payer_id, payment_type, amount,
                payment_method, transaction_id, status
            ) VALUES (?, ?, 'rental_fee', ?, 'toss', ?, 'pending')
        ");
        $stmt->execute([$rentalId, $userId, $totalAmount, $orderId]);
        $paymentId = $db->lastInsertId();

        $db->commit();

        sendSuccess([
            'order_id' => $orderId,
            'order_name' => $orderName,
            'amount' => $totalAmount,
            'payment_id' => $paymentId,
            'customer_email' => currentUser()['email'] ?? '',
            'customer_name' => currentUser()['username'] ?? ''
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        logError('Prepare payment error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 결제 승인 (토스페이먼츠 API 호출)
 */
function confirmPayment($db, $input) {
    $userId = requireAuth();

    try {
        $paymentKey = $input['paymentKey'] ?? null;
        $orderId = $input['orderId'] ?? null;
        $amount = $input['amount'] ?? null;

        if (!$paymentKey || !$orderId || !$amount) {
            sendError('Missing required parameters', 400);
        }

        // 주문 정보 조회
        $stmt = $db->prepare("
            SELECT p.*, r.rental_id, r.owner_id
            FROM payments p
            INNER JOIN rentals r ON p.rental_id = r.rental_id
            WHERE p.transaction_id = ? AND p.payer_id = ?
        ");
        $stmt->execute([$orderId, $userId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            throw new Exception('Payment not found');
        }

        if ($payment['amount'] != $amount) {
            throw new Exception('Amount mismatch');
        }

        // 토스페이먼츠 API 호출
        $tossResponse = callTossAPI('/payments/confirm', [
            'paymentKey' => $paymentKey,
            'orderId' => $orderId,
            'amount' => intval($amount)
        ]);

        if (!$tossResponse || !isset($tossResponse['status'])) {
            throw new Exception('Toss API call failed');
        }

        $db->beginTransaction();

        // 결제 상태 업데이트
        $stmt = $db->prepare("
            UPDATE payments
            SET status = 'completed',
                paid_at = NOW(),
                pg_response = ?
            WHERE payment_id = ?
        ");
        $stmt->execute([json_encode($tossResponse, JSON_UNESCAPED_UNICODE), $payment['payment_id']]);

        // 대여 결제 상태 업데이트
        $stmt = $db->prepare("
            UPDATE rentals
            SET payment_status = 'paid', updated_at = NOW()
            WHERE rental_id = ?
        ");
        $stmt->execute([$payment['rental_id']]);

        // 정산 레코드 생성
        $stmt = $db->prepare("
            SELECT rental_fee, platform_commission, owner_payout
            FROM rentals
            WHERE rental_id = ?
        ");
        $stmt->execute([$payment['rental_id']]);
        $rental = $stmt->fetch();

        $stmt = $db->prepare("
            INSERT INTO settlements (
                rental_id, owner_id, rental_fee, platform_commission, payout_amount, status
            ) VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $payment['rental_id'],
            $payment['owner_id'],
            $rental['rental_fee'],
            $rental['platform_commission'],
            $rental['owner_payout']
        ]);

        // 알림 생성
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '결제 완료', '결제가 완료되었습니다.', 'payment', ?)
        ");
        $stmt->execute([$userId, $payment['rental_id']]);

        // 소유자에게 알림
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '대여 결제 완료', '대여 결제가 완료되었습니다.', 'payment', ?)
        ");
        $stmt->execute([$payment['owner_id'], $payment['rental_id']]);

        $db->commit();

        logActivity($userId, 'Payment confirmed', [
            'rental_id' => $payment['rental_id'],
            'amount' => $amount
        ]);

        sendSuccess([
            'status' => 'success',
            'message' => '결제가 완료되었습니다.',
            'toss_response' => $tossResponse
        ]);

    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        logError('Confirm payment error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 결제 취소/환불
 */
function cancelPayment($db, $input) {
    requireAuth();

    try {
        $paymentKey = $input['paymentKey'] ?? null;
        $cancelReason = $input['cancelReason'] ?? '사용자 요청';

        if (!$paymentKey) {
            sendError('Payment key required', 400);
        }

        // 토스페이먼츠 API 호출
        $tossResponse = callTossAPI("/payments/$paymentKey/cancel", [
            'cancelReason' => $cancelReason
        ]);

        if (!$tossResponse || $tossResponse['status'] !== 'CANCELED') {
            throw new Exception('Payment cancellation failed');
        }

        $db->beginTransaction();

        // 결제 상태 업데이트
        $stmt = $db->prepare("
            UPDATE payments
            SET status = 'refunded',
                pg_response = ?
            WHERE transaction_id IN (
                SELECT orderId FROM (
                    SELECT JSON_UNQUOTE(JSON_EXTRACT(pg_response, '$.orderId')) as orderId
                    FROM payments
                    WHERE JSON_UNQUOTE(JSON_EXTRACT(pg_response, '$.paymentKey')) = ?
                ) as temp
            )
        ");
        $stmt->execute([json_encode($tossResponse, JSON_UNESCAPED_UNICODE), $paymentKey]);

        $db->commit();

        sendSuccess([
            'status' => 'success',
            'message' => '결제가 취소되었습니다.',
            'toss_response' => $tossResponse
        ]);

    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        logError('Cancel payment error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 클라이언트 키 조회
 */
function getClientKey() {
    sendSuccess([
        'client_key' => TOSS_CLIENT_KEY
    ]);
}

/**
 * 토스페이먼츠 API 호출 헬퍼 함수
 */
function callTossAPI($endpoint, $data) {
    $url = TOSS_API_URL . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode(TOSS_SECRET_KEY . ':'),
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        $error = json_decode($response, true);
        logError('Toss API Error: ' . json_encode($error));
        throw new Exception($error['message'] ?? 'Toss API call failed');
    }

    return json_decode($response, true);
}
