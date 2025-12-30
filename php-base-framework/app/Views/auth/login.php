<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="container">
        <div class="auth-container">
            <h1>로그인</h1>

            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="email">이메일</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">로그인</button>
            </form>

            <div class="auth-links">
                <a href="/register">회원가입</a>
                <a href="/forgot-password">비밀번호 찾기</a>
            </div>

            <div class="social-login">
                <h3>소셜 로그인</h3>
                <a href="/auth/google" class="btn btn-google">Google로 로그인</a>
                <a href="/auth/facebook" class="btn btn-facebook">Facebook으로 로그인</a>
                <a href="/auth/kakao" class="btn btn-kakao">Kakao로 로그인</a>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = '/';
                } else {
                    alert(result.error || '로그인에 실패했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('오류가 발생했습니다.');
            }
        });
    </script>
</body>
</html>
