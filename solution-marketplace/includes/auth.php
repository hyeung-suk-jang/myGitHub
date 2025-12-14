<?php
/**
 * 인증 관련 함수
 */

/**
 * 로그인 필수 체크
 */
function requireLogin() {
    if (!Session::isLoggedIn()) {
        Session::setFlash('error', '로그인이 필요합니다.');
        redirect('/solution-marketplace/public/login.php');
    }
}

/**
 * 관리자 권한 체크
 */
function requireAdmin() {
    requireLogin();
    if (!Session::isAdmin()) {
        Session::setFlash('error', '관리자 권한이 필요합니다.');
        redirect('/solution-marketplace/public/index.php');
    }
}

/**
 * 셀러 권한 체크
 */
function requireSeller() {
    requireLogin();
    if (!Session::isSeller()) {
        Session::setFlash('error', '셀러 권한이 필요합니다.');
        redirect('/solution-marketplace/public/index.php');
    }
}

/**
 * 이미 로그인한 경우 리다이렉트
 */
function redirectIfLoggedIn($url = '/solution-marketplace/public/index.php') {
    if (Session::isLoggedIn()) {
        redirect($url);
    }
}

/**
 * 사용자 로그인 처리
 */
function loginUser($email, $password) {
    $db = Database::getInstance();

    $user = $db->fetchOne(
        "SELECT * FROM users WHERE email = ? AND status = 'active'",
        [$email]
    );

    if (!$user) {
        return ['success' => false, 'message' => '이메일 또는 비밀번호가 일치하지 않습니다.'];
    }

    // SNS 로그인 사용자는 비밀번호가 없을 수 있음
    if ($user['password'] === null) {
        return ['success' => false, 'message' => 'SNS 로그인을 이용해주세요.'];
    }

    if (!verifyPassword($password, $user['password'])) {
        return ['success' => false, 'message' => '이메일 또는 비밀번호가 일치하지 않습니다.'];
    }

    Session::setUser($user);

    return ['success' => true, 'user' => $user];
}

/**
 * 사용자 회원가입 처리
 */
function registerUser($data) {
    $db = Database::getInstance();

    // 이메일 중복 체크
    $existingUser = $db->fetchOne(
        "SELECT id FROM users WHERE email = ?",
        [$data['email']]
    );

    if ($existingUser) {
        return ['success' => false, 'message' => '이미 사용중인 이메일입니다.'];
    }

    // 사용자 등록
    $userId = $db->insert(
        "INSERT INTO users (email, password, name, phone, user_type, status) VALUES (?, ?, ?, ?, ?, ?)",
        [
            $data['email'],
            hashPassword($data['password']),
            $data['name'],
            $data['phone'] ?? null,
            $data['user_type'] ?? 'user',
            'active'
        ]
    );

    if (!$userId) {
        return ['success' => false, 'message' => '회원가입에 실패했습니다.'];
    }

    return ['success' => true, 'user_id' => $userId];
}

/**
 * SNS 로그인/회원가입 처리
 */
function socialLogin($provider, $providerId, $email, $name) {
    $db = Database::getInstance();

    // 기존 SNS 계정 확인
    $social = $db->fetchOne(
        "SELECT sa.*, u.* FROM social_accounts sa
         JOIN users u ON sa.user_id = u.id
         WHERE sa.provider = ? AND sa.provider_id = ?",
        [$provider, $providerId]
    );

    if ($social) {
        // 기존 사용자 로그인
        Session::setUser($social);
        return ['success' => true, 'user' => $social];
    }

    // 이메일로 기존 사용자 확인
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE email = ?",
        [$email]
    );

    $db->beginTransaction();

    try {
        if (!$user) {
            // 신규 사용자 등록
            $userId = $db->insert(
                "INSERT INTO users (email, name, user_type, status) VALUES (?, ?, 'user', 'active')",
                [$email, $name]
            );
        } else {
            $userId = $user['id'];
        }

        // SNS 계정 연동
        $db->insert(
            "INSERT INTO social_accounts (user_id, provider, provider_id) VALUES (?, ?, ?)",
            [$userId, $provider, $providerId]
        );

        $db->commit();

        // 사용자 정보 다시 조회
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        Session::setUser($user);

        return ['success' => true, 'user' => $user];
    } catch (Exception $e) {
        $db->rollback();
        return ['success' => false, 'message' => 'SNS 로그인에 실패했습니다.'];
    }
}

/**
 * 로그아웃 처리
 */
function logoutUser() {
    Session::destroy();
}

/**
 * 셀러 등록 신청
 */
function registerSeller($userId, $data) {
    $db = Database::getInstance();

    // 이미 셀러인지 확인
    $existingSeller = $db->fetchOne(
        "SELECT id FROM sellers WHERE user_id = ?",
        [$userId]
    );

    if ($existingSeller) {
        return ['success' => false, 'message' => '이미 셀러로 등록되어 있습니다.'];
    }

    $db->beginTransaction();

    try {
        // 셀러 정보 등록
        $sellerId = $db->insert(
            "INSERT INTO sellers (user_id, business_name, business_number, bank_name, account_number, account_holder, seller_status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')",
            [
                $userId,
                $data['business_name'],
                $data['business_number'] ?? null,
                $data['bank_name'],
                $data['account_number'],
                $data['account_holder']
            ]
        );

        $db->commit();

        return ['success' => true, 'seller_id' => $sellerId];
    } catch (Exception $e) {
        $db->rollback();
        return ['success' => false, 'message' => '셀러 등록 신청에 실패했습니다.'];
    }
}

/**
 * 셀러 정보 조회
 */
function getSellerInfo($userId) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM sellers WHERE user_id = ?",
        [$userId]
    );
}

/**
 * 셀러 승인 여부 확인
 */
function isApprovedSeller($userId) {
    $seller = getSellerInfo($userId);
    return $seller && $seller['seller_status'] === 'approved';
}
