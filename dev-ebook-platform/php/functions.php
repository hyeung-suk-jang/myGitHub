<?php
require_once 'config.php';
require_once 'database.php';

// XSS 방지
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// 로그인 체크
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// 관리자 체크
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// 현재 사용자 정보 가져오기
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// 카테고리 목록 가져오기
function get_categories() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

// 기술 레벨 가져오기
function get_skill_levels() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM skill_levels ORDER BY id");
    return $stmt->fetchAll();
}

// 전자책 목록 가져오기 (필터링 포함)
function get_ebooks($filters = []) {
    $db = getDB();

    $sql = "SELECT e.*, c.name as category_name, c.icon as category_icon,
            s.name as skill_level_name, u.username as author_name,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT r.id) as review_count
            FROM ebooks e
            LEFT JOIN categories c ON e.category_id = c.id
            LEFT JOIN skill_levels s ON e.skill_level_id = s.id
            LEFT JOIN users u ON e.author_id = u.id
            LEFT JOIN reviews r ON e.id = r.ebook_id
            WHERE e.is_active = 1";

    $params = [];

    if (!empty($filters['category'])) {
        $sql .= " AND c.slug = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['skill_level'])) {
        $sql .= " AND s.slug = ?";
        $params[] = $filters['skill_level'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['tag'])) {
        $sql .= " AND e.id IN (SELECT ebook_id FROM ebook_tags et
                  JOIN tags t ON et.tag_id = t.id WHERE t.slug = ?)";
        $params[] = $filters['tag'];
    }

    $sql .= " GROUP BY e.id";

    // 정렬
    $order = $filters['order'] ?? 'latest';
    switch ($order) {
        case 'popular':
            $sql .= " ORDER BY e.views DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY avg_rating DESC";
            break;
        case 'price_low':
            $sql .= " ORDER BY e.price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY e.price DESC";
            break;
        default:
            $sql .= " ORDER BY e.created_at DESC";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// 전자책 상세 정보
function get_ebook_by_id($id) {
    $db = getDB();

    $sql = "SELECT e.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon,
            s.name as skill_level_name, s.slug as skill_level_slug,
            u.username as author_name, u.github_username, u.profile_image as author_image,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT r.id) as review_count
            FROM ebooks e
            LEFT JOIN categories c ON e.category_id = c.id
            LEFT JOIN skill_levels s ON e.skill_level_id = s.id
            LEFT JOIN users u ON e.author_id = u.id
            LEFT JOIN reviews r ON e.id = r.ebook_id
            WHERE e.id = ?
            GROUP BY e.id";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// 전자책 조회수 증가
function increment_views($ebook_id) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE ebooks SET views = views + 1 WHERE id = ?");
    $stmt->execute([$ebook_id]);
}

// 전자책 태그 가져오기
function get_ebook_tags($ebook_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT t.* FROM tags t
                         JOIN ebook_tags et ON t.id = et.tag_id
                         WHERE et.ebook_id = ?");
    $stmt->execute([$ebook_id]);
    return $stmt->fetchAll();
}

// 인기 태그 가져오기
function get_popular_tags($limit = 20) {
    $db = getDB();
    $stmt = $db->prepare("SELECT t.*, COUNT(et.ebook_id) as ebook_count
                         FROM tags t
                         LEFT JOIN ebook_tags et ON t.id = et.tag_id
                         GROUP BY t.id
                         ORDER BY ebook_count DESC
                         LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// 장바구니에 추가
function add_to_cart($user_id, $ebook_id) {
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO cart (user_id, ebook_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $ebook_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// 장바구니 개수
function get_cart_count($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// 장바구니 아이템 가져오기
function get_cart_items($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT e.*, c.id as cart_id
                         FROM cart c
                         JOIN ebooks e ON c.ebook_id = e.id
                         WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// 가격 포맷팅
function format_price($price) {
    return number_format($price, 0) . '원';
}

// 날짜 포맷팅
function format_date($date) {
    return date('Y.m.d', strtotime($date));
}

// 사용자가 이미 구매했는지 확인
function has_purchased($user_id, $ebook_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_library WHERE user_id = ? AND ebook_id = ?");
    $stmt->execute([$user_id, $ebook_id]);
    return $stmt->fetchColumn() > 0;
}

// 리뷰 가져오기
function get_reviews($ebook_id, $limit = null) {
    $db = getDB();
    $sql = "SELECT r.*, u.username, u.profile_image
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.ebook_id = ?
            ORDER BY r.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT ?";
    }

    $stmt = $db->prepare($sql);
    if ($limit) {
        $stmt->execute([$ebook_id, $limit]);
    } else {
        $stmt->execute([$ebook_id]);
    }
    return $stmt->fetchAll();
}

// 추천 전자책 (같은 카테고리)
function get_recommended_ebooks($category_id, $exclude_id, $limit = 4) {
    $db = getDB();
    $stmt = $db->prepare("SELECT e.*, c.name as category_name,
                         COALESCE(AVG(r.rating), 0) as avg_rating
                         FROM ebooks e
                         LEFT JOIN categories c ON e.category_id = c.id
                         LEFT JOIN reviews r ON e.id = r.ebook_id
                         WHERE e.category_id = ? AND e.id != ? AND e.is_active = 1
                         GROUP BY e.id
                         ORDER BY e.views DESC
                         LIMIT ?");
    $stmt->execute([$category_id, $exclude_id, $limit]);
    return $stmt->fetchAll();
}
?>
