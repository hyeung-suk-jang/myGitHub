<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공지사항</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="container">
        <h1>공지사항</h1>

        <table class="board-list">
            <thead>
                <tr>
                    <th width="80">번호</th>
                    <th>제목</th>
                    <th width="120">작성자</th>
                    <th width="100">작성일</th>
                    <th width="80">조회수</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts['data'])): ?>
                    <tr>
                        <td colspan="5" class="text-center">등록된 공지사항이 없습니다.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts['data'] as $post): ?>
                        <tr>
                            <td><?= $post['id'] ?></td>
                            <td><a href="/board/notice/<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></td>
                            <td><?= htmlspecialchars($post['author_name']) ?></td>
                            <td><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                            <td><?= $post['views'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $posts['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $posts['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
