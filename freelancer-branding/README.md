# FreelancerPro - 1인 프리랜서 개인 브랜딩 사이트

1인 사업가와 프리랜서를 위한 전문적인 개인 브랜딩 웹사이트입니다.

## 기술 스택

- **HTML5**: 시맨틱 마크업
- **CSS3**: 반응형 디자인, 애니메이션, Flexbox, Grid
- **JavaScript**: ES6+
- **jQuery**: DOM 조작 및 이벤트 핸들링
- **PHP**: 문의 폼 처리

## 주요 기능

### 1. 반응형 디자인
- 모바일, 태블릿, 데스크톱 완벽 지원
- 유연한 그리드 시스템
- 햄버거 메뉴 (모바일)

### 2. 인터랙티브 요소
- 부드러운 스크롤 애니메이션
- 포트폴리오 필터링 시스템
- 자동 슬라이드 후기 섹션
- 카운터 애니메이션

### 3. 섹션 구성
- **Hero**: 메인 히어로 섹션
- **About**: 자기소개 및 통계
- **Services**: 제공 서비스 카드
- **Portfolio**: 프로젝트 갤러리
- **Testimonials**: 고객 후기
- **Contact**: 문의 폼

### 4. 폼 기능
- 실시간 유효성 검사
- AJAX 폼 제출
- PHP 이메일 발송
- 성공/오류 메시지

## 프로젝트 구조

```
freelancer-branding/
├── index.html              # 메인 HTML 파일
├── css/
│   └── style.css          # 스타일시트
├── js/
│   └── main.js            # JavaScript/jQuery
├── php/
│   └── contact.php        # 문의 폼 처리
├── images/                # 이미지 디렉토리
│   ├── profile.jpg        # 프로필 사진
│   ├── project1-6.jpg     # 포트폴리오 이미지
│   └── client1-3.jpg      # 고객 사진
└── README.md              # 프로젝트 문서

```

## 설치 및 실행

### 1. 저장소 클론
```bash
git clone <repository-url>
cd freelancer-branding
```

### 2. 웹 서버 설정

#### Apache
- `freelancer-branding` 폴더를 `htdocs`에 복사
- `http://localhost/freelancer-branding` 접속

#### PHP 내장 서버
```bash
cd freelancer-branding
php -S localhost:8000
```
- `http://localhost:8000` 접속

#### Live Server (VS Code)
- VS Code의 Live Server 확장 설치
- `index.html` 우클릭 → "Open with Live Server"

### 3. PHP 메일 설정

`php/contact.php` 파일에서 이메일 설정을 수정하세요:

```php
// 받을 이메일 주소
$to = 'your-email@example.com';

// 발신 이메일 설정
$headers[] = 'From: YourName <noreply@yourdomain.com>';
```

## 커스터마이징

### 색상 변경

`css/style.css`의 CSS 변수를 수정하세요:

```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #1e40af;
    --accent-color: #f59e0b;
    --text-dark: #1f2937;
    --text-light: #6b7280;
}
```

### 콘텐츠 수정

1. **개인 정보**: `index.html`에서 이름, 이메일, 전화번호 수정
2. **서비스**: Services 섹션의 카드 내용 수정
3. **포트폴리오**: 프로젝트 정보 및 이미지 교체
4. **후기**: Testimonials 섹션의 고객 후기 수정

### 이미지 교체

`images/` 폴더에 다음 이미지를 추가하세요:

- `profile.jpg`: 프로필 사진 (400x500px 권장)
- `project1.jpg ~ project6.jpg`: 포트폴리오 (600x400px 권장)
- `client1.jpg ~ client3.jpg`: 고객 사진 (80x80px 권장)

## 브라우저 지원

- Chrome (최신)
- Firefox (최신)
- Safari (최신)
- Edge (최신)
- Opera (최신)

## 성능 최적화

- CSS와 JS 파일 최소화
- 이미지 압축 (WebP 포맷 권장)
- 레이지 로딩 구현
- CDN 활용 (jQuery, Font Awesome)

## 보안 고려사항

- XSS 방지를 위한 입력 검증
- CSRF 토큰 추가 권장
- SQL Injection 방지 (데이터베이스 사용 시)
- HTTPS 사용 권장

## 추가 기능 제안

1. **블로그 섹션**: 콘텐츠 마케팅
2. **다국어 지원**: 영어/한국어 전환
3. **다크 모드**: 테마 전환 기능
4. **검색 엔진 최적화**: SEO 메타 태그
5. **Google Analytics**: 트래픽 분석

## 라이선스

이 프로젝트는 개인 및 상업적 용도로 자유롭게 사용할 수 있습니다.

## 지원

문의사항이 있으시면 contact@freelancerpro.com으로 연락주세요.

## 업데이트 로그

### v1.0.0 (2024-01-01)
- 초기 릴리스
- 반응형 디자인 구현
- 포트폴리오 필터링 시스템
- 문의 폼 기능
- 애니메이션 효과

## 기여

버그 리포트나 기능 제안은 이슈 트래커를 통해 제출해주세요.

---

Made with ❤️ by FreelancerPro
