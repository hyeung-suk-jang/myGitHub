<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>솔루션 마켓</title>
    <link rel="stylesheet" href="/solution-marketplace/public/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                    <a href="/solution-marketplace/public/index.php">솔루션 마켓</a>
                </h1>
                <nav class="main-nav">
                    <ul>
                        <li><a href="/solution-marketplace/public/index.php">홈</a></li>
                        <li><a href="/solution-marketplace/public/products/list.php">상품 목록</a></li>
                        <?php if (Session::isLoggedIn()): ?>
                            <?php if (Session::isSeller()): ?>
                                <li><a href="/solution-marketplace/seller/index.php">셀러 대시보드</a></li>
                            <?php endif; ?>
                            <?php if (Session::isAdmin()): ?>
                                <li><a href="/solution-marketplace/admin/index.php">관리자</a></li>
                            <?php endif; ?>
                            <li><a href="/solution-marketplace/public/cart.php">장바구니</a></li>
                            <li><a href="/solution-marketplace/public/mypage.php">마이페이지</a></li>
                            <li><a href="/solution-marketplace/public/logout.php">로그아웃</a></li>
                            <li class="user-name"><?php echo escape(Session::get('user_name')); ?>님</li>
                        <?php else: ?>
                            <li><a href="/solution-marketplace/public/login.php">로그인</a></li>
                            <li><a href="/solution-marketplace/public/register.php">회원가입</a></li>
                            <li><a href="/solution-marketplace/seller/register.php">셀러 등록</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <?php if ($flashSuccess = Session::getFlash('success')): ?>
        <div class="alert alert-success"><?php echo escape($flashSuccess); ?></div>
    <?php endif; ?>

    <?php if ($flashError = Session::getFlash('error')): ?>
        <div class="alert alert-error"><?php echo escape($flashError); ?></div>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
