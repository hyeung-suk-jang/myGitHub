<?php
/**
 * 대여 관리 API
 * 예약, 승인, 검수, 반납 관리
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        if ($method === 'POST') {
            createRental($db, $input);
        }
        break;

    case 'list':
        getRentalList($db);
        break;

    case 'detail':
        getRentalDetail($db);
        break;

    case 'my-rentals':
        getMyRentals($db);
        break;

    case 'approve':
        if ($method === 'PUT') {
            approveRental($db, $input);
        }
        break;

    case 'reject':
        if ($method === 'PUT') {
            rejectRental($db, $input);
        }
        break;

    case 'cancel':
        if ($method === 'PUT') {
            cancelRental($db, $input);
        }
        break;

    case 'confirm-pickup':
        if ($method === 'PUT') {
            confirmPickup($db, $input);
        }
        break;

    case 'confirm-return':
        if ($method === 'PUT') {
            confirmReturn($db, $input);
        }
        break;

    case 'create-inspection':
        if ($method === 'POST') {
            createInspection($db, $input);
        }
        break;

    case 'upload-inspection-image':
        if ($method === 'POST') {
            uploadInspectionImage($db);
        }
        break;

    case 'review':
        if ($method === 'POST') {
            createReview($db, $input);
        }
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 대여 신청
 */
function createRental($db, $input) {
    $userId = requireAuth();

    try {
        $db->beginTransaction();

        // 입력 검증
        $equipmentId = $input['equipment_id'] ?? null;
        $startDate = $input['start_date'] ?? null;
        $endDate = $input['end_date'] ?? null;
        $pickupMethod = $input['pickup_method'] ?? 'delivery';

        if (!$equipmentId || !$startDate || !$endDate) {
            throw new Exception('Missing required fields');
        }

        if (!validateDate($startDate) || !validateDate($endDate)) {
            throw new Exception('Invalid date format');
        }

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $now = new DateTime();

        if ($start < $now || $end < $start) {
            throw new Exception('Invalid rental period');
        }

        $totalDays = $start->diff($end)->days + 1;

        // 장비 정보 조회
        $stmt = $db->prepare("
            SELECT owner_id, daily_rate, deposit_amount, status
            FROM equipment
            WHERE equipment_id = ?
        ");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch();

        if (!$equipment) {
            throw new Exception('Equipment not found');
        }

        if ($equipment['status'] !== 'available') {
            throw new Exception('Equipment is not available');
        }

        if ($equipment['owner_id'] == $userId) {
            throw new Exception('Cannot rent your own equipment');
        }

        // 날짜 중복 확인
        $stmt = $db->prepare("
            SELECT rental_id
            FROM rentals
            WHERE equipment_id = ?
            AND status IN ('approved', 'active')
            AND (
                (start_date <= ? AND end_date >= ?)
                OR (start_date <= ? AND end_date >= ?)
                OR (start_date >= ? AND end_date <= ?)
            )
        ");
        $stmt->execute([
            $equipmentId,
            $startDate, $startDate,
            $endDate, $endDate,
            $startDate, $endDate
        ]);

        if ($stmt->fetch()) {
            throw new Exception('Equipment is already booked for this period');
        }

        // 비용 계산
        $rentalFee = $equipment['daily_rate'] * $totalDays;
        $platformCommission = $rentalFee * PLATFORM_COMMISSION_RATE;
        $ownerPayout = $rentalFee - $platformCommission;

        // 대여 생성
        $stmt = $db->prepare("
            INSERT INTO rentals (
                equipment_id, renter_id, owner_id, start_date, end_date,
                daily_rate, total_days, rental_fee, deposit_amount,
                platform_commission, owner_payout, pickup_method
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $equipmentId,
            $userId,
            $equipment['owner_id'],
            $startDate,
            $endDate,
            $equipment['daily_rate'],
            $totalDays,
            $rentalFee,
            $equipment['deposit_amount'],
            $platformCommission,
            $ownerPayout,
            $pickupMethod
        ]);

        $rentalId = $db->lastInsertId();

        // 장비 상태 업데이트
        $stmt = $db->prepare("UPDATE equipment SET status = 'reserved' WHERE equipment_id = ?");
        $stmt->execute([$equipmentId]);

        // 알림 생성 (소유자에게)
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '새로운 대여 신청', '장비 대여 신청이 접수되었습니다.', 'rental', ?)
        ");
        $stmt->execute([$equipment['owner_id'], $rentalId]);

        $db->commit();

        logActivity($userId, 'Rental created', ['rental_id' => $rentalId]);

        sendSuccess([
            'rental_id' => $rentalId,
            'rental_fee' => $rentalFee,
            'deposit_amount' => $equipment['deposit_amount'],
            'total_amount' => $rentalFee + $equipment['deposit_amount']
        ], 'Rental request created successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Create rental error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 대여 목록 조회 (관리자용)
 */
function getRentalList($db) {
    requireAdmin();

    try {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $status = $_GET['status'] ?? null;

        $where = [];
        $params = [];

        if ($status) {
            $where[] = "r.status = ?";
            $params[] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // 전체 개수
        $countSql = "SELECT COUNT(*) as total FROM rentals r $whereClause";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];

        // 목록 조회
        $sql = "
            SELECT
                r.*,
                e.equipment_name,
                renter.username as renter_name,
                owner.username as owner_name
            FROM rentals r
            LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
            LEFT JOIN users renter ON r.renter_id = renter.user_id
            LEFT JOIN users owner ON r.owner_id = owner.user_id
            $whereClause
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rentals = $stmt->fetchAll();

        sendSuccess([
            'rentals' => $rentals,
            'pagination' => [
                'total' => intval($total),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);

    } catch (Exception $e) {
        logError('Get rental list error: ' . $e->getMessage());
        sendError('Failed to get rental list', 500);
    }
}

/**
 * 대여 상세 조회
 */
function getRentalDetail($db) {
    $userId = requireAuth();

    try {
        $rentalId = $_GET['id'] ?? null;
        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $stmt = $db->prepare("
            SELECT
                r.*,
                e.equipment_name, e.model_name, e.brand,
                renter.username as renter_name, renter.phone as renter_phone,
                owner.username as owner_name, owner.phone as owner_phone
            FROM rentals r
            LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
            LEFT JOIN users renter ON r.renter_id = renter.user_id
            LEFT JOIN users owner ON r.owner_id = owner.user_id
            WHERE r.rental_id = ?
        ");
        $stmt->execute([$rentalId]);
        $rental = $stmt->fetch();

        if (!$rental) {
            sendError('Rental not found', 404);
        }

        // 권한 확인
        $isOwner = $rental['owner_id'] == $userId;
        $isRenter = $rental['renter_id'] == $userId;
        $isAdmin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';

        if (!$isOwner && !$isRenter && !$isAdmin) {
            sendError('Unauthorized', 403);
        }

        // 검수 리포트 조회
        $stmt = $db->prepare("
            SELECT
                ir.*,
                u.username as inspector_name
            FROM inspection_reports ir
            LEFT JOIN users u ON ir.inspector_id = u.user_id
            WHERE ir.rental_id = ?
            ORDER BY ir.created_at DESC
        ");
        $stmt->execute([$rentalId]);
        $rental['inspections'] = $stmt->fetchAll();

        // 결제 정보 조회
        $stmt = $db->prepare("
            SELECT * FROM payments
            WHERE rental_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$rentalId]);
        $rental['payments'] = $stmt->fetchAll();

        sendSuccess($rental);

    } catch (Exception $e) {
        logError('Get rental detail error: ' . $e->getMessage());
        sendError('Failed to get rental detail', 500);
    }
}

/**
 * 내 대여 목록 조회
 */
function getMyRentals($db) {
    $userId = requireAuth();

    try {
        $type = $_GET['type'] ?? 'renter'; // 'renter' or 'owner'

        $whereClause = $type === 'owner' ? 'r.owner_id = ?' : 'r.renter_id = ?';

        $stmt = $db->prepare("
            SELECT
                r.*,
                e.equipment_name, e.model_name,
                (SELECT image_path FROM equipment_images WHERE equipment_id = e.equipment_id AND is_primary = 1 LIMIT 1) as equipment_image,
                IF(r.owner_id = ?, owner.username, renter.username) as counterpart_name
            FROM rentals r
            LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
            LEFT JOIN users renter ON r.renter_id = renter.user_id
            LEFT JOIN users owner ON r.owner_id = owner.user_id
            WHERE $whereClause
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId, $userId]);
        $rentals = $stmt->fetchAll();

        sendSuccess(['rentals' => $rentals]);

    } catch (Exception $e) {
        logError('Get my rentals error: ' . $e->getMessage());
        sendError('Failed to get rentals', 500);
    }
}

/**
 * 대여 승인
 */
function approveRental($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $db->beginTransaction();

        $stmt = $db->prepare("SELECT owner_id, renter_id, status FROM rentals WHERE rental_id = ?");
        $stmt->execute([$rentalId]);
        $rental = $stmt->fetch();

        if (!$rental || $rental['owner_id'] != $userId) {
            throw new Exception('Unauthorized');
        }

        if ($rental['status'] !== 'pending') {
            throw new Exception('Rental cannot be approved');
        }

        $stmt = $db->prepare("UPDATE rentals SET status = 'approved', updated_at = NOW() WHERE rental_id = ?");
        $stmt->execute([$rentalId]);

        // 알림 생성
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '대여 승인', '대여 신청이 승인되었습니다.', 'rental', ?)
        ");
        $stmt->execute([$rental['renter_id'], $rentalId]);

        $db->commit();

        logActivity($userId, 'Rental approved', ['rental_id' => $rentalId]);

        sendSuccess([], 'Rental approved successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Approve rental error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 대여 거절
 */
function rejectRental($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        $reason = sanitizeInput($input['reason'] ?? '');

        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $db->beginTransaction();

        $stmt = $db->prepare("SELECT owner_id, renter_id, equipment_id, status FROM rentals WHERE rental_id = ?");
        $stmt->execute([$rentalId]);
        $rental = $stmt->fetch();

        if (!$rental || $rental['owner_id'] != $userId) {
            throw new Exception('Unauthorized');
        }

        if ($rental['status'] !== 'pending') {
            throw new Exception('Rental cannot be rejected');
        }

        $stmt = $db->prepare("UPDATE rentals SET status = 'rejected', updated_at = NOW() WHERE rental_id = ?");
        $stmt->execute([$rentalId]);

        // 장비 상태 복구
        $stmt = $db->prepare("UPDATE equipment SET status = 'available' WHERE equipment_id = ?");
        $stmt->execute([$rental['equipment_id']]);

        // 알림 생성
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type, related_id)
            VALUES (?, '대여 거절', ?, 'rental', ?)
        ");
        $stmt->execute([$rental['renter_id'], $reason ?: '대여 신청이 거절되었습니다.', $rentalId]);

        $db->commit();

        logActivity($userId, 'Rental rejected', ['rental_id' => $rentalId]);

        sendSuccess([], 'Rental rejected');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Reject rental error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 대여 취소
 */
function cancelRental($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $db->beginTransaction();

        $stmt = $db->prepare("SELECT renter_id, equipment_id, status FROM rentals WHERE rental_id = ?");
        $stmt->execute([$rentalId]);
        $rental = $stmt->fetch();

        if (!$rental || $rental['renter_id'] != $userId) {
            throw new Exception('Unauthorized');
        }

        if (!in_array($rental['status'], ['pending', 'approved'])) {
            throw new Exception('Rental cannot be cancelled');
        }

        $stmt = $db->prepare("UPDATE rentals SET status = 'cancelled', updated_at = NOW() WHERE rental_id = ?");
        $stmt->execute([$rentalId]);

        // 장비 상태 복구
        $stmt = $db->prepare("UPDATE equipment SET status = 'available' WHERE equipment_id = ?");
        $stmt->execute([$rental['equipment_id']]);

        $db->commit();

        logActivity($userId, 'Rental cancelled', ['rental_id' => $rentalId]);

        sendSuccess([], 'Rental cancelled successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Cancel rental error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 픽업 확인
 */
function confirmPickup($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $stmt = $db->prepare("
            UPDATE rentals
            SET status = 'active', pickup_confirmed_at = NOW(), updated_at = NOW()
            WHERE rental_id = ? AND (renter_id = ? OR owner_id = ?) AND status = 'approved'
        ");
        $stmt->execute([$rentalId, $userId, $userId]);

        if ($stmt->rowCount() === 0) {
            sendError('Unable to confirm pickup', 400);
        }

        // 장비 상태 업데이트
        $stmt = $db->prepare("
            UPDATE equipment e
            INNER JOIN rentals r ON e.equipment_id = r.equipment_id
            SET e.status = 'in_use'
            WHERE r.rental_id = ?
        ");
        $stmt->execute([$rentalId]);

        logActivity($userId, 'Pickup confirmed', ['rental_id' => $rentalId]);

        sendSuccess([], 'Pickup confirmed successfully');

    } catch (Exception $e) {
        logError('Confirm pickup error: ' . $e->getMessage());
        sendError('Failed to confirm pickup', 500);
    }
}

/**
 * 반납 확인
 */
function confirmReturn($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        if (!$rentalId) {
            sendError('Rental ID required', 400);
        }

        $db->beginTransaction();

        $stmt = $db->prepare("
            SELECT equipment_id, owner_id
            FROM rentals
            WHERE rental_id = ? AND owner_id = ? AND status = 'active'
        ");
        $stmt->execute([$rentalId, $userId]);
        $rental = $stmt->fetch();

        if (!$rental) {
            throw new Exception('Unauthorized or invalid rental');
        }

        $stmt = $db->prepare("
            UPDATE rentals
            SET status = 'completed', return_confirmed_at = NOW(), updated_at = NOW()
            WHERE rental_id = ?
        ");
        $stmt->execute([$rentalId]);

        // 장비 상태 업데이트
        $stmt = $db->prepare("
            UPDATE equipment
            SET status = 'available', total_rental_count = total_rental_count + 1
            WHERE equipment_id = ?
        ");
        $stmt->execute([$rental['equipment_id']]);

        // 이력 기록
        $stmt = $db->prepare("
            INSERT INTO equipment_history (equipment_id, event_type, event_description, rental_id)
            VALUES (?, 'returned', 'Equipment returned from rental', ?)
        ");
        $stmt->execute([$rental['equipment_id'], $rentalId]);

        $db->commit();

        logActivity($userId, 'Return confirmed', ['rental_id' => $rentalId]);

        sendSuccess([], 'Return confirmed successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Confirm return error: ' . $e->getMessage());
        sendError($e->getMessage(), 500);
    }
}

/**
 * 검수 리포트 작성
 */
function createInspection($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        $inspectionType = $input['inspection_type'] ?? null;
        $conditionGrade = $input['condition_grade'] ?? null;
        $notes = sanitizeInput($input['notes'] ?? '');
        $damageDescription = sanitizeInput($input['damage_description'] ?? '');
        $estimatedRepairCost = $input['estimated_repair_cost'] ?? null;

        if (!$rentalId || !$inspectionType || !$conditionGrade) {
            sendError('Missing required fields', 400);
        }

        $validTypes = ['pre_rental', 'post_rental'];
        if (!in_array($inspectionType, $validTypes)) {
            sendError('Invalid inspection type', 400);
        }

        $stmt = $db->prepare("
            INSERT INTO inspection_reports (
                rental_id, inspector_id, inspection_type, condition_grade,
                notes, damage_description, estimated_repair_cost
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $rentalId,
            $userId,
            $inspectionType,
            $conditionGrade,
            $notes,
            $damageDescription,
            $estimatedRepairCost
        ]);

        $reportId = $db->lastInsertId();

        logActivity($userId, 'Inspection report created', ['report_id' => $reportId]);

        sendSuccess([
            'report_id' => $reportId
        ], 'Inspection report created successfully');

    } catch (Exception $e) {
        logError('Create inspection error: ' . $e->getMessage());
        sendError('Failed to create inspection', 500);
    }
}

/**
 * 검수 이미지 업로드
 */
function uploadInspectionImage($db) {
    $userId = requireAuth();

    try {
        $reportId = $_POST['report_id'] ?? null;
        $description = sanitizeInput($_POST['description'] ?? '');

        if (!$reportId) {
            sendError('Report ID required', 400);
        }

        if (!isset($_FILES['image'])) {
            sendError('Image file required', 400);
        }

        $filePath = uploadFile($_FILES['image'], 'inspection');

        $stmt = $db->prepare("
            INSERT INTO inspection_images (report_id, image_path, image_description)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$reportId, $filePath, $description]);

        sendSuccess([
            'image_id' => $db->lastInsertId(),
            'image_path' => $filePath
        ], 'Image uploaded successfully');

    } catch (Exception $e) {
        logError('Upload inspection image error: ' . $e->getMessage());
        sendError('Failed to upload image: ' . $e->getMessage(), 500);
    }
}

/**
 * 리뷰 작성
 */
function createReview($db, $input) {
    $userId = requireAuth();

    try {
        $rentalId = $input['rental_id'] ?? null;
        $equipmentId = $input['equipment_id'] ?? null;
        $rating = intval($input['rating'] ?? 0);
        $comment = sanitizeInput($input['comment'] ?? '');
        $reviewType = $input['review_type'] ?? 'equipment';

        if (!$rentalId || !$equipmentId || $rating < 1 || $rating > 5) {
            sendError('Invalid input', 400);
        }

        // 대여 확인
        $stmt = $db->prepare("
            SELECT renter_id, status
            FROM rentals
            WHERE rental_id = ? AND equipment_id = ?
        ");
        $stmt->execute([$rentalId, $equipmentId]);
        $rental = $stmt->fetch();

        if (!$rental || $rental['renter_id'] != $userId || $rental['status'] !== 'completed') {
            sendError('Cannot write review for this rental', 400);
        }

        $db->beginTransaction();

        $stmt = $db->prepare("
            INSERT INTO reviews (rental_id, equipment_id, reviewer_id, rating, comment, review_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$rentalId, $equipmentId, $userId, $rating, $comment, $reviewType]);

        // 평균 평점 업데이트
        $stmt = $db->prepare("
            UPDATE equipment
            SET average_rating = (
                SELECT AVG(rating)
                FROM reviews
                WHERE equipment_id = ? AND review_type = 'equipment'
            )
            WHERE equipment_id = ?
        ");
        $stmt->execute([$equipmentId, $equipmentId]);

        $db->commit();

        logActivity($userId, 'Review created', ['rental_id' => $rentalId]);

        sendSuccess([], 'Review submitted successfully');

    } catch (Exception $e) {
        $db->rollBack();
        logError('Create review error: ' . $e->getMessage());
        sendError('Failed to create review', 500);
    }
}
