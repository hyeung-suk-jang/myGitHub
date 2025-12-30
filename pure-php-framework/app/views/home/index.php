<div class="card">
    <h1><?= escape($message) ?></h1>
    <p style="margin-top: 1rem; color: #666;">
        이 프레임워크는 Composer, Autoload, 네임스페이스 등을 사용하지 않고<br>
        순수한 PHP의 require/include만으로 동작하는 미니멀한 프레임워크입니다.
    </p>
</div>

<div class="card">
    <h2>주요 기능</h2>
    <ul style="margin-top: 1rem; padding-left: 2rem;">
        <?php foreach ($features as $feature): ?>
            <li style="margin-bottom: 0.5rem;"><?= escape($feature) ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="card">
    <h2>시작하기</h2>
    <div style="margin-top: 1rem;">
        <p style="margin-bottom: 1rem;">프레임워크 구조:</p>
        <pre style="background: #f8f8f8; padding: 1rem; border-radius: 4px; overflow-x: auto;">
pure-php-framework/
├── index.php           # 메인 진입점
├── config.php          # 설정 파일
├── core/               # 프레임워크 핵심
│   ├── functions.php   # 공통 함수
│   ├── router.php      # 라우터
│   ├── database.php    # DB 클래스
│   ├── template.php    # 템플릿 엔진
│   └── controller.php  # 베이스 컨트롤러
├── app/
│   ├── controllers/    # 컨트롤러
│   ├── models/         # 모델
│   └── views/          # 뷰
└── public/             # 정적 파일
        </pre>
    </div>
</div>

<div class="card">
    <h2>예제 페이지</h2>
    <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="/posts" class="btn">게시판 보기</a>
        <a href="/users" class="btn btn-secondary">사용자 목록</a>
        <a href="/api/posts" class="btn btn-secondary">API 예제</a>
    </div>
</div>
