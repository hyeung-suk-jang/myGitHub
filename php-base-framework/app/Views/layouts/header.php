<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="/">WenIT</a>
            </div>
            <ul class="navbar-menu">
                <li><a href="/">홈</a></li>
                <li><a href="#services">서비스</a></li>
                <li><a href="/shop">솔루션</a></li>
                <li><a href="/community">커뮤니티</a></li>
                <li><a href="/board/notice">공지사항</a></li>
                <li><a href="#contact">문의하기</a></li>
            </ul>
            <div class="navbar-user">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="color: #64748b; margin-right: 0.5rem;">환영합니다, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>님</span>
                    <a href="/logout" class="btn-logout">로그아웃</a>
                <?php else: ?>
                    <a href="/login" class="btn-login">로그인</a>
                    <a href="/register" class="btn-register">회원가입</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
