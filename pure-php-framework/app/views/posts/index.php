<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>게시판</h1>
        <?php if (is_logged_in()): ?>
            <a href="/posts/create" class="btn">글쓰기</a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($posts)): ?>
    <div class="card">
        <p style="text-align: center; color: #666;">게시글이 없습니다.</p>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <div class="card">
            <h2 style="margin-bottom: 0.5rem;">
                <a href="/posts/<?= $post['id'] ?>" style="color: #2c3e50; text-decoration: none;">
                    <?= escape($post['title']) ?>
                </a>
            </h2>
            <div style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">
                작성자: <?= escape($post['username'] ?? '알 수 없음') ?> |
                작성일: <?= format_date($post['created_at'], 'Y-m-d H:i') ?>
            </div>
            <p style="color: #555;">
                <?= escape(mb_substr($post['content'], 0, 200)) ?>
                <?= mb_strlen($post['content']) > 200 ? '...' : '' ?>
            </p>
        </div>
    <?php endforeach; ?>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
        <div style="text-align: center; margin-top: 2rem;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $current_page): ?>
                    <strong style="padding: 0.5rem 1rem; background: #3498db; color: white; border-radius: 4px; margin: 0 0.25rem;">
                        <?= $i ?>
                    </strong>
                <?php else: ?>
                    <a href="/posts?page=<?= $i ?>"
                       style="padding: 0.5rem 1rem; background: #ecf0f1; color: #2c3e50; text-decoration: none; border-radius: 4px; margin: 0 0.25rem;">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
