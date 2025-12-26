<?php
require_once 'php/functions.php';

$ebook_id = $_GET['id'] ?? 0;
$ebook = get_ebook_by_id($ebook_id);

if (!$ebook) {
    header('Location: index.php');
    exit;
}

// 조회수 증가
increment_views($ebook_id);

$tags = get_ebook_tags($ebook_id);
$reviews = get_reviews($ebook_id, 5);
$recommended = get_recommended_ebooks($ebook['category_id'], $ebook_id, 4);
$has_purchased = is_logged_in() ? has_purchased($_SESSION['user_id'], $ebook_id) : false;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ebook['title']) ?> - DevBooks</title>
    <meta name="description" content="<?= htmlspecialchars(substr($ebook['description'] ?? '', 0, 150)) ?>">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="ebook-detail">
            <div class="detail-header">
                <div class="detail-cover" style="background: linear-gradient(135deg, <?= $ebook['category_icon'] === '⚡' ? '#667eea, #764ba2' : '#f093fb, #f5576c' ?>);">
                    <div style="color: white; font-size: 1.5rem; font-weight: 700; text-align: center; padding: 20px;">
                        <?= htmlspecialchars($ebook['title']) ?>
                    </div>
                </div>

                <div class="detail-info">
                    <span class="category-badge" style="font-size: 1rem; padding: 8px 16px;">
                        <span class="nav-icon"><?= $ebook['category_icon'] ?></span>
                        <?= htmlspecialchars($ebook['category_name']) ?>
                    </span>

                    <h1><?= htmlspecialchars($ebook['title']) ?></h1>

                    <div class="ebook-rating" style="margin: 15px 0;">
                        <span class="stars" style="font-size: 1.3rem;">
                            <?php
                            $rating = floatval($ebook['avg_rating']);
                            $full_stars = floor($rating);
                            $half_star = ($rating - $full_stars) >= 0.5;

                            for ($i = 0; $i < $full_stars; $i++) echo '★';
                            if ($half_star) echo '⯨';
                            for ($i = 0; $i < (5 - ceil($rating)); $i++) echo '☆';
                            ?>
                        </span>
                        <span style="font-size: 1.1rem; margin-left: 8px;"><?= number_format($rating, 1) ?></span>
                        <span class="rating-count" style="margin-left: 8px;">(<?= $ebook['review_count'] ?>개 리뷰)</span>
                    </div>

                    <div class="detail-meta">
                        <div class="meta-item">
                            <strong>👤 저자:</strong>
                            <span><?= htmlspecialchars($ebook['author_name']) ?></span>
                            <?php if ($ebook['github_username']): ?>
                                <a href="https://github.com/<?= htmlspecialchars($ebook['github_username']) ?>" target="_blank" style="color: var(--primary-color); margin-left: 8px;">
                                    GitHub 프로필 →
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="meta-item">
                            <strong>📊 레벨:</strong>
                            <span><?= htmlspecialchars($ebook['skill_level_name']) ?></span>
                        </div>
                        <div class="meta-item">
                            <strong>📄 페이지:</strong>
                            <span><?= number_format($ebook['page_count'] ?? 0) ?>페이지</span>
                        </div>
                        <div class="meta-item">
                            <strong>📅 출판년도:</strong>
                            <span><?= $ebook['published_year'] ?? 'N/A' ?>년</span>
                        </div>
                        <div class="meta-item">
                            <strong>👁️ 조회수:</strong>
                            <span><?= number_format($ebook['views']) ?>회</span>
                        </div>
                    </div>

                    <?php if (!empty($tags)): ?>
                        <div class="detail-tags">
                            <?php foreach ($tags as $tag): ?>
                                <a href="books.php?tag=<?= $tag['slug'] ?>" class="tag">
                                    <?= htmlspecialchars($tag['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="detail-price">
                        <?php if ($ebook['discount_price']): ?>
                            <span class="original-price" style="font-size: 1.5rem;"><?= format_price($ebook['price']) ?></span>
                        <?php endif; ?>
                        <span class="price"><?= format_price($ebook['discount_price'] ?: $ebook['price']) ?></span>
                    </div>

                    <div class="detail-actions">
                        <?php if ($has_purchased): ?>
                            <a href="download.php?id=<?= $ebook_id ?>" class="btn btn-success btn-large">
                                📥 다운로드하기
                            </a>
                            <a href="library.php" class="btn btn-primary btn-large">
                                📚 내 서재에서 보기
                            </a>
                        <?php else: ?>
                            <button class="btn btn-success btn-large btn-add-cart" data-id="<?= $ebook_id ?>">
                                🛒 장바구니에 담기
                            </button>
                            <a href="checkout.php?ebook_id=<?= $ebook_id ?>" class="btn btn-primary btn-large">
                                💳 바로 구매하기
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 설명 -->
            <div style="margin-top: 40px; padding-top: 40px; border-top: 2px solid var(--border-color);">
                <h2 style="margin-bottom: 20px;">📝 책 소개</h2>
                <div style="line-height: 1.8; color: var(--text-secondary); white-space: pre-wrap;">
                    <?= htmlspecialchars($ebook['description'] ?? '책 소개가 없습니다.') ?>
                </div>
            </div>

            <!-- 미리보기 -->
            <?php if ($ebook['content_preview']): ?>
                <div style="margin-top: 40px; padding-top: 40px; border-top: 2px solid var(--border-color);">
                    <h2 style="margin-bottom: 20px;">👀 미리보기</h2>
                    <div style="background: #f8fafc; padding: 30px; border-radius: 10px; border-left: 4px solid var(--primary-color);">
                        <pre style="white-space: pre-wrap; font-family: 'Courier New', monospace; line-height: 1.6;"><?= htmlspecialchars($ebook['content_preview']) ?></pre>
                    </div>
                </div>
            <?php endif; ?>

            <!-- GitHub 저장소 -->
            <?php if ($ebook['github_repo_url']): ?>
                <div style="margin-top: 30px; padding: 20px; background: var(--light-bg); border-radius: 10px;">
                    <h3 style="margin-bottom: 10px;">💾 샘플 코드 저장소</h3>
                    <a href="<?= htmlspecialchars($ebook['github_repo_url']) ?>" target="_blank" style="color: var(--primary-color); font-weight: 600;">
                        <?= htmlspecialchars($ebook['github_repo_url']) ?> →
                    </a>
                </div>
            <?php endif; ?>

            <!-- 리뷰 섹션 -->
            <div class="reviews-section">
                <h2 style="margin-bottom: 25px;">⭐ 리뷰 (<?= $ebook['review_count'] ?>)</h2>

                <?php if (is_logged_in() && $has_purchased): ?>
                    <div style="background: var(--light-bg); padding: 25px; border-radius: 10px; margin-bottom: 30px;">
                        <h3 style="margin-bottom: 15px;">리뷰 작성하기</h3>
                        <form id="review-form" data-ebook-id="<?= $ebook_id ?>">
                            <div style="margin-bottom: 15px;">
                                <label>종합 평가:</label>
                                <div class="rating-input">
                                    <input type="hidden" id="rating" name="rating" value="5">
                                    <div class="stars-display"></div>
                                </div>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label>코드 품질:</label>
                                <div class="rating-input">
                                    <input type="hidden" id="code-quality-rating" name="code_quality_rating" value="5">
                                    <div class="stars-display"></div>
                                </div>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label>내용 완성도:</label>
                                <div class="rating-input">
                                    <input type="hidden" id="content-rating" name="content_rating" value="5">
                                    <div class="stars-display"></div>
                                </div>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label>리뷰 내용:</label>
                                <textarea id="review-comment" name="comment" rows="4" style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">리뷰 등록</button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">
                                    <?= strtoupper(substr($review['username'], 0, 1)) ?>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($review['username']) ?></strong>
                                    <div class="stars" style="color: var(--accent-color);">
                                        <?php
                                        for ($i = 0; $i < $review['rating']; $i++) echo '★';
                                        for ($i = $review['rating']; $i < 5; $i++) echo '☆';
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <span class="review-date"><?= format_date($review['created_at']) ?></span>
                        </div>
                        <div style="margin-top: 10px; color: var(--text-secondary);">
                            <span style="margin-right: 15px;">💻 코드 품질: <?= $review['code_quality_rating'] ?>/5</span>
                            <span>📖 내용: <?= $review['content_rating'] ?>/5</span>
                        </div>
                        <p style="margin-top: 12px; line-height: 1.6;"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($reviews)): ?>
                    <p style="text-align: center; color: var(--text-light); padding: 40px;">아직 리뷰가 없습니다.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 추천 도서 -->
        <?php if (!empty($recommended)): ?>
            <h2 class="section-title">🔗 이런 책은 어때요?</h2>
            <div class="ebook-grid">
                <?php foreach ($recommended as $rec): ?>
                    <div class="ebook-card" onclick="location.href='book.php?id=<?= $rec['id'] ?>'">
                        <div class="ebook-cover">
                            <?= htmlspecialchars($rec['title']) ?>
                        </div>
                        <div class="ebook-info">
                            <span class="category-badge">
                                <?= htmlspecialchars($rec['category_name']) ?>
                            </span>
                            <h3 class="ebook-title"><?= htmlspecialchars($rec['title']) ?></h3>
                            <div class="ebook-rating">
                                <span class="stars">
                                    <?php
                                    $r = floatval($rec['avg_rating']);
                                    for ($i = 0; $i < floor($r); $i++) echo '★';
                                    if (($r - floor($r)) >= 0.5) echo '⯨';
                                    for ($i = 0; $i < (5 - ceil($r)); $i++) echo '☆';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
