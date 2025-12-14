# 솔루션 마켓 (Solution Marketplace)

전자책, 솔루션 소스 코드, 동영상 강의를 판매할 수 있는 온라인 마켓플레이스입니다.

## 주요 기능

### 1. 사용자 관리
- 일반 회원가입/로그인
- SNS 로그인 (카카오, 네이버, 구글)
- 마이페이지 및 구매 내역 관리

### 2. 셀러 기능
- 셀러 등록 및 승인 시스템
- 상품 등록/수정/삭제
  - 전자책 (PDF, EPUB)
  - 솔루션 소스 (ZIP, RAR)
  - 동영상 강의 (MP4, AVI, MOV)
- 판매 현황 대시보드
- 매출 및 정산 내역 확인

### 3. 상품 관리
- 상품 목록 및 검색
- 카테고리별 필터링
- 상품 상세 정보 및 미리보기
- 장바구니 기능

### 4. 결제 시스템
- 단일 상품 구매
- 장바구니에서 일괄 구매
- 주문 내역 관리
- 구매한 상품 다운로드

### 5. 관리자 기능
- 대시보드 (통계 및 현황)
- 셀러 승인/거절 관리
- 월별 정산 관리
- 수수료 설정 (기본 10%)

## 기술 스택

- **프론트엔드**: HTML5, CSS3, JavaScript, jQuery
- **백엔드**: PHP (순수 PHP, 프레임워크 없음)
- **데이터베이스**: MySQL (MariaDB)
- **보안**:
  - Password hashing (bcrypt)
  - XSS 방지 (htmlspecialchars)
  - CSRF 토큰
  - SQL Injection 방지 (PDO Prepared Statements)

## 설치 방법

### 1. 환경 요구사항
- PHP 7.4 이상
- MySQL 5.7 이상 또는 MariaDB 10.2 이상
- Apache 또는 Nginx 웹 서버
- curl 확장 모듈 (SNS 로그인용)
- GD 라이브러리 (이미지 처리용)

### 2. 데이터베이스 설정

```bash
# MySQL 접속
mysql -u root -p

# 데이터베이스 생성
CREATE DATABASE solution_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 사용자 생성 및 권한 부여
CREATE USER 'marketplace_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON solution_marketplace.* TO 'marketplace_user'@'localhost';
FLUSH PRIVILEGES;

# 스키마 적용
mysql -u root -p solution_marketplace < database/schema.sql
```

### 3. 설정 파일 수정

`config/database.php` 파일을 열어 데이터베이스 접속 정보를 수정합니다:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'solution_marketplace');
define('DB_USER', 'marketplace_user');
define('DB_PASS', 'your_password');
```

### 4. SNS 로그인 설정

SNS 로그인을 사용하려면 각 플랫폼에서 앱을 등록하고 `config/database.php`에 클라이언트 ID와 시크릿을 설정합니다:

**카카오 개발자 센터**: https://developers.kakao.com/
```php
define('KAKAO_CLIENT_ID', 'your_kakao_client_id');
```

**네이버 개발자 센터**: https://developers.naver.com/
```php
define('NAVER_CLIENT_ID', 'your_naver_client_id');
define('NAVER_CLIENT_SECRET', 'your_naver_client_secret');
```

**구글 클라우드 콘솔**: https://console.cloud.google.com/
```php
define('GOOGLE_CLIENT_ID', 'your_google_client_id');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret');
```

### 5. 파일 업로드 디렉토리 권한 설정

```bash
chmod 755 public/uploads
chmod 755 public/uploads/ebooks
chmod 755 public/uploads/sources
chmod 755 public/uploads/videos
chmod 755 public/uploads/thumbnails
```

### 6. 관리자 계정

데이터베이스 스키마를 설치하면 기본 관리자 계정이 생성됩니다:

- 이메일: `admin@solutionmarket.com`
- 비밀번호: `password`

**중요**: 첫 로그인 후 반드시 비밀번호를 변경하세요!

## 프로젝트 구조

```
solution-marketplace/
├── config/              # 설정 파일
│   └── database.php     # 데이터베이스 설정
├── includes/            # 공통 라이브러리
│   ├── db.php          # 데이터베이스 클래스
│   ├── session.php     # 세션 관리
│   ├── utils.php       # 유틸리티 함수
│   ├── auth.php        # 인증 함수
│   ├── header.php      # 공통 헤더
│   └── footer.php      # 공통 푸터
├── public/              # 공개 웹 루트
│   ├── index.php       # 메인 페이지
│   ├── login.php       # 로그인
│   ├── register.php    # 회원가입
│   ├── cart.php        # 장바구니
│   ├── download.php    # 다운로드
│   ├── products/       # 상품 페이지
│   ├── payment/        # 결제 페이지
│   ├── css/            # 스타일시트
│   ├── js/             # JavaScript
│   └── uploads/        # 업로드 파일
├── seller/              # 셀러 페이지
│   ├── index.php       # 셀러 대시보드
│   ├── register.php    # 셀러 등록
│   └── products/       # 상품 관리
├── admin/               # 관리자 페이지
│   ├── index.php       # 관리자 대시보드
│   ├── sellers.php     # 셀러 관리
│   └── settlements.php # 정산 관리
├── api/                 # API 엔드포인트
│   ├── auth/           # 인증 API
│   ├── cart/           # 장바구니 API
│   └── payment/        # 결제 API
└── database/            # 데이터베이스
    └── schema.sql      # 스키마 파일
```

## 사용 방법

### 일반 사용자
1. 회원가입 또는 SNS 로그인
2. 상품 목록에서 원하는 상품 검색
3. 상품 상세 페이지에서 상품 정보 확인
4. 장바구니에 추가 또는 바로 구매
5. 결제 완료 후 다운로드

### 셀러
1. 일반 회원으로 로그인
2. 셀러 등록 신청
3. 관리자 승인 대기
4. 승인 후 상품 등록
5. 판매 현황 및 정산 내역 확인

### 관리자
1. 관리자 계정으로 로그인
2. 셀러 승인/거절 처리
3. 월별 정산 데이터 생성
4. 정산 완료 처리

## 보안 주의사항

1. **비밀번호 변경**: 기본 관리자 비밀번호를 반드시 변경하세요
2. **파일 업로드**: 업로드 디렉토리의 PHP 실행을 차단하세요
3. **HTTPS**: 실제 운영 시 HTTPS를 사용하세요
4. **에러 표시**: 운영 환경에서는 `display_errors`를 off로 설정하세요
5. **데이터베이스**: 강력한 데이터베이스 비밀번호를 사용하세요

## 라이선스

이 프로젝트는 교육 목적으로 제작되었습니다.

## 문의

문제가 발생하거나 기능 개선이 필요한 경우 이슈를 등록해주세요.
