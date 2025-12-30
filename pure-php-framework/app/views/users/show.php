<div class="card">
    <h1><?= escape($user['username']) ?></h1>
    <div style="color: #666; margin-top: 1rem;">
        <p><strong>이메일:</strong> <?= escape($user['email']) ?></p>
        <p><strong>가입일:</strong> <?= format_date($user['created_at'], 'Y-m-d H:i') ?></p>
    </div>
</div>

<div class="card">
    <h2>최근 게시글 (<?= count($posts) ?>)</h2>

    <?php if (empty($posts)): ?>
        <p style="margin-top: 1rem; color: #666;">작성한 게시글이 없습니다.</p>
    <?php else: ?>
        <div style="margin-top: 1rem;">
            <?php foreach ($posts as $post): ?>
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 4px; margin-bottom: 1rem;">
                    <h3 style="margin-bottom: 0.5rem;">
                        <a href="/posts/<?= $post['id'] ?>" style="color: #2c3e50; text-decoration: none;">
                            <?= escape($post['title']) ?>
                        </a>
                    </h3>
                    <div style="color: #666; font-size: 0.9rem;">
                        <?= format_date($post['created_at'], 'Y-m-d H:i') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<a href="/users" class="btn btn-secondary">목록으로</a>
