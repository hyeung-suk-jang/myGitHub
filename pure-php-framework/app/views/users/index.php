<div class="card">
    <h1>사용자 목록</h1>
</div>

<?php if (empty($users)): ?>
    <div class="card">
        <p style="text-align: center; color: #666;">등록된 사용자가 없습니다.</p>
    </div>
<?php else: ?>
    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd;">
                    <th style="padding: 1rem; text-align: left;">ID</th>
                    <th style="padding: 1rem; text-align: left;">사용자명</th>
                    <th style="padding: 1rem; text-align: left;">이메일</th>
                    <th style="padding: 1rem; text-align: left;">가입일</th>
                    <th style="padding: 1rem; text-align: left;">액션</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 1rem;"><?= $user['id'] ?></td>
                        <td style="padding: 1rem;"><?= escape($user['username']) ?></td>
                        <td style="padding: 1rem;"><?= escape($user['email']) ?></td>
                        <td style="padding: 1rem;"><?= format_date($user['created_at'], 'Y-m-d') ?></td>
                        <td style="padding: 1rem;">
                            <a href="/users/<?= $user['id'] ?>" class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.9rem;">
                                보기
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
