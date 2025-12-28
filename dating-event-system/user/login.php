<?php
require_once '../config/database.php';
require_once '../config/functions.php';

$error = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    $password = $_POST['password'];

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_phone'] = $user['phone'];
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = '전화번호 또는 비밀번호가 올바르지 않습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - 미팅 행사</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>💑 로그인</h1>
    </header>

    <div class="container">
        <div class="card" style="max-width: 500px; margin: 50px auto;">
            <h2>로그인</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= escape($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="phone">전화번호</label>
                    <input type="tel" id="phone" name="phone" placeholder="01012345678" required>
                </div>

                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">로그인</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <p>계정이 없으신가요? <a href="register.php">회원가입</a></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
