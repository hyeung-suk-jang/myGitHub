<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="container">
        <div class="auth-container">
            <h1>회원가입</h1>

            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="name">이름</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">이메일</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">비밀번호 확인</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-primary">가입하기</button>
            </form>

            <div class="auth-links">
                <a href="/login">이미 계정이 있으신가요? 로그인</a>
            </div>

            <div class="social-login">
                <h3>소셜 회원가입</h3>
                <a href="/auth/google" class="btn btn-google">Google로 가입</a>
                <a href="/auth/facebook" class="btn btn-facebook">Facebook으로 가입</a>
                <a href="/auth/kakao" class="btn btn-kakao">Kakao로 가입</a>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = '/login';
                } else {
                    alert(result.error || '회원가입에 실패했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('오류가 발생했습니다.');
            }
        });
    </script>
</body>
</html>
