<?php
if (!function_exists('get_categories')) {
    require_once __DIR__ . '/../php/functions.php';
}

$categories = get_categories();
$cart_count = is_logged_in() ? get_cart_count($_SESSION['user_id']) : 0;
?>
<header class="header">
    <div class="top-bar">
        <div class="container">
            <div>📚 개발자를 위한 프리미엄 전자책 플랫폼</div>
            <div>
                <?php if (is_logged_in()): ?>
                    <a href="library.php">내 라이브러리</a>
                    <a href="profile.php">프로필</a>
                    <?php if (is_admin()): ?>
                        <a href="admin/">관리자</a>
                    <?php endif; ?>
                    <a href="php/logout.php">로그아웃</a>
                <?php else: ?>
                    <a href="login.php">로그인</a>
                    <a href="register.php">회원가입</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <span class="logo-icon">💻</span>
                    <span>DevBooks</span>
                </a>

                <div class="search-bar">
                    <input type="text" id="search-input" placeholder="책 제목, 저자, 기술 스택으로 검색...">
                    <button type="button">🔍</button>
                    <div id="search-results" style="display:none;"></div>
                </div>

                <div class="header-actions">
                    <a href="cart.php" class="cart-icon">
                        🛒
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?= $cart_count ?></span>
                        <?php else: ?>
                            <span class="cart-count" style="display:none;">0</span>
                        <?php endif; ?>
                    </a>
                    <?php if (is_logged_in()): ?>
                        <a href="library.php" class="btn btn-primary">내 서재</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">로그인</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <nav class="nav">
        <div class="container">
            <ul class="nav-list">
                <li><a href="index.php"><span class="nav-icon">🏠</span> 홈</a></li>
                <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                    <li>
                        <a href="books.php?category=<?= $category['slug'] ?>">
                            <span class="nav-icon"><?= $category['icon'] ?></span>
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li><a href="books.php"><span class="nav-icon">📚</span> 전체보기</a></li>
            </ul>
        </div>
    </nav>
</header>
