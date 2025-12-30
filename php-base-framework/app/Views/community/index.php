<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>커뮤니티</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="container">
        <div class="community-header">
            <h1>커뮤니티</h1>
            <a href="/community/create" class="btn">글쓰기</a>
        </div>

        <div class="category-tabs">
            <a href="?category=general" class="<?= $category == 'general' ? 'active' : '' ?>">자유게시판</a>
            <a href="?category=qna" class="<?= $category == 'qna' ? 'active' : '' ?>">Q&A</a>
            <a href="?category=notice" class="<?= $category == 'notice' ? 'active' : '' ?>">공지사항</a>
        </div>

        <table class="post-list">
            <thead>
                <tr>
                    <th>번호</th>
                    <th>제목</th>
                    <th>작성자</th>
                    <th>조회수</th>
                    <th>작성일</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts['data'] as $post): ?>
                    <tr>
                        <td><?= $post['id'] ?></td>
                        <td><a href="/community/<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></td>
                        <td><?= htmlspecialchars($post['author_name']) ?></td>
                        <td><?= $post['views'] ?></td>
                        <td><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $posts['last_page']; $i++): ?>
                <a href="?category=<?= $category ?>&page=<?= $i ?>" class="<?= $i == $posts['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
