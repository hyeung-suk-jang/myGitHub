<div class="card">
    <h1>게시글 수정</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/posts/<?= $post['id'] ?>/update" style="margin-top: 1.5rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title"
                   value="<?= escape($post['title']) ?>" required>
        </div>

        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="content" name="content" required><?= escape($post['content']) ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn">수정</button>
            <a href="/posts/<?= $post['id'] ?>" class="btn btn-secondary">취소</a>
        </div>
    </form>
</div>
