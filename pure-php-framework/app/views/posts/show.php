<div class="card">
    <h1><?= escape($post['title']) ?></h1>
    <div style="color: #666; font-size: 0.9rem; margin: 1rem 0; padding-bottom: 1rem; border-bottom: 1px solid #eee;">
        작성자: <?= escape($post['username'] ?? '알 수 없음') ?> |
        작성일: <?= format_date($post['created_at'], 'Y-m-d H:i') ?>
        <?php if ($post['updated_at']): ?>
            | 수정일: <?= format_date($post['updated_at'], 'Y-m-d H:i') ?>
        <?php endif; ?>
    </div>
    <div style="line-height: 1.8; white-space: pre-wrap;">
        <?= escape($post['content']) ?>
    </div>

    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="/posts" class="btn btn-secondary">목록</a>
        <?php if (is_logged_in() && session_get('user_id') == $post['user_id']): ?>
            <a href="/posts/<?= $post['id'] ?>/edit" class="btn">수정</a>
            <form method="POST" action="/posts/<?= $post['id'] ?>/delete" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('정말 삭제하시겠습니까?')">삭제</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- 댓글 섹션 -->
<div class="card">
    <h3>댓글 (<?= count($comments) ?>)</h3>

    <?php if (empty($comments)): ?>
        <p style="margin-top: 1rem; color: #666;">댓글이 없습니다.</p>
    <?php else: ?>
        <div style="margin-top: 1rem;">
            <?php foreach ($comments as $comment): ?>
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 4px; margin-bottom: 1rem;">
                    <div style="font-weight: bold; margin-bottom: 0.5rem;">
                        <?= escape($comment['username'] ?? '알 수 없음') ?>
                        <span style="font-weight: normal; color: #666; font-size: 0.9rem;">
                            | <?= format_date($comment['created_at'], 'Y-m-d H:i') ?>
                        </span>
                    </div>
                    <div><?= escape($comment['content']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
