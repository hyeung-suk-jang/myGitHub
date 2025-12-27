# 💻 DevBooks - 개발자 전용 전자책 플랫폼

개발자를 위한 프리미엄 전자책 판매 플랫폼입니다. HTML, CSS, JavaScript, jQuery, PHP를 기반으로 구축되었습니다.

## 🌟 주요 기능

### 사용자 기능
- ✅ 회원가입 및 로그인 시스템
- 📚 프로그래밍 언어별 카테고리 분류
- 🔍 실시간 검색 및 필터링
- 🏷️ 기술 스택 태그 기반 탐색
- ⭐ 별점 및 리뷰 시스템 (종합, 코드 품질, 내용 완성도)
- 🛒 장바구니 기능
- 💳 주문 및 결제 시스템
- 📖 내 서재 (구매한 전자책 관리)
- 👤 GitHub 프로필 연동

### 개발자 특화 기능
- 💾 샘플 코드 저장소 링크
- 📝 코드 미리보기
- 🎯 기술 레벨별 분류 (초급/중급/고급/전문가)
- 👨‍💻 저자 GitHub 프로필 연동
- 🔧 기술 스택 필터링

### 관리자 기능
- 📊 대시보드 (통계 및 분석)
- 📚 전자책 관리
- 👥 회원 관리
- 💳 주문 관리
- 🏷️ 카테고리 및 태그 관리

## 🛠 기술 스택

### Frontend
- HTML5
- CSS3 (반응형 디자인)
- JavaScript (ES6+)
- jQuery 3.6.0

### Backend
- PHP 7.4+
- MySQL / MariaDB
- PDO (데이터베이스 연결)

### 디자인
- 모던 그라디언트 UI
- 다크 모드 지원 가능
- 반응형 디자인 (모바일, 태블릿, 데스크톱)
- 부드러운 애니메이션 효과

## 📁 프로젝트 구조

```
dev-ebook-platform/
├── admin/                  # 관리자 페이지
│   └── index.php          # 관리자 대시보드
├── css/                   # 스타일시트
│   └── style.css          # 메인 스타일
├── js/                    # JavaScript 파일
│   └── script.js          # 메인 스크립트
├── php/                   # PHP 백엔드
│   ├── config.php         # 설정 파일
│   ├── database.php       # DB 연결
│   ├── functions.php      # 공통 함수
│   ├── cart_add.php       # 장바구니 추가
│   ├── cart_count.php     # 장바구니 개수
│   ├── cart_remove.php    # 장바구니 삭제
│   ├── checkout.php       # 결제 처리
│   └── logout.php         # 로그아웃
├── includes/              # 공통 포함 파일
│   ├── header.php         # 헤더
│   └── footer.php         # 푸터
├── images/                # 이미지 파일
├── uploads/               # 업로드 파일
├── index.php              # 메인 페이지
├── books.php              # 전자책 목록
├── book.php               # 전자책 상세
├── login.php              # 로그인
├── register.php           # 회원가입
├── cart.php               # 장바구니
├── database.sql           # 데이터베이스 스키마
└── README.md              # 프로젝트 문서
```

## 🚀 설치 방법

### 1. 사전 요구사항
- PHP 7.4 이상
- MySQL 5.7 이상 또는 MariaDB 10.2 이상
- Apache 또는 Nginx 웹 서버
- Composer (선택사항)

### 2. 프로젝트 클론
```bash
git clone <repository-url>
cd dev-ebook-platform
```

### 3. 데이터베이스 설정
```bash
# MySQL에 로그인
mysql -u root -p

# database.sql 파일 실행
source database.sql
```

또는 phpMyAdmin을 사용하여 `database.sql` 파일을 import 하세요.

### 4. 설정 파일 수정
`php/config.php` 파일을 열어 데이터베이스 정보를 수정하세요:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dev_ebook_store');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 5. 웹 서버 설정

#### Apache
```apache
<VirtualHost *:80>
    DocumentRoot "/path/to/dev-ebook-platform"
    ServerName devbooks.local

    <Directory "/path/to/dev-ebook-platform">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name devbooks.local;
    root /path/to/dev-ebook-platform;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. 권한 설정
```bash
chmod -R 755 dev-ebook-platform
chmod -R 777 dev-ebook-platform/uploads
```

### 7. 접속
브라우저에서 `http://localhost/dev-ebook-platform` 또는 설정한 도메인으로 접속하세요.

## 👤 기본 관리자 계정

```
이메일: admin@devbooks.com
비밀번호: admin123
```

**⚠️ 보안을 위해 첫 로그인 후 반드시 비밀번호를 변경하세요!**

## 📊 데이터베이스 구조

### 주요 테이블
- `users` - 회원 정보
- `ebooks` - 전자책 정보
- `categories` - 카테고리
- `skill_levels` - 기술 레벨
- `tags` - 기술 스택 태그
- `ebook_tags` - 전자책-태그 연결
- `reviews` - 리뷰
- `orders` - 주문
- `order_items` - 주문 상세
- `cart` - 장바구니
- `user_library` - 사용자 라이브러리

## 🎨 주요 화면

### 메인 페이지
- 히어로 섹션 with 통계
- 인기 기술 스택 태그 클라우드
- 최신 전자책
- 인기 전자책

### 전자책 상세 페이지
- 상세 정보 및 메타데이터
- 저자 정보 및 GitHub 링크
- 기술 스택 태그
- 미리보기 및 샘플 코드
- 리뷰 시스템
- 추천 도서

### 관리자 대시보드
- 실시간 통계
- 최근 주문 내역
- 회원 관리
- 전자책 관리

## 🔧 커스터마이징

### 색상 테마 변경
`css/style.css`의 `:root` 변수를 수정하세요:

```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #7c3aed;
    --accent-color: #f59e0b;
    /* ... */
}
```

### 카테고리 추가
관리자 페이지에서 직접 추가하거나, `database.sql`의 INSERT 문을 수정하세요.

## 📝 TODO / 향후 개선 사항

- [ ] 결제 게이트웨이 연동 (토스페이먼츠, 아임포트 등)
- [ ] 전자책 다운로드 기능 구현
- [ ] 이메일 인증 시스템
- [ ] 소셜 로그인 (GitHub, Google)
- [ ] 전자책 리더 뷰어
- [ ] 위시리스트 기능
- [ ] 쿠폰 및 할인 시스템
- [ ] SEO 최적화
- [ ] 관리자 전자책 CRUD 완성
- [ ] 파일 업로드 기능

## 🤝 기여하기

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 라이선스

이 프로젝트는 교육 목적으로 만들어졌습니다.

## 📧 연락처

프로젝트 관련 문의: contact@devbooks.com

## 🙏 감사의 말

이 프로젝트는 개발자들을 위한 전자책 플랫폼으로 제작되었습니다.

---

**Made with ❤️ for Developers**
