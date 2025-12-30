<?php
/**
 * 장비 관리 API
 * 장비 등록, 수정, 삭제, 조회, 검색
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        getEquipmentList($db);
        break;

    case 'detail':
        getEquipmentDetail($db);
        break;

    case 'create':
        if ($method === 'POST') {
            createEquipment($db, $input);
        }
        break;

    case 'update':
        if ($method === 'PUT') {
            updateEquipment($db, $input);
        }
        break;

    case 'delete':
        if ($method === 'DELETE') {
            deleteEquipment($db);
        }
        break;

    case 'my-equipment':
        getMyEquipment($db);
        break;

    case 'upload-image':
        if ($method === 'POST') {
            uploadEquipmentImage($db);
        }
        break;

    case 'categories':
        getCategories($db);
        break;

    case 'update-status':
        if ($method === 'PUT') {
            updateEquipmentStatus($db, $input);
        }
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 장비 목록 조회 (검색, 필터링, 페이지네이션)
 */
function getEquipmentList($db) {
    try {
        // 쿼리 파라미터
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(50, max(1, intval($_GET['limit'] ?? ITEMS_PER_PAGE)));
        $offset = ($page - 1) * $limit;

        // 필터 파라미터
        $categoryId = $_GET['category_id'] ?? null;
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;
        $condition = $_GET['condition'] ?? null;
        $location = $_GET['location'] ?? null;
        $search = $_GET['search'] ?? null;
        $sortBy = $_GET['sort'] ?? 'created_at';
        $sortOrder = $_GET['order'] ?? 'DESC';

        // 허용된 정렬 필드
        $allowedSort = ['created_at', 'daily_rate', 'average_rating', 'total_rental_count'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }

        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        // 쿼리 구성
        $where = ["e.status = 'available'"];
        $params = [];

        if ($categoryId) {
            $where[] = "e.category_id = ?";
            $params[] = $categoryId;
        }

        if ($minPrice !== null) {
            $where[] = "e.daily_rate >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice !== null) {
            $where[] = "e.daily_rate <= ?";
            $params[] = $maxPrice;
        }

        if ($condition) {
            $where[] = "e.condition_grade = ?";
            $params[] = $condition;
        }

        if ($location) {
            $where[] = "e.location LIKE ?";
            $params[] = "%$location%";
        }

        if ($search) {
            $where[] = "(e.equipment_name LIKE ? OR e.model_name LIKE ? OR e.brand LIKE ? OR e.description LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = implode(' AND ', $where);

        // 전체 개수 조회
        $countSql = "SELECT COUNT(*) as total FROM equipment e WHERE $whereClause";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];

        // 장비 목록 조회
        $sql = "
            SELECT
                e.equipment_id, e.equipment_name, e.model_name, e.brand,
                e.daily_rate, e.deposit_amount, e.condition_grade,
                e.location, e.average_rating, e.total_rental_count,
                e.created_at,
                c.category_name,
                u.username as owner_name,
                (SELECT image_path FROM equipment_images WHERE equipment_id = e.equipment_id AND is_primary = 1 LIMIT 1) as primary_image
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.category_id
            LEFT JOIN users u ON e.owner_id = u.user_id
            WHERE $whereClause
            ORDER BY e.$sortBy $sortOrder
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $equipment = $stmt->fetchAll();

        sendSuccess([
            'equipment' => $equipment,
            'pagination' => [
                'total' => intval($total),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);

    } catch (Exception $e) {
        logError('Get equipment list error: ' . $e->getMessage());
        sendError('Failed to get equipment list', 500);
    }
}

/**
 * 장비 상세 조회
 */
function getEquipmentDetail($db) {
    try {
        $equipmentId = $_GET['id'] ?? null;
        if (!$equipmentId) {
            sendError('Equipment ID required', 400);
        }

        // 장비 정보
        $stmt = $db->prepare("
            SELECT
                e.*,
                c.category_name,
                u.username as owner_name,
                u.phone as owner_phone,
                u.email as owner_email
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.category_id
            LEFT JOIN users u ON e.owner_id = u.user_id
            WHERE e.equipment_id = ?
        ");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch();

        if (!$equipment) {
            sendError('Equipment not found', 404);
        }

        // 이미지 목록
        $stmt = $db->prepare("
            SELECT image_id, image_path, is_primary, display_order
            FROM equipment_images
            WHERE equipment_id = ?
            ORDER BY is_primary DESC, display_order ASC
        ");
        $stmt->execute([$equipmentId]);
        $equipment['images'] = $stmt->fetchAll();

        // 리뷰 목록
        $stmt = $db->prepare("
            SELECT
                r.review_id, r.rating, r.comment, r.created_at,
                u.username as reviewer_name
            FROM reviews r
            LEFT JOIN users u ON r.reviewer_id = u.user_id
            WHERE r.equipment_id = ? AND r.review_type = 'equipment'
            ORDER BY r.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$equipmentId]);
        $equipment['reviews'] = $stmt->fetchAll();

        // 장비 이력 기록
        $stmt = $db->prepare("
            INSERT INTO equipment_history (equipment_id, event_type, event_description)
            VALUES (?, 'viewed', 'Equipment detail viewed')
        ");
        $stmt->execute([$equipmentId]);

        sendSuccess($equipment);

    } catch (Exception $e) {
        logError('Get equipment detail error: ' . $e->getMessage());
        sendError('Failed to get equipment detail', 500);
    }
}

/**
 * 장비 등록
 */
function createEquipment($db, $input) {
    $userId = requireAuth();

    try {
        // 입력 검증
        $required = ['equipment_name', 'daily_rate', 'deposit_amount'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                sendError("Missing required field: $field", 400);
            }
        }

        $equipmentName = sanitizeInput($input['equipment_name']);
        $modelName = sanitizeInput($input['model_name'] ?? '');
        $serialNumber = sanitizeInput($input['serial_number'] ?? '');
        $brand = sanitizeInput($input['brand'] ?? '');
        $categoryId = $input['category_id'] ?? null;
        $purchasePrice = $input['purchase_price'] ?? null;
        $dailyRate = floatval($input['daily_rate']);
        $depositAmount = floatval($input['deposit_amount']);
        $conditionGrade = sanitizeInput($input['condition_grade'] ?? 'A');
        $description = sanitizeInput($input['description'] ?? '');
        $specifications = json_encode($input['specifications'] ?? [], JSON_UNESCAPED_UNICODE);
        $location = sanitizeInput($input['location'] ?? '');

        // 가격 검증
        if ($dailyRate <= 0 || $depositAmount <= 0) {
            sendError('Daily rate and deposit must be greater than 0', 400);
        }

        $stmt = $db->prepare("
            INSERT INTO equipment (
                owner_id, category_id, equipment_name, model_name, serial_number,
                brand, purchase_price, daily_rate, deposit_amount, condition_grade,
                description, specifications, location
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId, $categoryId, $equipmentName, $modelName, $serialNumber,
            $brand, $purchasePrice, $dailyRate, $depositAmount, $conditionGrade,
            $description, $specifications, $location
        ]);

        $equipmentId = $db->lastInsertId();

        // 이력 기록
        $stmt = $db->prepare("
            INSERT INTO equipment_history (equipment_id, event_type, event_description)
            VALUES (?, 'created', 'Equipment registered')
        ");
        $stmt->execute([$equipmentId]);

        logActivity($userId, 'Equipment created', ['equipment_id' => $equipmentId]);

        sendSuccess([
            'equipment_id' => $equipmentId
        ], 'Equipment created successfully');

    } catch (Exception $e) {
        logError('Create equipment error: ' . $e->getMessage());
        sendError('Failed to create equipment', 500);
    }
}

/**
 * 장비 수정
 */
function updateEquipment($db, $input) {
    $userId = requireAuth();

    try {
        $equipmentId = $input['equipment_id'] ?? null;
        if (!$equipmentId) {
            sendError('Equipment ID required', 400);
        }

        // 소유권 확인
        $stmt = $db->prepare("SELECT owner_id FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch();

        if (!$equipment) {
            sendError('Equipment not found', 404);
        }

        if ($equipment['owner_id'] != $userId) {
            sendError('Unauthorized', 403);
        }

        // 업데이트할 필드
        $allowedFields = [
            'equipment_name', 'model_name', 'daily_rate', 'deposit_amount',
            'condition_grade', 'description', 'location', 'category_id'
        ];

        $updates = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $values[] = $field === 'description' ? sanitizeInput($input[$field]) : $input[$field];
            }
        }

        if (empty($updates)) {
            sendError('No fields to update', 400);
        }

        $values[] = $equipmentId;
        $sql = "UPDATE equipment SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE equipment_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        logActivity($userId, 'Equipment updated', ['equipment_id' => $equipmentId]);

        sendSuccess([], 'Equipment updated successfully');

    } catch (Exception $e) {
        logError('Update equipment error: ' . $e->getMessage());
        sendError('Failed to update equipment', 500);
    }
}

/**
 * 장비 삭제
 */
function deleteEquipment($db) {
    $userId = requireAuth();

    try {
        $equipmentId = $_GET['id'] ?? null;
        if (!$equipmentId) {
            sendError('Equipment ID required', 400);
        }

        // 소유권 확인
        $stmt = $db->prepare("SELECT owner_id, status FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch();

        if (!$equipment) {
            sendError('Equipment not found', 404);
        }

        if ($equipment['owner_id'] != $userId) {
            sendError('Unauthorized', 403);
        }

        // 대여 중인 장비는 삭제 불가
        if (in_array($equipment['status'], ['reserved', 'in_use'])) {
            sendError('Cannot delete equipment that is currently rented', 400);
        }

        $stmt = $db->prepare("DELETE FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$equipmentId]);

        logActivity($userId, 'Equipment deleted', ['equipment_id' => $equipmentId]);

        sendSuccess([], 'Equipment deleted successfully');

    } catch (Exception $e) {
        logError('Delete equipment error: ' . $e->getMessage());
        sendError('Failed to delete equipment', 500);
    }
}

/**
 * 내 장비 목록 조회
 */
function getMyEquipment($db) {
    $userId = requireAuth();

    try {
        $stmt = $db->prepare("
            SELECT
                e.equipment_id, e.equipment_name, e.model_name, e.brand,
                e.daily_rate, e.deposit_amount, e.condition_grade,
                e.status, e.total_rental_count, e.average_rating,
                e.created_at,
                c.category_name,
                (SELECT image_path FROM equipment_images WHERE equipment_id = e.equipment_id AND is_primary = 1 LIMIT 1) as primary_image
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.category_id
            WHERE e.owner_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$userId]);
        $equipment = $stmt->fetchAll();

        sendSuccess(['equipment' => $equipment]);

    } catch (Exception $e) {
        logError('Get my equipment error: ' . $e->getMessage());
        sendError('Failed to get equipment list', 500);
    }
}

/**
 * 장비 이미지 업로드
 */
function uploadEquipmentImage($db) {
    $userId = requireAuth();

    try {
        $equipmentId = $_POST['equipment_id'] ?? null;
        if (!$equipmentId) {
            sendError('Equipment ID required', 400);
        }

        // 소유권 확인
        $stmt = $db->prepare("SELECT owner_id FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch();

        if (!$equipment || $equipment['owner_id'] != $userId) {
            sendError('Unauthorized', 403);
        }

        if (!isset($_FILES['image'])) {
            sendError('Image file required', 400);
        }

        $filePath = uploadFile($_FILES['image'], 'equipment');
        $isPrimary = isset($_POST['is_primary']) && $_POST['is_primary'] == '1';

        // 기존 주 이미지 해제
        if ($isPrimary) {
            $stmt = $db->prepare("UPDATE equipment_images SET is_primary = 0 WHERE equipment_id = ?");
            $stmt->execute([$equipmentId]);
        }

        $stmt = $db->prepare("
            INSERT INTO equipment_images (equipment_id, image_path, is_primary)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$equipmentId, $filePath, $isPrimary ? 1 : 0]);

        sendSuccess([
            'image_id' => $db->lastInsertId(),
            'image_path' => $filePath
        ], 'Image uploaded successfully');

    } catch (Exception $e) {
        logError('Upload image error: ' . $e->getMessage());
        sendError('Failed to upload image: ' . $e->getMessage(), 500);
    }
}

/**
 * 카테고리 목록 조회
 */
function getCategories($db) {
    try {
        $stmt = $db->prepare("
            SELECT category_id, category_name, description
            FROM equipment_categories
            ORDER BY category_name
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        sendSuccess(['categories' => $categories]);

    } catch (Exception $e) {
        logError('Get categories error: ' . $e->getMessage());
        sendError('Failed to get categories', 500);
    }
}

/**
 * 장비 상태 업데이트
 */
function updateEquipmentStatus($db, $input) {
    $userId = requireAuth();

    try {
        $equipmentId = $input['equipment_id'] ?? null;
        $newStatus = $input['status'] ?? null;

        if (!$equipmentId || !$newStatus) {
            sendError('Equipment ID and status required', 400);
        }

        $validStatuses = ['available', 'maintenance', 'unavailable'];
        if (!in_array($newStatus, $validStatuses)) {
            sendError('Invalid status', 400);
        }

        // 소유권 확인
        $stmt = $db->prepare("SELECT owner_id FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$equipmentId]);
        $equipment = $stmt->fetch();

        if (!$equipment || $equipment['owner_id'] != $userId) {
            sendError('Unauthorized', 403);
        }

        $stmt = $db->prepare("UPDATE equipment SET status = ?, updated_at = NOW() WHERE equipment_id = ?");
        $stmt->execute([$newStatus, $equipmentId]);

        // 이력 기록
        $stmt = $db->prepare("
            INSERT INTO equipment_history (equipment_id, event_type, event_description)
            VALUES (?, 'status_changed', ?)
        ");
        $stmt->execute([$equipmentId, "Status changed to $newStatus"]);

        logActivity($userId, 'Equipment status updated', [
            'equipment_id' => $equipmentId,
            'new_status' => $newStatus
        ]);

        sendSuccess([], 'Status updated successfully');

    } catch (Exception $e) {
        logError('Update status error: ' . $e->getMessage());
        sendError('Failed to update status', 500);
    }
}
