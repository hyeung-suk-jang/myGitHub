<?php
/**
 * 결제 및 정산 API
 * 결제 처리, 정산 관리
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create-payment':
        if ($method === 'POST') {
            createPayment($db, $input);
        }
        break;

    case 'confirm-payment':
        if ($method === 'POST') {
            confirmPayment($db, $input);
        }
        break;

    case 'refund':
        if ($method === 'POST') {
            processRefund($db, $input);
        }
        break;

    case 'settlements':
        getSettlements($db);
        break;

    case 'my-settlements':
        getMySettlements($db);
        break;

    case 'request-settlement':
        if ($method === 'POST') {
            requestSettlement($db, $input);
        }
        break;

    case 'process-settlement':
        if ($method === 'POST') {
            processSettlement($db, $input);
        }
        break;

    case 'payment-history':
        getPaymentHistory($db);
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 결제 생성 (에스크로)
 */
function createPayment($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        $paymentMethod = sanitizeInput($input['payment_method'] ?? 'card');

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

        // 실제 PG 연동은 여기서 처리
        // 여기서는 시뮬레이션만 수행
        $transactionId = 'TXN_' . uniqid() . '_' . time();

        // 대여료 결제 기록
        $stmt = $db->prepare("
            INSERT INTO payments (rental_id, payer_id, payment_type, amount, payment_method, transaction_id, status)
            VALUES (?, ?, 'rental_fee', ?, ?, ?, 'pending')
        ");
        $stmt->execute([$rentalId, $userId, $rental['rental_fee'], $paymentMethod, $transactionId . '_RENTAL']);

        $rentalPaymentId = $db->lastInsertId();

        // 보증금 결제 기록
        $stmt = $db->prepare("
            INSERT INTO payments (rental_id, payer_id, payment_type, amount, payment_method, transaction_id, status)
            VALUES (?, ?, 'deposit', ?, ?, ?, 'pending')
        ");
        $stmt->execute([$rentalId, $userId, $rental['deposit_amount'], $paymentMethod, $transactionId . '_DEPOSIT']);

        $depositPaymentId = $db->lastInsertId();

        $db->commit();

        logActivity($userId, 'Payment created', [
            'rental_id' => $rentalId,
            'amount' => $totalAmount
        ]);

        sendSuccess([
            'rental_payment_id' => $rentalPaymentId,
            'deposit_payment_id' => $depositPaymentId,
            'transaction_id' => $transactionId,
            'total_amount' => $totalAmount,
            'message' => 'Payment initiated. Please confirm payment.'
        ], 'Payment created successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Create payment error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 결제 확인 (PG사 콜백 처리)
 */
function confirmPayment($db, $input) {
    try {
        $transactionId = sanitizeInput($input['transaction_id'] ?? '');
        $status = sanitizeInput($input['status'] ?? 'completed');

        if (!$transactionId) {
            sendError('Transaction ID required', 400);
        }

        $db->beginTransaction();

        // 거래 ID로 결제 조회
        $stmt = $db->prepare("
            SELECT payment_id, rental_id, payment_type, status
            FROM payments
            WHERE transaction_id LIKE ?
            LIMIT 1
        ");
        $stmt->execute([$transactionId . '%']);
        $payment = $stmt->fetch();

        if (!$payment) {
            throw new Exception('Payment not found');
        }

        if ($payment['status'] !== 'pending') {
            throw new Exception('Payment already processed');
        }

        // 결제 상태 업데이트
        $stmt = $db->prepare("
            UPDATE payments
            SET status = ?, paid_at = NOW()
            WHERE transaction_id LIKE ?
        ");
        $stmt->execute([$status, $transactionId . '%']);

        // 대여료와 보증금 모두 완료되었는지 확인
        $stmt = $db->prepare("
            SELECT COUNT(*) as completed_count
            FROM payments
            WHERE rental_id = ? AND status = 'completed'
        ");
        $stmt->execute([$payment['rental_id']]);
        $result = $stmt->fetch();

        // 두 결제 모두 완료되면 대여 상태 업데이트
        if ($result['completed_count'] >= 2) {
            $stmt = $db->prepare("
                UPDATE rentals
                SET payment_status = 'paid', updated_at = NOW()
                WHERE rental_id = ?
            ");
            $stmt->execute([$payment['rental_id']]);

            // 정산 레코드 생성
            createSettlementRecord($db, $payment['rental_id']);
        }

        $db->commit();

        sendSuccess([], 'Payment confirmed successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Confirm payment error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 환불 처리
 */
function processRefund($db, $input) {
    requireAdmin();

    try {
        $paymentId = $input['payment_id'] ?? null;
        $refundAmount = floatval($input['refund_amount'] ?? 0);
        $reason = sanitizeInput($input['reason'] ?? '');

        if (!$paymentId || $refundAmount <= 0) {
            sendError('Invalid input', 400);
        }

        $db->beginTransaction();

        $stmt = $db->prepare("
            SELECT rental_id, payer_id, amount, status
            FROM payments
            WHERE payment_id = ?
        ");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();

        if (!$payment || $payment['status'] !== 'completed') {
            throw new Exception('Payment not found or not eligible for refund');
        }

        if ($refundAmount > $payment['amount']) {
            throw new Exception('Refund amount exceeds payment amount');
        }

        // 환불 기록
        $stmt = $db->prepare("
            INSERT INTO payments (rental_id, payer_id, payment_type, amount, status, paid_at)
            VALUES (?, ?, 'refund', ?, 'completed', NOW())
        ");
        $stmt->execute([$payment['rental_id'], $payment['payer_id'], -$refundAmount]);

        // 원 결제 상태 업데이트
        $newStatus = $refundAmount == $payment['amount'] ? 'refunded' : 'partially_refunded';
        $stmt = $db->prepare("UPDATE payments SET status = ? WHERE payment_id = ?");
        $stmt->execute([$newStatus, $paymentId]);

        // 알림 생성
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '환불 완료', ?, 'payment', ?)
        ");
        $stmt->execute([$payment['payer_id'], $reason ?: '환불이 완료되었습니다.', $payment['rental_id']]);

        $db->commit();

        sendSuccess([], 'Refund processed successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Process refund error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 정산 레코드 생성 (내부 함수)
 */
function createSettlementRecord($db, $rentalId) {
    $stmt = $db->prepare("
        SELECT owner_id, rental_fee, platform_commission, owner_payout
        FROM rentals
        WHERE rental_id = ?
    ");
    $stmt->execute([$rentalId]);
    $rental = $stmt->fetch();

    if (!$rental) {
        return false;
    }

    $stmt = $db->prepare("
        INSERT INTO settlements (
            rental_id, owner_id, rental_fee, platform_commission, payout_amount, status
        ) VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $rentalId,
        $rental['owner_id'],
        $rental['rental_fee'],
        $rental['platform_commission'],
        $rental['owner_payout']
    ]);

    return true;
}

/**
 * 정산 목록 조회 (관리자)
 */
function getSettlements($db) {
    requireAdmin();

    try {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $status = $_GET['status'] ?? null;

        $where = [];
        $params = [];

        if ($status) {
            $where[] = "s.status = ?";
            $params[] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // 전체 개수
        $countSql = "SELECT COUNT(*) as total FROM settlements s $whereClause";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];

        // 목록 조회
        $sql = "
            SELECT
                s.*,
                u.username as owner_name,
                u.email as owner_email,
                r.equipment_id,
                e.equipment_name
            FROM settlements s
            LEFT JOIN users u ON s.owner_id = u.user_id
            LEFT JOIN rentals r ON s.rental_id = r.rental_id
            LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
            $whereClause
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $settlements = $stmt->fetchAll();

        sendSuccess([
            'settlements' => $settlements,
            'pagination' => [
                'total' => intval($total),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);

    } catch (Exception $e) {
        logError('Get settlements error: ' . $e->getMessage());
        sendError('Failed to get settlements', 500);
    }
}

/**
 * 내 정산 목록 조회
 */
function getMySettlements($db) {
    $userId = requireAuth();

    try {
        $stmt = $db->prepare("
            SELECT
                s.*,
                r.equipment_id,
                e.equipment_name
            FROM settlements s
            LEFT JOIN rentals r ON s.rental_id = r.rental_id
            LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
            WHERE s.owner_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        $settlements = $stmt->fetchAll();

        // 통계 계산
        $stmt = $db->prepare("
            SELECT
                COUNT(*) as total_count,
                SUM(CASE WHEN status = 'pending' THEN payout_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'completed' THEN payout_amount ELSE 0 END) as completed_amount
            FROM settlements
            WHERE owner_id = ?
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();

        sendSuccess([
            'settlements' => $settlements,
            'statistics' => $stats
        ]);

    } catch (Exception $e) {
        logError('Get my settlements error: ' . $e->getMessage());
        sendError('Failed to get settlements', 500);
    }
}

/**
 * 정산 요청 (출금 신청)
 */
function requestSettlement($db, $input) {
    $userId = requireAuth();

    try {
        $settlementId = $input['settlement_id'] ?? null;
        $bankAccount = sanitizeInput($input['bank_account'] ?? '');
        $bankName = sanitizeInput($input['bank_name'] ?? '');
        $accountHolder = sanitizeInput($input['account_holder'] ?? '');

        if (!$settlementId || !$bankAccount || !$bankName || !$accountHolder) {
            sendError('Missing required fields', 400);
        }

        $stmt = $db->prepare("
            SELECT owner_id, status
            FROM settlements
            WHERE settlement_id = ?
        ");
        $stmt->execute([$settlementId]);
        $settlement = $stmt->fetch();

        if (!$settlement || $settlement['owner_id'] != $userId) {
            sendError('Unauthorized', 403);
        }

        if ($settlement['status'] !== 'pending') {
            sendError('Settlement cannot be requested', 400);
        }

        $stmt = $db->prepare("
            UPDATE settlements
            SET status = 'processing',
                bank_account = ?,
                bank_name = ?,
                account_holder = ?,
                updated_at = NOW()
            WHERE settlement_id = ?
        ");

        $stmt->execute([$bankAccount, $bankName, $accountHolder, $settlementId]);

        logActivity($userId, 'Settlement requested', ['settlement_id' => $settlementId]);

        sendSuccess([], 'Settlement request submitted successfully');

    } catch (Exception $e) {
        logError('Request settlement error: ' . $e->getMessage());
        sendError('Failed to request settlement', 500);
    }
}

/**
 * 정산 처리 (관리자)
 */
function processSettlement($db, $input) {
    requireAdmin();

    try {
        $settlementId = $input['settlement_id'] ?? null;
        $action = $input['action'] ?? 'complete'; // 'complete' or 'fail'

        if (!$settlementId) {
            sendError('Settlement ID required', 400);
        }

        $db->beginTransaction();

        $stmt = $db->prepare("
            SELECT owner_id, status, payout_amount
            FROM settlements
            WHERE settlement_id = ?
        ");
        $stmt->execute([$settlementId]);
        $settlement = $stmt->fetch();

        if (!$settlement || $settlement['status'] !== 'processing') {
            throw new Exception('Invalid settlement or status');
        }

        $newStatus = $action === 'complete' ? 'completed' : 'failed';
        $payoutDate = $action === 'complete' ? date('Y-m-d') : null;

        $stmt = $db->prepare("
            UPDATE settlements
            SET status = ?, payout_date = ?, updated_at = NOW()
            WHERE settlement_id = ?
        ");
        $stmt->execute([$newStatus, $payoutDate, $settlementId]);

        // 알림 생성
        $message = $action === 'complete'
            ? '정산이 완료되었습니다. 입금을 확인해주세요.'
            : '정산 처리가 실패했습니다. 계좌 정보를 확인해주세요.';

        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '정산 처리 알림', ?, 'settlement', ?)
        ");
        $stmt->execute([$settlement['owner_id'], $message, $settlementId]);

        $db->commit();

        sendSuccess([], 'Settlement processed successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Process settlement error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 결제 내역 조회
 */
function getPaymentHistory($db) {
    $userId = requireAuth();

    try {
        $type = $_GET['type'] ?? 'all'; // 'payer' or 'receiver' or 'all'

        $where = [];
        $params = [];

        if ($type === 'payer') {
            $where[] = "p.payer_id = ?";
            $params[] = $userId;
        } elseif ($type === 'receiver') {
            $where[] = "r.owner_id = ?";
            $params[] = $userId;
        } else {
            $where[] = "(p.payer_id = ? OR r.owner_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $stmt = $db->prepare("
            SELECT
                p.*,
                r.equipment_id,
                e.equipment_name,
                IF(p.payer_id = ?, 'outgoing', 'incoming') as direction
            FROM payments p
            LEFT JOIN rentals r ON p.rental_id = r.rental_id
            LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
            $whereClause
            ORDER BY p.created_at DESC
        ");

        $stmt->execute(array_merge([$userId], $params));
        $payments = $stmt->fetchAll();

        sendSuccess(['payments' => $payments]);

    } catch (Exception $e) {
        logError('Get payment history error: ' . $e->getMessage());
        sendError('Failed to get payment history', 500);
    }
}
