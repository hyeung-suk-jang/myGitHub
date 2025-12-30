<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="container">
        <div class="post-detail">
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <span>작성자: <?= htmlspecialchars($post['author_name']) ?></span>
                <span>조회수: <?= $post['views'] ?></span>
                <span>작성일: <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></span>
            </div>
            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </div>

        <div class="comments-section">
            <h2>댓글 <?= count($comments) ?>개</h2>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="commentForm" class="comment-form">
                    <textarea name="content" placeholder="댓글을 입력하세요" required></textarea>
                    <button type="submit" class="btn">댓글 작성</button>
                </form>
            <?php else: ?>
                <p><a href="/login">로그인</a>하여 댓글을 작성하세요.</p>
            <?php endif; ?>

            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-author"><?= htmlspecialchars($comment['author_name']) ?></div>
                        <div class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                        <div class="comment-date"><?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="post-actions">
            <a href="/community" class="btn">목록으로</a>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        document.getElementById('commentForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/community/<?= $post['id'] ?>/comment', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert('댓글 작성에 실패했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
