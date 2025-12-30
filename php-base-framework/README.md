# PHP Base Framework

모든 프로젝트에 사용할 수 있는 PHP 베이스 프레임워크입니다.

## 주요 기능

### 1. 사용자 인증 시스템
- 기본 계정 로그인/회원가입
- 이메일 인증
- SNS 로그인 (Google, Facebook, Kakao)
- 비밀번호 재설정
- Rate Limiting

### 2. 보안 기능
- CSRF 토큰 보호
- XSS 방지 (입출력 필터링)
- SQL Injection 방지 (Prepared Statements)
- 비밀번호 암호화 (Argon2ID)
- 세션 보안

### 3. ORM 데이터베이스 연동
- PDO 기반 데이터베이스 연결
- Model 베이스 클래스
- CRUD 작업 지원
- 페이지네이션
- 트랜잭션 지원

### 4. 유틸리티
- Validator (데이터 검증)
- Mailer (이메일 발송)
- FileUpload (파일 업로드)
- OAuth (소셜 로그인)
- Payment (결제 처리)

### 5. MVC 구조
- Router (라우팅 시스템)
- Controllers (컨트롤러)
- Models (모델)
- Views (뷰 템플릿)
- Middleware (미들웨어)

### 6. 결제 시스템
- Toss Payments
- Iamport
- Stripe
- 환불 처리

### 7. 템플릿
- 쇼핑몰 템플릿
- 커뮤니티 템플릿
- 게시판 템플릿

## 디렉토리 구조

```
php-base-framework/
├── app/
│   ├── Controllers/       # 컨트롤러
│   ├── Models/           # 모델
│   ├── Views/            # 뷰 템플릿
│   └── Middleware/       # 미들웨어
├── core/
│   ├── Database/         # 데이터베이스
│   ├── Security/         # 보안
│   ├── Routing/          # 라우팅
│   └── Utilities/        # 유틸리티
├── config/               # 설정 파일
├── public/               # 공개 디렉토리
│   ├── css/
│   ├── js/
│   ├── images/
│   └── index.php         # 진입점
├── storage/              # 저장소
│   ├── logs/
│   ├── cache/
│   └── sessions/
├── database/             # 데이터베이스 스키마
└── routes/               # 라우트 정의
```

## 설치

### 1. 의존성 설치

```bash
composer install
```

### 2. 환경 설정

`.env.example` 파일을 `.env`로 복사하고 설정을 수정합니다:

```bash
cp .env.example .env
```

### 3. 데이터베이스 설정

`database/schema.sql` 파일을 사용하여 데이터베이스를 생성합니다:

```bash
mysql -u root -p < database/schema.sql
```

### 4. 웹 서버 설정

Apache 또는 Nginx 웹 서버를 설정하여 `public` 디렉토리를 문서 루트로 지정합니다.

## 사용법

### 라우트 정의

`routes/web.php` 파일에서 라우트를 정의합니다:

```php
$router->get('/example', 'ExampleController@index');
$router->post('/example', 'ExampleController@store', [AuthMiddleware::class]);
```

### 컨트롤러 작성

```php
namespace App\Controllers;

class ExampleController
{
    public function index()
    {
        // 로직 작성
    }
}
```

### 모델 작성

```php
namespace App\Models;

use Core\Database\Model;

class Example extends Model
{
    protected $table = 'examples';
    protected $fillable = ['name', 'description'];
}
```

### 뷰 렌더링

```php
include __DIR__ . '/../Views/example/index.php';
```

## 보안

- 모든 사용자 입력은 자동으로 필터링됩니다
- CSRF 토큰은 POST 요청에 자동으로 검증됩니다
- 비밀번호는 Argon2ID로 암호화됩니다
- SQL Injection은 Prepared Statements로 방지됩니다

## 라이센스

MIT License

## 기여

프로젝트에 기여하고 싶으시면 Pull Request를 보내주세요.
