<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Base Framework</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/layouts/header.php'; ?>

    <main class="container">
        <section class="hero">
            <h1>PHP Base Framework</h1>
            <p>모든 프로젝트에 사용할 수 있는 강력하고 안전한 PHP 프레임워크</p>
        </section>

        <section class="features">
            <h2>주요 기능</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>사용자 인증</h3>
                    <p>로그인, 회원가입, SNS 로그인, 이메일 인증</p>
                </div>
                <div class="feature-card">
                    <h3>보안</h3>
                    <p>CSRF, XSS, SQL Injection 방지</p>
                </div>
                <div class="feature-card">
                    <h3>ORM</h3>
                    <p>간편한 데이터베이스 연동</p>
                </div>
                <div class="feature-card">
                    <h3>결제 시스템</h3>
                    <p>Toss, Iamport, Stripe 지원</p>
                </div>
                <div class="feature-card">
                    <h3>쇼핑몰</h3>
                    <p>상품, 장바구니, 주문 관리</p>
                </div>
                <div class="feature-card">
                    <h3>커뮤니티</h3>
                    <p>게시판, 댓글, 카테고리</p>
                </div>
            </div>
        </section>

        <section class="cta">
            <h2>시작하기</h2>
            <p>PHP Base Framework로 프로젝트를 빠르게 시작하세요</p>
            <div class="cta-buttons">
                <a href="/register" class="btn btn-primary">회원가입</a>
                <a href="/shop" class="btn btn-secondary">쇼핑몰 보기</a>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/layouts/footer.php'; ?>
</body>
</html>
