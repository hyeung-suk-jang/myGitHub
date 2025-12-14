<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/auth.php';

Session::start();
redirectIfLoggedIn();

$pageTitle = '로그인';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = getPost('email');
    $password = getPost('password');

    if (empty($email) || empty($password)) {
        $error = '이메일과 비밀번호를 입력해주세요.';
    } else {
        $result = loginUser($email, $password);

        if ($result['success']) {
            Session::setFlash('success', '로그인되었습니다.');

            // 사용자 타입에 따라 리다이렉트
            if ($result['user']['user_type'] === 'admin') {
                redirect('/solution-marketplace/admin/index.php');
            } elseif ($result['user']['user_type'] === 'seller') {
                redirect('/solution-marketplace/seller/index.php');
            } else {
                redirect('/solution-marketplace/public/index.php');
            }
        } else {
            $error = $result['message'];
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="login-container">
    <div class="login-box">
        <h2>로그인</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo escape($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="email">이메일</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($email) ? escape($email) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">로그인</button>
        </form>

        <div class="social-login">
            <p>SNS 계정으로 로그인</p>
            <div class="social-buttons">
                <a href="/solution-marketplace/api/auth/kakao.php" class="btn btn-kakao">
                    카카오 로그인
                </a>
                <a href="/solution-marketplace/api/auth/naver.php" class="btn btn-naver">
                    네이버 로그인
                </a>
                <a href="/solution-marketplace/api/auth/google.php" class="btn btn-google">
                    구글 로그인
                </a>
            </div>
        </div>

        <div class="login-footer">
            <p>계정이 없으신가요? <a href="register.php">회원가입</a></p>
            <p>셀러로 등록하시겠어요? <a href="/solution-marketplace/seller/register.php">셀러 등록</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
