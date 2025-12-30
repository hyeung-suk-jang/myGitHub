<?php
/**
 * 사용자 인증 API
 * 회원가입, 로그인, 로그아웃, 프로필 관리
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

// 요청 데이터 파싱
$input = json_decode(file_get_contents('php://input'), true);

// 라우팅
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($method === 'POST') {
            register($db, $input);
        }
        break;

    case 'login':
        if ($method === 'POST') {
            login($db, $input);
        }
        break;

    case 'logout':
        logout();
        break;

    case 'profile':
        if ($method === 'GET') {
            getProfile($db);
        } elseif ($method === 'PUT') {
            updateProfile($db, $input);
        }
        break;

    case 'check':
        checkAuth();
        break;

    case 'verify-identity':
        if ($method === 'POST') {
            verifyIdentity($db);
        }
        break;

    default:
        sendError('Invalid action', 400);
}

/**
 * 회원가입
 */
function register($db, $input) {
    try {
        // 입력 검증
        $required = ['email', 'password', 'username', 'phone', 'user_type'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                sendError("Missing required field: $field", 400);
            }
        }

        $email = sanitizeInput($input['email']);
        $password = $input['password'];
        $username = sanitizeInput($input['username']);
        $phone = sanitizeInput($input['phone']);
        $userType = sanitizeInput($input['user_type']);
        $address = sanitizeInput($input['address'] ?? '');

        // 이메일 형식 검증
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError('Invalid email format', 400);
        }

        // 비밀번호 강도 검증
        if (strlen($password) < 8) {
            sendError('Password must be at least 8 characters', 400);
        }

        // 사용자 타입 검증
        $validTypes = ['supplier', 'renter', 'both'];
        if (!in_array($userType, $validTypes)) {
            sendError('Invalid user type', 400);
        }

        // 이메일 중복 확인
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendError('Email already exists', 409);
        }

        // 비밀번호 해싱
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 사용자 생성
        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, username, phone, user_type, address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $email,
            $passwordHash,
            $username,
            $phone,
            $userType,
            $address
        ]);

        $userId = $db->lastInsertId();

        logActivity($userId, 'User registered', ['email' => $email]);

        sendSuccess([
            'user_id' => $userId,
            'email' => $email,
            'username' => $username
        ], 'Registration successful');

    } catch (Exception $e) {
        logError('Registration error: ' . $e->getMessage());
        sendError('Registration failed', 500);
    }
}

/**
 * 로그인
 */
function login($db, $input) {
    try {
        $email = sanitizeInput($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            sendError('Email and password required', 400);
        }

        // 사용자 조회
        $stmt = $db->prepare("
            SELECT user_id, email, password_hash, username, user_type,
                   identity_verified, status, profile_image
            FROM users
            WHERE email = ? AND status = 'active'
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            sendError('Invalid credentials', 401);
        }

        // 비밀번호 확인
        if (!password_verify($password, $user['password_hash'])) {
            sendError('Invalid credentials', 401);
        }

        // 세션 생성
        startSession();
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['identity_verified'] = $user['identity_verified'];

        // 마지막 로그인 시간 업데이트
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);

        logActivity($user['user_id'], 'User logged in');

        unset($user['password_hash']);
        sendSuccess($user, 'Login successful');

    } catch (Exception $e) {
        logError('Login error: ' . $e->getMessage());
        sendError('Login failed', 500);
    }
}

/**
 * 로그아웃
 */
function logout() {
    startSession();
    $userId = $_SESSION['user_id'] ?? null;

    session_unset();
    session_destroy();

    if ($userId) {
        logActivity($userId, 'User logged out');
    }

    sendSuccess([], 'Logout successful');
}

/**
 * 프로필 조회
 */
function getProfile($db) {
    $userId = requireAuth();

    try {
        $stmt = $db->prepare("
            SELECT user_id, email, username, phone, user_type,
                   identity_verified, profile_image, address, created_at
            FROM users
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            sendError('User not found', 404);
        }

        sendSuccess($user);

    } catch (Exception $e) {
        logError('Get profile error: ' . $e->getMessage());
        sendError('Failed to get profile', 500);
    }
}

/**
 * 프로필 업데이트
 */
function updateProfile($db, $input) {
    $userId = requireAuth();

    try {
        $allowedFields = ['username', 'phone', 'address'];
        $updates = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $values[] = sanitizeInput($input[$field]);
            }
        }

        if (empty($updates)) {
            sendError('No fields to update', 400);
        }

        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE user_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        logActivity($userId, 'Profile updated', $input);

        sendSuccess([], 'Profile updated successfully');

    } catch (Exception $e) {
        logError('Update profile error: ' . $e->getMessage());
        sendError('Failed to update profile', 500);
    }
}

/**
 * 인증 상태 확인
 */
function checkAuth() {
    $user = getCurrentUser();

    if ($user) {
        sendSuccess($user, 'Authenticated');
    } else {
        sendError('Not authenticated', 401);
    }
}

/**
 * 신원 인증 (KYC)
 */
function verifyIdentity($db) {
    $userId = requireAuth();

    try {
        if (!isset($_FILES['identity_document'])) {
            sendError('Identity document required', 400);
        }

        $filePath = uploadFile($_FILES['identity_document'], 'profile');

        $stmt = $db->prepare("
            UPDATE users
            SET identity_document = ?, identity_verified = TRUE, updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->execute([$filePath, $userId]);

        $_SESSION['identity_verified'] = true;

        logActivity($userId, 'Identity verified');

        sendSuccess(['identity_document' => $filePath], 'Identity verification submitted');

    } catch (Exception $e) {
        logError('Identity verification error: ' . $e->getMessage());
        sendError('Failed to verify identity: ' . $e->getMessage(), 500);
    }
}
