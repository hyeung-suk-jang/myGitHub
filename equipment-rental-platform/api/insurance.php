<?php
/**
 * 보험 API 연동
 * 대여 기간 중 장비 파손/분실에 대비한 단기 보험
 * 실제 보험사 API 연동 구조 (예: DB손해보험, 삼성화재 등)
 */

require_once 'config.php';

// 보험 API 설정 (실제 환경에서는 환경변수로 관리)
define('INSURANCE_API_URL', getenv('INSURANCE_API_URL') ?: 'https://api.insurance-company.com/v1');
define('INSURANCE_API_KEY', getenv('INSURANCE_API_KEY') ?: 'test_insurance_api_key');
define('INSURANCE_COMPANY_ID', getenv('INSURANCE_COMPANY_ID') ?: 'equiprent_partner');

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'quote':
        if ($method === 'POST') {
            getInsuranceQuote($db, $input);
        }
        break;

    case 'purchase':
        if ($method === 'POST') {
            purchaseInsurance($db, $input);
        }
        break;

    case 'claim':
        if ($method === 'POST') {
            fileInsuranceClaim($db, $input);
        }
        break;

    case 'get-policy':
        getInsurancePolicy($db);
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 보험료 견적 조회
 */
function getInsuranceQuote($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;

        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        // 대여 정보 조회
        $stmt = $db->prepare("
            SELECT
                r.*,
                e.equipment_name,
                e.brand,
                e.model_name,
                e.purchase_price,
                e.condition_grade
            FROM rentals r
            INNER JOIN equipment e ON r.equipment_id = e.equipment_id
            WHERE r.rental_id = ? AND (r.renter_id = ? OR r.owner_id = ?)
        ");
        $stmt->execute([$rentalId, $userId, $userId]);
        $rental = $stmt->fetch();

        if (!$rental) {
            sendError('Rental not found', 404);
        }

        // 보험료 계산 (간단한 알고리즘)
        $equipmentValue = $rental['purchase_price'] ?: $rental['deposit_amount'] * 2;
        $rentalDays = $rental['total_days'];

        // 기본 보험료: 장비 가치의 1% + 일일 추가 0.1%
        $basePremium = $equipmentValue * 0.01;
        $dailyPremium = $equipmentValue * 0.001 * $rentalDays;
        $totalPremium = $basePremium + $dailyPremium;

        // 등급에 따른 할인/할증
        $gradeMultiplier = [
            'S' => 0.9,  // 10% 할인
            'A' => 1.0,  // 기본
            'B' => 1.1,  // 10% 할증
            'C' => 1.2   // 20% 할증
        ];
        $totalPremium *= ($gradeMultiplier[$rental['condition_grade']] ?? 1.0);

        // 실제 환경에서는 외부 보험 API 호출
        // $apiResponse = callInsuranceAPI('/quote', [...]);

        $quote = [
            'rental_id' => $rentalId,
            'equipment_value' => $equipmentValue,
            'rental_days' => $rentalDays,
            'premium' => round($totalPremium, 2),
            'coverage_amount' => $equipmentValue,
            'valid_until' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'quote_id' => 'QUOTE_' . $rentalId . '_' . time()
        ];

        sendSuccess($quote);

    } catch (Exception $e) {
        logError('Insurance quote error: ' . $e->getMessage());
        sendError('Failed to get insurance quote', 500);
    }
}

/**
 * 보험 가입
 */
function purchaseInsurance($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        $quoteId = $input['quote_id'] ?? null;
        $premium = $input['premium'] ?? null;
        $coverageAmount = $input['coverage_amount'] ?? null;

        if (!$rentalId || !$quoteId || !$premium || !$coverageAmount) {
            sendError('Missing required parameters', 400);
        }

        $db->beginTransaction();

        // 대여 정보 확인
        $stmt = $db->prepare("
            SELECT start_date, end_date
            FROM rentals
            WHERE rental_id = ? AND (renter_id = ? OR owner_id = ?)
        ");
        $stmt->execute([$rentalId, $userId, $userId]);
        $rental = $stmt->fetch();

        if (!$rental) {
            throw new Exception('Rental not found');
        }

        // 실제 환경에서는 외부 보험 API 호출
        // $apiResponse = callInsuranceAPI('/purchase', [...]);
        $policyNumber = 'INS_' . $rentalId . '_' . time();

        // 보험 정보 저장
        $stmt = $db->prepare("
            INSERT INTO insurance (
                rental_id, policy_number, provider, coverage_amount,
                premium, start_date, end_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        $stmt->execute([
            $rentalId,
            $policyNumber,
            'Partner Insurance Co.',
            $coverageAmount,
            $premium,
            $rental['start_date'],
            $rental['end_date']
        ]);

        $insuranceId = $db->lastInsertId();

        // 결제 기록 (보험료)
        $stmt = $db->prepare("
            INSERT INTO payments (
                rental_id, payer_id, payment_type, amount,
                payment_method, transaction_id, status, paid_at
            ) VALUES (?, ?, 'insurance', ?, 'card', ?, 'completed', NOW())
        ");
        $stmt->execute([$rentalId, $userId, $premium, 'INS_PAY_' . $insuranceId]);

        $db->commit();

        logActivity($userId, 'Insurance purchased', [
            'rental_id' => $rentalId,
            'policy_number' => $policyNumber
        ]);

        sendSuccess([
            'insurance_id' => $insuranceId,
            'policy_number' => $policyNumber,
            'coverage_amount' => $coverageAmount,
            'premium' => $premium,
            'status' => 'active'
        ], 'Insurance purchased successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Purchase insurance error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 보험 청구
 */
function fileInsuranceClaim($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        $claimReason = sanitizeInput($input['claim_reason'] ?? '');
        $damageDescription = sanitizeInput($input['damage_description'] ?? '');
        $claimAmount = floatval($input['claim_amount'] ?? 0);

        if (!$rentalId || !$claimReason || $claimAmount <= 0) {
            sendError('Missing required parameters', 400);
        }

        $db->beginTransaction();

        // 보험 정보 조회
        $stmt = $db->prepare("
            SELECT i.*, r.renter_id, r.owner_id
            FROM insurance i
            INNER JOIN rentals r ON i.rental_id = r.rental_id
            WHERE i.rental_id = ? AND i.status = 'active'
        ");
        $stmt->execute([$rentalId]);
        $insurance = $stmt->fetch();

        if (!$insurance) {
            throw new Exception('No active insurance found for this rental');
        }

        if ($insurance['renter_id'] != $userId && $insurance['owner_id'] != $userId) {
            throw new Exception('Unauthorized');
        }

        if ($claimAmount > $insurance['coverage_amount']) {
            throw new Exception('Claim amount exceeds coverage');
        }

        // 실제 환경에서는 외부 보험 API 호출
        // $apiResponse = callInsuranceAPI('/claim', [...]);

        // 분쟁 기록 생성
        $stmt = $db->prepare("
            INSERT INTO disputes (
                rental_id, reporter_id, dispute_type, description, status
            ) VALUES (?, ?, 'damage', ?, 'open')
        ");
        $stmt->execute([$rentalId, $userId, $damageDescription]);

        // 보험 상태 업데이트
        $stmt = $db->prepare("
            UPDATE insurance
            SET status = 'claimed'
            WHERE insurance_id = ?
        ");
        $stmt->execute([$insurance['insurance_id']]);

        $db->commit();

        sendSuccess([
            'claim_status' => 'submitted',
            'message' => '보험 청구가 접수되었습니다. 심사 후 연락드리겠습니다.'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        logError('Insurance claim error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 보험 정보 조회
 */
function getInsurancePolicy($db) {
    $userId = requireAuth();

    try {
        $rentalId = $_GET['rental_id'] ?? null;

        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $stmt = $db->prepare("
            SELECT i.*
            FROM insurance i
            INNER JOIN rentals r ON i.rental_id = r.rental_id
            WHERE i.rental_id = ?
            AND (r.renter_id = ? OR r.owner_id = ?)
        ");
        $stmt->execute([$rentalId, $userId, $userId]);
        $policy = $stmt->fetch();

        if (!$policy) {
            sendError('Insurance policy not found', 404);
        }

        sendSuccess($policy);

    } catch (Exception $e) {
        logError('Get insurance policy error: ' . $e->getMessage());
        sendError('Failed to get insurance policy', 500);
    }
}

/**
 * 외부 보험 API 호출 헬퍼 (실제 구현 필요)
 */
function callInsuranceAPI($endpoint, $data) {
    $url = INSURANCE_API_URL . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . INSURANCE_API_KEY,
        'Content-Type: application/json',
        'X-Company-ID: ' . INSURANCE_COMPANY_ID
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Insurance API call failed');
    }

    return json_decode($response, true);
}
