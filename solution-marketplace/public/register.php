<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
redirectIfLoggedIn();

$pageTitle = '회원가입';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = getPost('email');
    $password = getPost('password');
    $passwordConfirm = getPost('password_confirm');
    $name = getPost('name');
    $phone = getPost('phone');

    $errors = [];

    // 유효성 검증
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = '올바른 이메일 주소를 입력해주세요.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = '비밀번호는 6자 이상이어야 합니다.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = '비밀번호가 일치하지 않습니다.';
    }

    if (empty($name)) {
        $errors[] = '이름을 입력해주세요.';
    }

    if (empty($errors)) {
        $result = registerUser([
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'phone' => $phone,
            'user_type' => 'user'
        ]);

        if ($result['success']) {
            Session::setFlash('success', '회원가입이 완료되었습니다. 로그인해주세요.');
            redirect('/solution-marketplace/public/login.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="register-container">
    <div class="register-box">
        <h2>회원가입</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="register-form">
            <div class="form-group">
                <label for="email">이메일 *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($email) ? escape($email) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">비밀번호 *</label>
                <input type="password" id="password" name="password" required
                       placeholder="6자 이상 입력해주세요">
            </div>

            <div class="form-group">
                <label for="password_confirm">비밀번호 확인 *</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>

            <div class="form-group">
                <label for="name">이름 *</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo isset($name) ? escape($name) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="phone">전화번호</label>
                <input type="tel" id="phone" name="phone"
                       placeholder="010-1234-5678"
                       value="<?php echo isset($phone) ? escape($phone) : ''; ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-block">회원가입</button>
        </form>

        <div class="register-footer">
            <p>이미 계정이 있으신가요? <a href="login.php">로그인</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
