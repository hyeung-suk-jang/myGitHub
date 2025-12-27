<?php
require_once 'php/functions.php';

$categories = get_categories();
$popular_tags = get_popular_tags(15);
$latest_ebooks = get_ebooks(['order' => 'latest']);
$popular_ebooks = get_ebooks(['order' => 'popular']);

// 통계 데이터
$db = getDB();
$stats = [
    'total_books' => $db->query("SELECT COUNT(*) FROM ebooks WHERE is_active = 1")->fetchColumn(),
    'total_authors' => $db->query("SELECT COUNT(DISTINCT author_id) FROM ebooks")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBooks - 개발자 전용 전자책 플랫폼</title>
    <meta name="description" content="프로그래밍, 개발, 코딩을 위한 최고의 전자책을 만나보세요">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- 헤더 -->
    <header class="header">
        <div class="top-bar">
            <div class="container">
                <div>📚 개발자를 위한 프리미엄 전자책 플랫폼</div>
                <div>
                    <?php if (is_logged_in()): ?>
                        <a href="library.php">내 라이브러리</a>
                        <a href="profile.php">프로필</a>
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
                            <span class="cart-count" style="display:none;">0</span>
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

        <!-- 네비게이션 -->
        <nav class="nav">
            <div class="container">
                <ul class="nav-list">
                    <li><a href="index.php"><span class="nav-icon">🏠</span> 홈</a></li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="books.php?category=<?= $category['slug'] ?>">
                                <span class="nav-icon"><?= $category['icon'] ?></span>
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li><a href="bestsellers.php"><span class="nav-icon">🏆</span> 베스트셀러</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- 히어로 섹션 -->
    <section class="hero">
        <div class="container">
            <h1>개발자를 위한 최고의 전자책</h1>
            <p>최신 프로그래밍 기술과 실전 노하우를 담은 전문 전자책을 만나보세요</p>

            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($stats['total_books']) ?>+</span>
                    <span class="stat-label">전자책</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($stats['total_authors']) ?>+</span>
                    <span class="stat-label">전문 저자</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($stats['total_users']) ?>+</span>
                    <span class="stat-label">개발자 회원</span>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- 인기 태그 -->
        <div class="tag-cloud">
            <h3>🏷️ 인기 기술 스택</h3>
            <div class="tags">
                <?php foreach ($popular_tags as $tag): ?>
                    <a href="books.php?tag=<?= $tag['slug'] ?>" class="tag">
                        <?= htmlspecialchars($tag['name']) ?>
                        <?php if ($tag['ebook_count'] > 0): ?>
                            <span style="color: #94a3b8;">(<?= $tag['ebook_count'] ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 최신 전자책 -->
        <h2 class="section-title">📖 최신 전자책</h2>
        <div class="ebook-grid">
            <?php foreach (array_slice($latest_ebooks, 0, 8) as $ebook): ?>
                <div class="ebook-card" onclick="location.href='book.php?id=<?= $ebook['id'] ?>'">
                    <div class="ebook-cover">
                        <?= htmlspecialchars($ebook['title']) ?>
                        <span class="skill-badge"><?= htmlspecialchars($ebook['skill_level_name']) ?></span>
                    </div>
                    <div class="ebook-info">
                        <span class="category-badge">
                            <span class="nav-icon"><?= $ebook['category_icon'] ?></span>
                            <?= htmlspecialchars($ebook['category_name']) ?>
                        </span>
                        <h3 class="ebook-title"><?= htmlspecialchars($ebook['title']) ?></h3>
                        <p class="ebook-author">by <?= htmlspecialchars($ebook['author_name']) ?></p>
                        <div class="ebook-rating">
                            <span class="stars">
                                <?php
                                $rating = floatval($ebook['avg_rating']);
                                $full_stars = floor($rating);
                                $half_star = ($rating - $full_stars) >= 0.5;

                                for ($i = 0; $i < $full_stars; $i++) echo '★';
                                if ($half_star) echo '⯨';
                                for ($i = 0; $i < (5 - ceil($rating)); $i++) echo '☆';
                                ?>
                            </span>
                            <span class="rating-count">(<?= $ebook['review_count'] ?>)</span>
                        </div>
                        <div class="ebook-footer">
                            <div>
                                <?php if ($ebook['discount_price']): ?>
                                    <span class="original-price"><?= format_price($ebook['price']) ?></span>
                                <?php endif; ?>
                                <span class="price"><?= format_price($ebook['discount_price'] ?: $ebook['price']) ?></span>
                            </div>
                            <button class="btn btn-cart btn-add-cart" data-id="<?= $ebook['id'] ?>" onclick="event.stopPropagation()">
                                🛒 담기
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 인기 전자책 -->
        <h2 class="section-title">🔥 인기 전자책</h2>
        <div class="ebook-grid">
            <?php foreach (array_slice($popular_ebooks, 0, 8) as $ebook): ?>
                <div class="ebook-card" onclick="location.href='book.php?id=<?= $ebook['id'] ?>'">
                    <div class="ebook-cover">
                        <?= htmlspecialchars($ebook['title']) ?>
                        <span class="skill-badge"><?= htmlspecialchars($ebook['skill_level_name']) ?></span>
                    </div>
                    <div class="ebook-info">
                        <span class="category-badge">
                            <span class="nav-icon"><?= $ebook['category_icon'] ?></span>
                            <?= htmlspecialchars($ebook['category_name']) ?>
                        </span>
                        <h3 class="ebook-title"><?= htmlspecialchars($ebook['title']) ?></h3>
                        <p class="ebook-author">by <?= htmlspecialchars($ebook['author_name']) ?></p>
                        <div class="ebook-rating">
                            <span class="stars">
                                <?php
                                $rating = floatval($ebook['avg_rating']);
                                $full_stars = floor($rating);
                                $half_star = ($rating - $full_stars) >= 0.5;

                                for ($i = 0; $i < $full_stars; $i++) echo '★';
                                if ($half_star) echo '⯨';
                                for ($i = 0; $i < (5 - ceil($rating)); $i++) echo '☆';
                                ?>
                            </span>
                            <span class="rating-count">(<?= $ebook['review_count'] ?>)</span>
                        </div>
                        <div class="ebook-footer">
                            <div>
                                <?php if ($ebook['discount_price']): ?>
                                    <span class="original-price"><?= format_price($ebook['price']) ?></span>
                                <?php endif; ?>
                                <span class="price"><?= format_price($ebook['discount_price'] ?: $ebook['price']) ?></span>
                            </div>
                            <button class="btn btn-cart btn-add-cart" data-id="<?= $ebook['id'] ?>" onclick="event.stopPropagation()">
                                🛒 담기
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 푸터 -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>DevBooks</h3>
                    <p>개발자를 위한 최고의 전자책 플랫폼</p>
                    <p style="margin-top: 15px;">
                        📧 contact@devbooks.com<br>
                        📞 02-1234-5678
                    </p>
                </div>
                <div class="footer-section">
                    <h3>카테고리</h3>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li><a href="books.php?category=<?= $category['slug'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>고객센터</h3>
                    <ul>
                        <li><a href="faq.php">자주 묻는 질문</a></li>
                        <li><a href="support.php">고객 지원</a></li>
                        <li><a href="terms.php">이용약관</a></li>
                        <li><a href="privacy.php">개인정보처리방침</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>저자 지원</h3>
                    <ul>
                        <li><a href="author-apply.php">저자 신청</a></li>
                        <li><a href="author-guide.php">저자 가이드</a></li>
                        <li><a href="royalty.php">인세 정책</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 DevBooks. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
