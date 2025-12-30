# 순수 PHP 프레임워크

Composer, Autoload, Namespace 없이 순수한 PHP의 `require`/`include`만으로 동작하는 미니멀한 MVC 프레임워크입니다.

## 특징

- ✅ **Composer 없음**: 외부 의존성 없이 순수 PHP만 사용
- ✅ **단순한 라우팅**: 복잡한 정규식 없이 간단한 패턴 매칭
- ✅ **PDO 데이터베이스**: PDO를 사용한 간단한 데이터베이스 래퍼
- ✅ **순수 PHP 템플릿**: 별도 템플릿 언어 없이 순수 PHP 사용
- ✅ **CSRF 보호**: 내장 CSRF 토큰 생성 및 검증
- ✅ **세션 관리**: 간단한 세션 헬퍼 함수
- ✅ **파일 업로드**: 파일 업로드 유효성 검사 및 처리

## 프로젝트 구조

```
pure-php-framework/
├── index.php           # 메인 진입점
├── config.php          # 설정 파일
├── .htaccess          # Apache 리라이트 규칙
├── core/              # 프레임워크 핵심
│   ├── functions.php   # 공통 함수
│   ├── router.php      # 라우터 클래스
│   ├── database.php    # 데이터베이스 클래스
│   ├── template.php    # 템플릿 엔진
│   └── controller.php  # 베이스 컨트롤러
├── app/
│   ├── controllers/    # 컨트롤러
│   ├── models/         # 모델 (필요시)
│   └── views/          # 뷰 템플릿
│       ├── layouts/    # 레이아웃
│       ├── partials/   # 부분 뷰
│       └── ...
├── public/            # 정적 파일
│   ├── css/
│   └── js/
├── uploads/           # 업로드 파일
└── database/          # 데이터베이스 스키마
    └── schema.sql
```

## 설치 및 설정

### 1. 프로젝트 복사

```bash
git clone <repository-url>
cd pure-php-framework
```

### 2. 데이터베이스 설정

```bash
# MySQL/MariaDB에서 데이터베이스 생성
mysql -u root -p
CREATE DATABASE your_database;
USE your_database;
SOURCE database/schema.sql;
```

### 3. 설정 파일 수정

`config.php` 파일에서 데이터베이스 정보를 수정하세요:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. 웹 서버 설정

#### Apache

`.htaccess` 파일이 이미 포함되어 있습니다. `mod_rewrite`가 활성화되어 있는지 확인하세요.

```bash
# Ubuntu/Debian
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### PHP 내장 서버 (개발용)

```bash
php -S localhost:8000
```

브라우저에서 `http://localhost:8000` 접속

### 5. 권한 설정

```bash
chmod -R 755 pure-php-framework
chmod -R 777 uploads
```

## 사용 방법

### 라우트 정의 (index.php)

```php
$router = new Router();

// GET 라우트
$router->get('/', 'Home', 'index');
$router->get('/posts', 'Post', 'index');
$router->get('/posts/:id', 'Post', 'show');

// POST 라우트
$router->post('/login', 'Auth', 'doLogin');
$router->post('/posts/create', 'Post', 'store');

// 라우트 실행
$router->dispatch();
```

### 컨트롤러 작성

```php
// app/controllers/Example.php
require_once CORE_PATH . '/controller.php';

class ExampleController extends Controller {
    public function index() {
        $data = [
            'title' => '예제 페이지',
            'items' => ['Item 1', 'Item 2', 'Item 3']
        ];

        $this->view('example/index', $data);
    }

    public function show($id) {
        $item = $this->db->findById('items', $id);

        $this->view('example/show', [
            'title' => $item['name'],
            'item' => $item
        ]);
    }
}
```

### 뷰 작성

```php
<!-- app/views/example/index.php -->
<div class="card">
    <h1><?= escape($title) ?></h1>
    <ul>
        <?php foreach ($items as $item): ?>
            <li><?= escape($item) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
```

### 데이터베이스 사용

```php
// 인스턴스 가져오기
$db = db();

// 조회
$users = $db->selectAll('users');
$user = $db->findById('users', 1);

// 삽입
$id = $db->insertData('users', [
    'username' => 'john',
    'email' => 'john@example.com',
    'password' => hash_password('secret'),
    'created_at' => date('Y-m-d H:i:s')
]);

// 업데이트
$db->updateData('users',
    ['username' => 'john_updated'],
    'id = ?',
    [1]
);

// 삭제
$db->deleteById('users', 1);

// 커스텀 쿼리
$posts = $db->query(
    "SELECT p.*, u.username FROM posts p
     LEFT JOIN users u ON p.user_id = u.id
     WHERE p.id = ?",
    [$post_id]
);
```

### 유용한 헬퍼 함수

```php
// XSS 방지 출력
echo escape($user_input);

// 세션
session_set('user_id', 1);
$user_id = session_get('user_id');
session_delete('user_id');

// CSRF 보호
$token = csrf_token();
csrf_verify($_POST['csrf_token']);

// 리다이렉트
redirect('/login');

// JSON 응답
json_response(['success' => true, 'data' => $data]);

// POST/GET 데이터
$email = post('email');
$page = get('page', 1); // 기본값 1

// 로그인 체크
require_login(); // 로그인 안 되어 있으면 리다이렉트
if (is_logged_in()) {
    // ...
}

// 파일 업로드
$result = upload_file($_FILES['photo'], 'photos');
if ($result['success']) {
    echo $result['filename'];
}
```

## 예제

### 회원가입/로그인

이미 포함된 `AuthController`를 참조하세요:
- `/register` - 회원가입
- `/login` - 로그인
- `/logout` - 로그아웃

### 게시판 CRUD

이미 포함된 `PostController`를 참조하세요:
- `/posts` - 게시글 목록
- `/posts/:id` - 게시글 상세
- `/posts/create` - 게시글 작성
- `/posts/:id/edit` - 게시글 수정
- `/posts/:id/delete` - 게시글 삭제

### API 엔드포인트

```php
// app/controllers/Api.php
class ApiController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->noLayout(); // 레이아웃 없이
    }

    public function users() {
        $users = $this->db->selectAll('users');
        $this->json(['success' => true, 'data' => $users]);
    }
}
```

## 보안 고려사항

1. **CSRF 보호**: 모든 POST 폼에 CSRF 토큰 포함
   ```php
   <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
   ```

2. **XSS 방지**: 출력 시 항상 `escape()` 사용
   ```php
   <?= escape($user_input) ?>
   ```

3. **SQL Injection 방지**: Prepared Statements 사용
   ```php
   $db->query("SELECT * FROM users WHERE id = ?", [$id]);
   ```

4. **비밀번호 해싱**: `password_hash()` 사용
   ```php
   $hash = hash_password($password);
   verify_password($password, $hash);
   ```

## 시스템 요구사항

- PHP 7.4 이상
- MySQL 5.7 이상 또는 MariaDB 10.2 이상
- Apache (mod_rewrite) 또는 Nginx
- PDO MySQL 확장

## 라이센스

MIT License

## 기여

이슈 및 풀 리퀘스트 환영합니다!

## 주의사항

이 프레임워크는 학습 목적 및 소규모 프로젝트를 위한 것입니다. 대규모 프로덕션 환경에서는 Laravel, Symfony 등의 완성도 높은 프레임워크 사용을 권장합니다.
