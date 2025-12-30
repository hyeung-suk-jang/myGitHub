# EquipRent - 고가 장비 대여 플랫폼

고가 장비를 안전하게 대여하고 수익을 창출할 수 있는 종합 플랫폼입니다.

## 프로젝트 개요

EquipRent는 고가의 전문 장비를 필요로 하는 사용자와 장비를 보유한 공급자를 연결하는 P2P 대여 플랫폼입니다. 에스크로 결제, 보증금 시스템, 검수 리포트 등의 기능을 통해 안전하고 투명한 거래를 지원합니다.

## 주요 기능

### 1. 사용자 관리
- **회원가입/로그인**: 이메일 기반 인증 시스템
- **사용자 타입**: 대여자(Renter), 공급자(Supplier), 양쪽 모두(Both), 관리자(Admin)
- **신원 인증(KYC)**: 신분증 업로드를 통한 본인 확인
- **프로필 관리**: 개인정보 수정 및 관리

### 2. 공급자(장비 소유자) 기능
- **장비 등록**: 상세 정보, 사진, 가격 설정
- **대여 관리**: 예약 승인/거절, 대여 현황 확인
- **검수 리포트**: 대여 전후 장비 상태 사진 업로드
- **정산 대시보드**: 수익 확인 및 출금 신청
- **장비 상태 관리**: Available, Reserved, In-use, Maintenance 등

### 3. 수요자(임차인) 기능
- **스마트 검색**: 카테고리, 지역, 날짜, 가격 등 다양한 필터
- **장비 상세 정보**: 고해상도 이미지, 사양, 리뷰 확인
- **안심 결제**: 에스크로 시스템으로 안전한 거래
- **대여 이력**: 현재/과거 대여 내역 관리
- **리뷰 작성**: 별점 및 상세 리뷰 작성

### 4. 플랫폼 관리 기능
- **전자 계약**: 대여 발생 시 자동 계약서 생성
- **보증금 시스템**: 장비 파손 대비 보증금 관리
- **정산 시스템**: 플랫폼 수수료(20%) 자동 계산 및 분배
- **분쟁 관리**: 파손/분실 시 분쟁 조정 프로세스
- **알림 시스템**: 예약, 결제, 정산 등 주요 이벤트 알림

### 5. 🆕 실시간 채팅 시스템
- **AJAX Long Polling**: 실시간 메시지 전송 및 수신
- **채팅방 관리**: 대여 건별 자동 채팅방 생성
- **파일 첨부**: 이미지 및 문서 첨부 기능
- **읽음 표시**: 메시지 읽음/안읽음 상태 관리
- **알림 통합**: 새 메시지 도착 시 알림

### 6. 🆕 토스페이먼츠 PG 연동
- **실제 결제**: 토스페이먼츠 API를 통한 실제 결제 처리
- **다양한 결제 수단**: 카드, 가상계좌, 계좌이체, 휴대폰, 상품권 등
- **에스크로 보호**: 대여 완료 후 판매자에게 자동 정산
- **결제 취소/환불**: 간편한 결제 취소 및 환불 처리
- **결제 내역**: 상세한 결제 및 정산 내역 제공

### 7. 🆕 Google Maps 통합
- **장비 위치 표시**: 지도에서 장비 위치 확인
- **주소 자동완성**: Google Places API 활용
- **거리 계산**: 사용자 위치 기반 근처 장비 찾기
- **경로 안내**: 장비 수령/반납 위치 안내
- **지역 필터링**: 지도 기반 장비 검색

### 8. 🆕 다국어 지원 (i18n)
- **3개 언어 지원**: 한국어, 영어, 일본어
- **동적 언어 전환**: 실시간 언어 변경 가능
- **로컬스토리지 저장**: 사용자 선호 언어 기억
- **확장 가능**: 쉽게 새로운 언어 추가 가능

### 9. 🆕 AI 기반 장비 추천 시스템
- **협업 필터링**: 유사 사용자 기반 추천
- **콘텐츠 기반 필터링**: 유사 장비 추천
- **트렌딩 알고리즘**: 인기 장비 실시간 분석
- **개인화 추천**: 사용자 대여 이력 기반 맞춤 추천

### 10. 🆕 보험 API 연동
- **보험료 견적**: 장비 가치 및 대여 기간 기반 자동 산출
- **단기 보험 가입**: 대여 기간에 맞춘 보험 가입
- **보험 청구**: 파손/분실 시 간편한 보험 청구
- **보험사 연동**: 외부 보험사 API 연동 구조

### 11. 🆕 React Native 모바일 앱
- **크로스 플랫폼**: iOS/Android 동시 지원
- **네이티브 성능**: React Native 기반 빠른 성능
- **백엔드 연동**: 기존 PHP API 완벽 호환
- **푸시 알림**: 모바일 푸시 알림 지원 (구현 예정)

## 기술 스택

### Frontend
- **HTML5**: 웹 페이지 구조
- **CSS3**: 반응형 디자인 및 스타일링
- **JavaScript (ES6+)**: 클라이언트 사이드 로직
- **jQuery 3.6**: DOM 조작 및 AJAX 통신

### Backend
- **PHP 7.4+**: 서버 사이드 로직
- **MySQL/MariaDB**: 데이터베이스
- **PDO**: 데이터베이스 추상화 계층

### 아키텍처
- **RESTful API**: PHP 기반 REST API
- **MVC 패턴**: 코드 구조화
- **세션 기반 인증**: PHP 세션 관리

## 데이터베이스 설계

### 주요 테이블
- `users`: 사용자 정보
- `equipment`: 장비 정보
- `equipment_categories`: 장비 카테고리
- `equipment_images`: 장비 이미지
- `rentals`: 대여 정보
- `payments`: 결제 정보
- `settlements`: 정산 정보
- `inspection_reports`: 검수 리포트
- `reviews`: 리뷰 및 평점
- `disputes`: 분쟁 관리
- `notifications`: 알림

## 설치 방법

### 1. 시스템 요구사항
- Apache 2.4+ 또는 Nginx
- PHP 7.4 이상
- MySQL 5.7+ 또는 MariaDB 10.2+
- 파일 업로드 권한

### 2. 설치 단계

#### 2.1 프로젝트 클론
```bash
git clone https://github.com/yourusername/equipment-rental-platform.git
cd equipment-rental-platform
```

#### 2.2 데이터베이스 설정
```bash
# MySQL/MariaDB 접속
mysql -u root -p

# 데이터베이스 생성 및 스키마 적용
mysql -u root -p < database.sql
```

#### 2.3 데이터베이스 설정 수정
`api/config.php` 파일을 열고 데이터베이스 연결 정보를 수정합니다:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'equipment_rental');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

#### 2.4 디렉토리 권한 설정
```bash
# 업로드 디렉토리 권한 설정
chmod 755 uploads/
chmod 755 uploads/equipment/
chmod 755 uploads/inspection/
chmod 755 uploads/profile/

# 로그 디렉토리 권한 설정
mkdir logs
chmod 755 logs/
```

#### 2.5 웹 서버 설정

**Apache (.htaccess)**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # PHP 설정
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</IfModule>
```

**Nginx**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/equipment-rental-platform;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location /uploads/ {
        expires 30d;
    }

    client_max_body_size 10M;
}
```

#### 2.6 관리자 계정 설정
데이터베이스에 기본 관리자 계정이 생성됩니다:
- 이메일: admin@equiprent.com
- 비밀번호: admin123! (실제 해시값으로 변경 필요)

## 프로젝트 구조

```
equipment-rental-platform/
├── index.html              # 메인 페이지
├── css/
│   └── style.css          # 공통 스타일시트
├── js/
│   └── main.js            # 공통 JavaScript
├── api/
│   ├── config.php         # 데이터베이스 설정 및 공통 함수
│   ├── auth.php           # 인증 API
│   ├── equipment.php      # 장비 관리 API
│   ├── rental.php         # 대여 관리 API
│   └── payment.php        # 결제/정산 API
├── pages/
│   ├── login.html         # 로그인 페이지
│   ├── register.html      # 회원가입 페이지
│   ├── equipment-list.html           # 장비 목록
│   ├── supplier-dashboard.html       # 공급자 대시보드
│   └── renter-dashboard.html         # 수요자 대시보드
├── uploads/               # 업로드 파일 저장
│   ├── equipment/        # 장비 이미지
│   ├── inspection/       # 검수 이미지
│   └── profile/          # 프로필/신분증
├── logs/                  # 로그 파일
├── database.sql          # 데이터베이스 스키마
└── README.md             # 프로젝트 문서
```

## API 엔드포인트

### 인증 (auth.php)
- `POST /api/auth.php?action=register` - 회원가입
- `POST /api/auth.php?action=login` - 로그인
- `GET /api/auth.php?action=logout` - 로그아웃
- `GET /api/auth.php?action=profile` - 프로필 조회
- `PUT /api/auth.php?action=profile` - 프로필 수정
- `GET /api/auth.php?action=check` - 인증 상태 확인
- `POST /api/auth.php?action=verify-identity` - 신원 인증

### 장비 관리 (equipment.php)
- `GET /api/equipment.php?action=list` - 장비 목록 조회
- `GET /api/equipment.php?action=detail` - 장비 상세 조회
- `POST /api/equipment.php?action=create` - 장비 등록
- `PUT /api/equipment.php?action=update` - 장비 수정
- `DELETE /api/equipment.php?action=delete` - 장비 삭제
- `GET /api/equipment.php?action=my-equipment` - 내 장비 조회
- `POST /api/equipment.php?action=upload-image` - 장비 이미지 업로드
- `GET /api/equipment.php?action=categories` - 카테고리 조회
- `PUT /api/equipment.php?action=update-status` - 장비 상태 변경

### 대여 관리 (rental.php)
- `POST /api/rental.php?action=create` - 대여 신청
- `GET /api/rental.php?action=list` - 대여 목록 (관리자)
- `GET /api/rental.php?action=detail` - 대여 상세
- `GET /api/rental.php?action=my-rentals` - 내 대여 목록
- `PUT /api/rental.php?action=approve` - 대여 승인
- `PUT /api/rental.php?action=reject` - 대여 거절
- `PUT /api/rental.php?action=cancel` - 대여 취소
- `PUT /api/rental.php?action=confirm-pickup` - 픽업 확인
- `PUT /api/rental.php?action=confirm-return` - 반납 확인
- `POST /api/rental.php?action=create-inspection` - 검수 리포트 작성
- `POST /api/rental.php?action=upload-inspection-image` - 검수 이미지 업로드
- `POST /api/rental.php?action=review` - 리뷰 작성

### 결제/정산 (payment.php)
- `POST /api/payment.php?action=create-payment` - 결제 생성
- `POST /api/payment.php?action=confirm-payment` - 결제 확인
- `POST /api/payment.php?action=refund` - 환불 처리
- `GET /api/payment.php?action=settlements` - 정산 목록 (관리자)
- `GET /api/payment.php?action=my-settlements` - 내 정산 목록
- `POST /api/payment.php?action=request-settlement` - 정산 요청
- `POST /api/payment.php?action=process-settlement` - 정산 처리 (관리자)
- `GET /api/payment.php?action=payment-history` - 결제 내역

## 보안 기능

### 1. 인증 및 권한
- 세션 기반 인증
- 비밀번호 암호화 (password_hash)
- CSRF 방지
- XSS 방지 (입력값 sanitize)

### 2. 데이터 보호
- SQL Injection 방지 (Prepared Statements)
- 파일 업로드 검증 (타입, 크기)
- 민감 정보 암호화

### 3. API 보안
- CORS 설정
- Rate Limiting (구현 권장)
- Input Validation

## 🎯 향후 확장 가능성

### 추가 구현 가능 기능
1. ✅ **실시간 채팅**: 공급자-대여자 간 실시간 메시징 (완료)
2. ✅ **보험 연동**: 대여 기간 중 파손 보험 (완료)
3. ✅ **지도 통합**: Google Maps API를 통한 위치 표시 (완료)
4. **배송 추적**: 택배 API 연동 (구현 예정)
5. ✅ **결제 게이트웨이**: 토스페이먼츠 연동 (완료)
6. ✅ **모바일 앱**: React Native 앱 (완료)
7. ✅ **AI 추천**: 사용자 기반 장비 추천 시스템 (완료)
8. ✅ **다국어 지원**: i18n 구현 (한/영/일 완료)

## 라이선스

이 프로젝트는 MIT 라이선스를 따릅니다.

## 기여

버그 리포트, 기능 제안, Pull Request를 환영합니다!

## 문의

- Email: support@equiprent.com
- GitHub Issues: [프로젝트 이슈 페이지]

## 변경 이력

### v2.0.0 (2024-12-30) - 🆕 Major Update
- **실시간 채팅 시스템**: AJAX Long Polling 기반 실시간 메시징
- **토스페이먼츠 연동**: 실제 PG 결제 시스템 통합
- **Google Maps 통합**: 장비 위치 표시 및 지도 검색
- **다국어 지원**: 한국어, 영어, 일본어 3개 언어 지원
- **AI 추천 시스템**: 협업 필터링 기반 장비 추천
- **보험 API 연동**: 대여 보험 견적 및 가입 시스템
- **React Native 앱**: 모바일 앱 기본 구조 구현

### v1.0.0 (2024-12-30)
- 초기 릴리스
- 기본 사용자 인증 시스템
- 장비 등록 및 검색
- 대여 예약 및 관리
- 결제 및 정산 시스템
- 검수 리포트 기능
- 리뷰 시스템

---

**참고**: 이 프로젝트는 데모/학습 목적으로 제작되었습니다. 실제 운영 환경에서는 추가적인 보안 강화, 성능 최적화, 법적 검토가 필요합니다.
