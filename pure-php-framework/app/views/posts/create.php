<div class="card">
    <h1>게시글 작성</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="padding-left: 1.5rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?= escape($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/posts/create" style="margin-top: 1.5rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title"
                   value="<?= escape($old['title'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="content" name="content" required><?= escape($old['content'] ?? '') ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn">작성</button>
            <a href="/posts" class="btn btn-secondary">취소</a>
        </div>
    </form>
</div>
