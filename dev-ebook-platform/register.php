<?php
require_once 'php/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email'] ?? '');
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = clean_input($_POST['full_name'] ?? '');
    $github_username = clean_input($_POST['github_username'] ?? '');

    // 유효성 검사
    if (empty($email) || empty($username) || empty($password)) {
        $error = '필수 항목을 모두 입력해주세요.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '올바른 이메일 주소를 입력해주세요.';
    } elseif (strlen($password) < 6) {
        $error = '비밀번호는 최소 6자 이상이어야 합니다.';
    } elseif ($password !== $confirm_password) {
        $error = '비밀번호가 일치하지 않습니다.';
    } else {
        $db = getDB();

        // 이메일 중복 체크
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = '이미 사용 중인 이메일입니다.';
        } else {
            // 사용자명 중복 체크
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = '이미 사용 중인 사용자명입니다.';
            } else {
                // 회원가입 처리
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("INSERT INTO users (email, username, password, full_name, github_username) VALUES (?, ?, ?, ?, ?)");

                if ($stmt->execute([$email, $username, $hashed_password, $full_name, $github_username])) {
                    $success = '회원가입이 완료되었습니다! 로그인해주세요.';
                } else {
                    $error = '회원가입 중 오류가 발생했습니다.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - DevBooks</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: var(--primary-color);
        }
        .form-group small {
            color: var(--text-light);
            font-size: 0.85rem;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: var(--secondary-color);
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
        }
        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>👤 회원가입</h1>
                <p style="color: var(--text-secondary);">개발자를 위한 전자책 플랫폼에 가입하세요</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <br><a href="login.php" style="color: inherit; font-weight: 700;">로그인 페이지로 이동 →</a>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">이메일 *</label>
                    <input type="email" id="email" name="email" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="username">사용자명 *</label>
                    <input type="text" id="username" name="username" placeholder="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    <small>다른 사용자에게 표시되는 이름입니다</small>
                </div>

                <div class="form-group">
                    <label for="full_name">실명</label>
                    <input type="text" id="full_name" name="full_name" placeholder="홍길동" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="github_username">GitHub 사용자명</label>
                    <input type="text" id="github_username" name="github_username" placeholder="github-username" value="<?= htmlspecialchars($_POST['github_username'] ?? '') ?>">
                    <small>선택사항: GitHub 프로필을 연동하세요</small>
                </div>

                <div class="form-group">
                    <label for="password">비밀번호 *</label>
                    <input type="password" id="password" name="password" placeholder="최소 6자 이상" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">비밀번호 확인 *</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="비밀번호를 다시 입력하세요" required>
                </div>

                <button type="submit" class="btn-submit">회원가입</button>
            </form>

            <div class="auth-links">
                이미 계정이 있으신가요? <a href="login.php">로그인</a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
