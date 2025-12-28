# 결혼정보 회사 미팅 행사 시스템

결혼정보 회사에서 상담만 받고 미가입한 고객을 대상으로 대규모 남녀 미팅 행사를 진행하기 위한 종합 관리 시스템입니다.

## 📋 주요 기능

### 🎯 사용자 앱 기능
1. **회원가입 및 로그인**
   - 신규 회원 등록 (프로필 정보 입력)
   - 기존 고객 정보 연동
   - 상세 프로필 작성 (직업, 학력, 취미 등)

2. **행사 신청 및 결제**
   - 진행 중인 행사 목록 조회
   - 행사 신청 및 서류 제출
   - 필수 서류 업로드 (신분증, 급여통장 내역, 주민등록등본, 가족관계증명서)
   - 온라인 결제 시스템

3. **행사 참여**
   - 행사장 안내 및 스케줄 확인
   - 현장 체크인
   - 참석자 목록 확인

4. **매칭 시스템**
   - 1차, 2차, 3차 선택 (각 1,2,3 지망)
   - 자동 매칭 알고리즘 (서로 선택 시 매칭)
   - 매칭 결과 확인
   - 데이트 신청/거절 기능

### 💼 관리자 사이트 기능
1. **행사 관리**
   - 행사 등록 및 수정
   - 행사 상태 관리
   - 참가비 설정

2. **신청자 관리**
   - 신청자 목록 조회
   - 신청 상태 및 결제 상태 확인
   - 체크인 현황 확인
   - 제출 서류 확인

3. **조별 그룹 관리**
   - 그룹 생성 및 관리
   - 참석자 그룹 배정

4. **선택 및 매칭 관리**
   - 1/2/3차 선택 시작/종료
   - 선택 결과 조회
   - 자동 매칭 처리
   - 매칭 커플 확인

## 🛠️ 기술 스택

- **Frontend**: HTML5, CSS3, JavaScript, jQuery
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Server**: Apache 2.4+ with mod_rewrite

## 📦 설치 방법

### 1. 요구사항
- PHP 7.4 이상
- MySQL 5.7 이상 또는 MariaDB 10.3 이상
- Apache 웹 서버 (mod_rewrite 활성화)
- PDO PHP Extension

### 2. 설치 단계

#### 2.1 파일 배포
```bash
# 웹 서버 루트 디렉토리에 프로젝트 파일 복사
cp -r dating-event-system /var/www/html/
```

#### 2.2 데이터베이스 생성
```bash
# MySQL 접속
mysql -u root -p

# 데이터베이스 생성
CREATE DATABASE dating_event_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 사용자 생성 및 권한 부여 (선택사항)
CREATE USER 'dating_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON dating_event_system.* TO 'dating_user'@'localhost';
FLUSH PRIVILEGES;

# 스키마 import
USE dating_event_system;
SOURCE /var/www/html/dating-event-system/database/schema.sql;
```

#### 2.3 데이터베이스 설정
`config/database.php` 파일을 수정하여 데이터베이스 접속 정보를 입력합니다:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'dating_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'dating_event_system');
```

#### 2.4 파일 권한 설정
```bash
# 업로드 디렉토리 쓰기 권한 부여
chmod -R 755 /var/www/html/dating-event-system/uploads
chown -R www-data:www-data /var/www/html/dating-event-system/uploads
```

#### 2.5 Apache 설정 (선택사항)
```apache
<VirtualHost *:80>
    ServerName dating.example.com
    DocumentRoot /var/www/html/dating-event-system

    <Directory /var/www/html/dating-event-system>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/dating_error.log
    CustomLog ${APACHE_LOG_DIR}/dating_access.log combined
</VirtualHost>
```

## 🚀 사용 방법

### 사용자 앱 접속
```
http://your-domain.com/user/
```

### 관리자 사이트 접속
```
http://your-domain.com/admin/

기본 계정:
- ID: admin
- Password: admin123
```

## 📁 프로젝트 구조

```
dating-event-system/
├── config/
│   ├── database.php          # DB 연결 설정
│   └── functions.php          # 공통 함수
├── database/
│   └── schema.sql             # DB 스키마
├── user/                      # 사용자 앱
│   ├── css/
│   │   └── style.css
│   ├── js/
│   ├── index.php              # 메인 (행사 목록)
│   ├── login.php              # 로그인
│   ├── register.php           # 회원가입
│   ├── apply.php              # 행사 신청
│   ├── payment.php            # 결제
│   ├── event-info.php         # 행사 안내
│   ├── schedule.php           # 스케줄
│   ├── checkin.php            # 체크인
│   ├── participants.php       # 참석자 목록
│   ├── selection.php          # 선택하기
│   ├── matching.php           # 매칭 결과
│   └── date-request.php       # 데이트 신청
├── admin/                     # 관리자 사이트
│   ├── css/
│   │   └── admin.css
│   ├── index.php              # 대시보드
│   ├── login.php              # 로그인
│   ├── events.php             # 행사 관리
│   ├── applicants.php         # 신청자 관리
│   ├── groups.php             # 그룹 관리
│   └── selections.php         # 선택 결과
└── uploads/                   # 업로드 파일
    └── documents/             # 제출 서류
```

## 💾 데이터베이스 구조

### 주요 테이블
- **users**: 사용자 정보
- **events**: 행사 정보
- **registrations**: 행사 신청
- **documents**: 제출 서류
- **payments**: 결제 정보
- **checkins**: 체크인 기록
- **groups**: 조별 그룹
- **selections**: 선택 정보
- **matches**: 매칭 결과
- **date_requests**: 데이트 신청
- **admins**: 관리자 계정

## 🔒 보안 사항

1. **비밀번호 암호화**: PHP password_hash() 사용
2. **SQL Injection 방지**: PDO Prepared Statements 사용
3. **XSS 방지**: htmlspecialchars() 함수로 출력 이스케이핑
4. **파일 업로드 보안**:
   - 허용된 확장자만 업로드 (jpg, png, pdf)
   - 파일 크기 제한 (5MB)
   - 파일명 난수화

## ⚠️ 주의사항

1. **결제 시스템**: 현재는 테스트 모드입니다. 실제 결제 게이트웨이 연동이 필요합니다.
2. **이메일 발송**: 이메일 발송 기능은 별도로 구현이 필요합니다.
3. **백업**: 정기적으로 데이터베이스 백업을 수행하세요.
4. **보안 업데이트**: PHP, MySQL, Apache를 최신 버전으로 유지하세요.

## 🔧 커스터마이징

### 디자인 변경
- `user/css/style.css`: 사용자 앱 스타일
- `admin/css/admin.css`: 관리자 사이트 스타일

### 기능 추가
- `config/functions.php`: 공통 함수 추가
- 각 페이지의 PHP 파일에서 비즈니스 로직 수정

## 📞 문의 및 지원

시스템 사용 중 문제가 발생하거나 문의사항이 있으시면 개발팀에 연락주세요.

## 📝 라이선스

이 프로젝트는 상업적 용도로 사용 가능합니다.

## 🎉 주요 워크플로우

### 행사 진행 흐름
1. **관리자**: 행사 등록
2. **사용자**: 회원가입 → 행사 신청 → 서류 제출 → 결제
3. **사용자**: 행사 당일 체크인
4. **관리자**: 1차 선택 시작
5. **사용자**: 마음에 드는 상대 1,2,3 지망 선택
6. **관리자**: 1차 선택 종료 및 매칭 처리
7. **매칭된 커플**: 데이트 진행
8. **미매칭 사용자**: 데이트 신청/거절
9. **2차, 3차 선택 반복** (4-8 과정 반복)
10. **행사 종료**

---

**개발일**: 2025년
**버전**: 1.0.0
