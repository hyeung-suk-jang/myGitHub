<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <h1>회원가입</h1>

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

    <form method="POST" action="/register" style="margin-top: 1.5rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label for="username">사용자명</label>
            <input type="text" id="username" name="username"
                   value="<?= escape($old['username'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">이메일</label>
            <input type="email" id="email" name="email"
                   value="<?= escape($old['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirm">비밀번호 확인</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>

        <button type="submit" class="btn">회원가입</button>
    </form>

    <p style="margin-top: 1rem;">
        이미 계정이 있으신가요? <a href="/login">로그인</a>
    </p>
</div>
