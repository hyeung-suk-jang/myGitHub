<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <h1>로그인</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login" style="margin-top: 1.5rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label for="email">이메일</label>
            <input type="email" id="email" name="email"
                   value="<?= escape($old['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn">로그인</button>
    </form>

    <p style="margin-top: 1rem;">
        계정이 없으신가요? <a href="/register">회원가입</a>
    </p>
</div>
