<?php
if (!function_exists('get_categories')) {
    require_once __DIR__ . '/../php/functions.php';
}

$footer_categories = get_categories();
?>
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
                    <?php foreach (array_slice($footer_categories, 0, 5) as $category): ?>
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
