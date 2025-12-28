<?php
require_once '../config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: index.php');
        exit;
    } else {
        $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 100px auto;">
            <h2 style="text-align: center;">🔐 관리자 로그인</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>아이디</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>비밀번호</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">로그인</button>
            </form>
            <p style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
                기본 계정 - ID: admin, PW: admin123
            </p>
        </div>
    </div>
</body>
</html>
