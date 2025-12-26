<?php
require_once 'php/functions.php';

$filters = [
    'category' => $_GET['category'] ?? '',
    'skill_level' => $_GET['skill_level'] ?? '',
    'tag' => $_GET['tag'] ?? '',
    'search' => $_GET['search'] ?? '',
    'order' => $_GET['order'] ?? 'latest'
];

$ebooks = get_ebooks($filters);
$categories = get_categories();
$skill_levels = get_skill_levels();
$popular_tags = get_popular_tags();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>전자책 목록 - DevBooks</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <!-- 필터 -->
        <div class="filters">
            <div class="filter-group">
                <div class="filter-item">
                    <label for="filter-category">📚 카테고리</label>
                    <select id="filter-category" class="filter-select">
                        <option value="">전체</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['slug'] ?>" <?= $filters['category'] === $cat['slug'] ? 'selected' : '' ?>>
                                <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="filter-skill">🎯 난이도</label>
                    <select id="filter-skill" class="filter-select">
                        <option value="">전체</option>
                        <?php foreach ($skill_levels as $level): ?>
                            <option value="<?= $level['slug'] ?>" <?= $filters['skill_level'] === $level['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($level['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="filter-order">🔄 정렬</label>
                    <select id="filter-order" class="filter-select">
                        <option value="latest" <?= $filters['order'] === 'latest' ? 'selected' : '' ?>>최신순</option>
                        <option value="popular" <?= $filters['order'] === 'popular' ? 'selected' : '' ?>>인기순</option>
                        <option value="rating" <?= $filters['order'] === 'rating' ? 'selected' : '' ?>>평점순</option>
                        <option value="price_low" <?= $filters['order'] === 'price_low' ? 'selected' : '' ?>>가격 낮은순</option>
                        <option value="price_high" <?= $filters['order'] === 'price_high' ? 'selected' : '' ?>>가격 높은순</option>
                    </select>
                </div>

                <input type="hidden" id="filter-tag" value="<?= htmlspecialchars($filters['tag']) ?>">
            </div>
        </div>

        <!-- 태그 클라우드 -->
        <?php if (!empty($popular_tags)): ?>
            <div class="tag-cloud">
                <h3>🏷️ 인기 기술 스택</h3>
                <div class="tags">
                    <?php foreach ($popular_tags as $tag): ?>
                        <a href="#" class="tag <?= $filters['tag'] === $tag['slug'] ? 'active' : '' ?>" data-tag="<?= $tag['slug'] ?>">
                            <?= htmlspecialchars($tag['name']) ?>
                            <?php if ($tag['ebook_count'] > 0): ?>
                                <span style="color: #94a3b8;">(<?= $tag['ebook_count'] ?>)</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 결과 -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0 20px;">
            <h2 class="section-title" style="margin: 0;">
                📖 전자책 목록
                <?php if ($filters['category']): ?>
                    <?php
                    $cat = array_filter($categories, fn($c) => $c['slug'] === $filters['category']);
                    $cat = reset($cat);
                    ?>
                    - <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                <?php endif; ?>
            </h2>
            <p style="color: var(--text-secondary);">총 <?= count($ebooks) ?>권</p>
        </div>

        <div class="ebook-grid">
            <?php if (empty($ebooks)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <p style="font-size: 1.2rem; color: var(--text-light);">😢 검색 결과가 없습니다.</p>
                </div>
            <?php else: ?>
                <?php foreach ($ebooks as $ebook): ?>
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
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
