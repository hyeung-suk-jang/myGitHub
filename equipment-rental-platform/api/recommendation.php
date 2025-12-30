<?php
/**
 * AI 기반 장비 추천 시스템
 * 협업 필터링(Collaborative Filtering) 알고리즘 사용
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-recommendations':
        getRecommendations($db);
        break;

    case 'similar-equipment':
        getSimilarEquipment($db);
        break;

    case 'trending':
        getTrendingEquipment($db);
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 사용자 기반 추천
 * 유사한 대여 패턴을 가진 사용자가 빌린 장비 추천
 */
function getRecommendations($db) {
    $userId = requireAuth();

    try {
        $limit = min(20, max(5, intval($_GET['limit'] ?? 10)));

        // 1. 사용자의 대여 이력 가져오기
        $stmt = $db->prepare("
            SELECT DISTINCT equipment_id
            FROM rentals
            WHERE renter_id = ? AND status IN ('completed', 'active')
        ");
        $stmt->execute([$userId]);
        $userRentals = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($userRentals)) {
            // 대여 이력이 없으면 인기 장비 추천
            return getTrendingEquipment($db);
        }

        // 2. 같은 장비를 빌린 다른 사용자 찾기
        $placeholders = implode(',', array_fill(0, count($userRentals), '?'));
        $stmt = $db->prepare("
            SELECT DISTINCT renter_id
            FROM rentals
            WHERE equipment_id IN ($placeholders)
            AND renter_id != ?
            AND status IN ('completed', 'active')
        ");
        $stmt->execute(array_merge($userRentals, [$userId]));
        $similarUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($similarUsers)) {
            return getTrendingEquipment($db);
        }

        // 3. 유사 사용자가 빌린 장비 중 현재 사용자가 빌리지 않은 것 추천
        $userPlaceholders = implode(',', array_fill(0, count($similarUsers), '?'));
        $rentalPlaceholders = implode(',', array_fill(0, count($userRentals), '?'));

        $stmt = $db->prepare("
            SELECT
                e.equipment_id,
                e.equipment_name,
                e.brand,
                e.model_name,
                e.daily_rate,
                e.deposit_amount,
                e.condition_grade,
                e.location,
                e.average_rating,
                e.total_rental_count,
                c.category_name,
                u.username as owner_name,
                (SELECT image_path FROM equipment_images
                 WHERE equipment_id = e.equipment_id AND is_primary = 1 LIMIT 1) as primary_image,
                COUNT(DISTINCT r.renter_id) as recommendation_score
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.category_id
            LEFT JOIN users u ON e.owner_id = u.user_id
            INNER JOIN rentals r ON e.equipment_id = r.equipment_id
            WHERE r.renter_id IN ($userPlaceholders)
            AND e.equipment_id NOT IN ($rentalPlaceholders)
            AND e.status = 'available'
            GROUP BY e.equipment_id
            ORDER BY recommendation_score DESC, e.average_rating DESC
            LIMIT ?
        ");

        $params = array_merge($similarUsers, $userRentals, [$limit]);
        $stmt->execute($params);
        $recommendations = $stmt->fetchAll();

        sendSuccess([
            'recommendations' => $recommendations,
            'algorithm' => 'collaborative_filtering'
        ]);

    } catch (Exception $e) {
        logError('Get recommendations error: ' . $e->getMessage());
        sendError('Failed to get recommendations', 500);
    }
}

/**
 * 유사 장비 추천
 * 같은 카테고리, 유사한 가격대의 장비 추천
 */
function getSimilarEquipment($db) {
    try {
        $equipmentId = $_GET['equipment_id'] ?? null;
        $limit = min(10, max(3, intval($_GET['limit'] ?? 5)));

        if (!$equipmentId) {
            sendError('Equipment ID required', 400);
        }

        // 기준 장비 정보
        $stmt = $db->prepare("
            SELECT category_id, daily_rate
            FROM equipment
            WHERE equipment_id = ?
        ");
        $stmt->execute([$equipmentId]);
        $baseEquipment = $stmt->fetch();

        if (!$baseEquipment) {
            sendError('Equipment not found', 404);
        }

        // 유사 장비 찾기
        $priceMin = $baseEquipment['daily_rate'] * 0.7;
        $priceMax = $baseEquipment['daily_rate'] * 1.3;

        $stmt = $db->prepare("
            SELECT
                e.equipment_id,
                e.equipment_name,
                e.brand,
                e.model_name,
                e.daily_rate,
                e.deposit_amount,
                e.condition_grade,
                e.location,
                e.average_rating,
                e.total_rental_count,
                c.category_name,
                (SELECT image_path FROM equipment_images
                 WHERE equipment_id = e.equipment_id AND is_primary = 1 LIMIT 1) as primary_image,
                ABS(e.daily_rate - ?) as price_difference
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.category_id
            WHERE e.category_id = ?
            AND e.equipment_id != ?
            AND e.daily_rate BETWEEN ? AND ?
            AND e.status = 'available'
            ORDER BY price_difference ASC, e.average_rating DESC
            LIMIT ?
        ");

        $stmt->execute([
            $baseEquipment['daily_rate'],
            $baseEquipment['category_id'],
            $equipmentId,
            $priceMin,
            $priceMax,
            $limit
        ]);
        $similar = $stmt->fetchAll();

        sendSuccess([
            'similar_equipment' => $similar,
            'algorithm' => 'content_based'
        ]);

    } catch (Exception $e) {
        logError('Get similar equipment error: ' . $e->getMessage());
        sendError('Failed to get similar equipment', 500);
    }
}

/**
 * 인기 장비 (트렌딩)
 * 최근 대여 횟수와 평점을 고려한 인기 장비
 */
function getTrendingEquipment($db) {
    try {
        $limit = min(20, max(5, intval($_GET['limit'] ?? 10)));
        $days = intval($_GET['days'] ?? 30); // 최근 N일

        $stmt = $db->prepare("
            SELECT
                e.equipment_id,
                e.equipment_name,
                e.brand,
                e.model_name,
                e.daily_rate,
                e.deposit_amount,
                e.condition_grade,
                e.location,
                e.average_rating,
                e.total_rental_count,
                c.category_name,
                u.username as owner_name,
                (SELECT image_path FROM equipment_images
                 WHERE equipment_id = e.equipment_id AND is_primary = 1 LIMIT 1) as primary_image,
                COUNT(DISTINCT r.rental_id) as recent_rentals,
                (COUNT(DISTINCT r.rental_id) * 0.7 + COALESCE(e.average_rating, 0) * 0.3) as trending_score
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.category_id
            LEFT JOIN users u ON e.owner_id = u.user_id
            LEFT JOIN rentals r ON e.equipment_id = r.equipment_id
                AND r.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE e.status = 'available'
            GROUP BY e.equipment_id
            ORDER BY trending_score DESC, e.created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$days, $limit]);
        $trending = $stmt->fetchAll();

        sendSuccess([
            'trending' => $trending,
            'period_days' => $days,
            'algorithm': 'trending'
        ]);

    } catch (Exception $e) {
        logError('Get trending equipment error: ' . $e->getMessage());
        sendError('Failed to get trending equipment', 500);
    }
}
