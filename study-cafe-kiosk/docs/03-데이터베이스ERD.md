# 스터디 카페 키오스크 시스템 - 데이터베이스 ERD

## 1. 개요

### 1.1 ERD 소개
본 문서는 스터디 카페 키오스크 시스템의 데이터 구조를 Entity-Relationship Diagram으로 표현합니다.

**현재 버전**: LocalStorage 기반 (Mock 데이터)
**향후 버전**: 실제 데이터베이스 (MySQL, PostgreSQL 등) 연동 예정

### 1.2 데이터베이스 설계 원칙
- 정규화: 제3정규형(3NF) 준수
- 일관성: 참조 무결성 유지
- 확장성: 향후 기능 추가 고려
- 성능: 적절한 인덱싱 전략

## 2. ERD 다이어그램

### 2.1 전체 ERD

```
┌─────────────────────────────┐
│          users              │
├─────────────────────────────┤
│ PK  id: VARCHAR(36)         │
│     username: VARCHAR(100)  │
│ UQ  email: VARCHAR(255)     │
│     phone: VARCHAR(20)      │
│     password: VARCHAR(255)  │
│     is_admin: BOOLEAN       │
│     created_at: TIMESTAMP   │
└──────────────┬──────────────┘
               │
               │ 1:N (사용자는 여러 세션 가능)
               │
               ↓
┌─────────────────────────────┐      N:1      ┌─────────────────────────────┐
│        sessions             │──────────────→│          seats              │
├─────────────────────────────┤               ├─────────────────────────────┤
│ PK  id: VARCHAR(36)         │               │ PK  id: VARCHAR(36)         │
│ FK  user_id: VARCHAR(36)    │               │ UQ  number: INTEGER         │
│ FK  seat_id: VARCHAR(36)    │               │     type: ENUM              │
│ FK  ticket_id: VARCHAR(36)  │               │     status: ENUM            │
│     start_time: TIMESTAMP   │               │     floor: INTEGER          │
│     end_time: TIMESTAMP     │               └─────────────────────────────┘
│     remaining_min: INTEGER  │
│     status: ENUM            │               ┌─────────────────────────────┐
└──────────────┬──────────────┘               │         tickets             │
               │                              ├─────────────────────────────┤
               │ N:1 (세션은 하나의 이용권)   │ PK  id: VARCHAR(36)         │
               └─────────────────────────────→│     name: VARCHAR(100)      │
                                              │     type: ENUM              │
                                              │     duration: INTEGER       │
                                              │     price: INTEGER          │
                                              │     description: TEXT       │
                                              └─────────────────────────────┘
               ┌─────────────────────────────┐
               │     usage_history           │
               ├─────────────────────────────┤
               │ PK  id: VARCHAR(36)         │
               │ FK  user_id: VARCHAR(36)    │
               │     seat_number: INTEGER    │
               │     ticket_name: VARCHAR    │
               │     start_time: TIMESTAMP   │
               │     end_time: TIMESTAMP     │
               │     duration: INTEGER       │
               │     amount: INTEGER         │
               └─────────────────────────────┘
                      ↑
                      │ 1:N (사용자의 이용 내역)
                      │
               ┌──────┴──────┐
               │             │
          (users)      (sessions 종료 시 생성)


┌─────────────────────────────┐
│         payments            │  ※ 현재 미사용 (향후 확장)
├─────────────────────────────┤
│ PK  id: VARCHAR(36)         │
│ FK  user_id: VARCHAR(36)    │
│ FK  ticket_id: VARCHAR(36)  │
│     amount: INTEGER         │
│     method: ENUM            │
│     status: ENUM            │
│     created_at: TIMESTAMP   │
└─────────────────────────────┘
```

## 3. 엔티티 상세 설명

### 3.1 users (사용자)

**목적**: 시스템 사용자 정보 관리

| 컬럼명 | 데이터 타입 | 제약조건 | 설명 |
|-------|------------|---------|------|
| id | VARCHAR(36) | PK, NOT NULL | 사용자 고유 ID (UUID) |
| username | VARCHAR(100) | NOT NULL | 사용자 이름 |
| email | VARCHAR(255) | UNIQUE, NOT NULL | 이메일 주소 (로그인 ID) |
| phone | VARCHAR(20) | NOT NULL | 전화번호 |
| password | VARCHAR(255) | NOT NULL | 비밀번호 (해시값) |
| is_admin | BOOLEAN | DEFAULT FALSE | 관리자 여부 |
| created_at | TIMESTAMP | DEFAULT NOW() | 가입 일시 |

**인덱스**:
- PRIMARY KEY: `id`
- UNIQUE INDEX: `email`
- INDEX: `created_at`

**비즈니스 룰**:
- 이메일 중복 불가
- 비밀번호는 bcrypt 해싱 저장 (향후 구현)
- is_admin이 TRUE인 경우 관리자 페이지 접근 가능

### 3.2 seats (좌석)

**목적**: 스터디 카페 좌석 정보 관리

| 컬럼명 | 데이터 타입 | 제약조건 | 설명 |
|-------|------------|---------|------|
| id | VARCHAR(36) | PK, NOT NULL | 좌석 고유 ID |
| number | INTEGER | UNIQUE, NOT NULL | 좌석 번호 (1-30) |
| type | ENUM | NOT NULL | 좌석 타입: 'single', 'double', 'group' |
| status | ENUM | NOT NULL, DEFAULT 'available' | 상태: 'available', 'occupied', 'reserved' |
| floor | INTEGER | NOT NULL | 층수 (1 또는 2) |

**인덱스**:
- PRIMARY KEY: `id`
- UNIQUE INDEX: `number`
- INDEX: `status, floor`

**비즈니스 룰**:
- 총 30석 고정
- 1-20번: single (1인석)
- 21-28번: double (2인석)
- 29-30번: group (단체석)
- 1-15번: 1층
- 16-30번: 2층

**상태 전이**:
```
available → occupied (결제 완료 시)
occupied → available (퇴실 시)
available → reserved (예약 시, 향후 기능)
reserved → occupied (입실 시, 향후 기능)
reserved → available (예약 취소 시, 향후 기능)
```

### 3.3 tickets (이용권)

**목적**: 이용권 종류 및 가격 정보 관리

| 컬럼명 | 데이터 타입 | 제약조건 | 설명 |
|-------|------------|---------|------|
| id | VARCHAR(36) | PK, NOT NULL | 이용권 고유 ID |
| name | VARCHAR(100) | NOT NULL | 이용권명 |
| type | ENUM | NOT NULL | 타입: 'hourly', 'daily', 'weekly', 'monthly' |
| duration | INTEGER | NOT NULL | 이용 시간 (분 단위) |
| price | INTEGER | NOT NULL | 가격 (원) |
| description | TEXT | - | 설명 |

**인덱스**:
- PRIMARY KEY: `id`
- INDEX: `type`

**기본 데이터**:
| ID | 이용권명 | 타입 | 시간 | 가격 |
|----|---------|------|------|------|
| ticket-1 | 2시간 이용권 | hourly | 120분 | 4,000원 |
| ticket-2 | 4시간 이용권 | hourly | 240분 | 7,000원 |
| ticket-3 | 종일 이용권 | daily | 720분 | 15,000원 |
| ticket-4 | 주간 이용권 | weekly | 10,080분 | 80,000원 |
| ticket-5 | 월간 이용권 | monthly | 43,200분 | 250,000원 |

### 3.4 sessions (이용 세션)

**목적**: 사용자의 현재 이용 세션 관리

| 컬럼명 | 데이터 타입 | 제약조건 | 설명 |
|-------|------------|---------|------|
| id | VARCHAR(36) | PK, NOT NULL | 세션 고유 ID |
| user_id | VARCHAR(36) | FK, NOT NULL | 사용자 ID (users.id) |
| seat_id | VARCHAR(36) | FK, NOT NULL | 좌석 ID (seats.id) |
| ticket_id | VARCHAR(36) | FK, NOT NULL | 이용권 ID (tickets.id) |
| start_time | TIMESTAMP | NOT NULL | 시작 시간 |
| end_time | TIMESTAMP | NULL | 종료 시간 |
| remaining_min | INTEGER | NOT NULL | 남은 시간 (분) |
| status | ENUM | NOT NULL | 상태: 'active', 'completed', 'expired' |

**인덱스**:
- PRIMARY KEY: `id`
- INDEX: `user_id, status`
- INDEX: `seat_id, status`
- INDEX: `start_time`

**외래키**:
- `user_id` → `users.id` (ON DELETE CASCADE)
- `seat_id` → `seats.id` (ON DELETE RESTRICT)
- `ticket_id` → `tickets.id` (ON DELETE RESTRICT)

**비즈니스 룰**:
- 사용자는 동시에 하나의 active 세션만 가능
- end_time이 NULL이면 현재 이용 중
- 퇴실 시 end_time 기록 및 status를 'completed'로 변경
- remaining_min이 0이 되면 status를 'expired'로 변경 (향후 타이머 기능)

### 3.5 usage_history (이용 내역)

**목적**: 완료된 이용 기록 저장 및 통계

| 컬럼명 | 데이터 타입 | 제약조건 | 설명 |
|-------|------------|---------|------|
| id | VARCHAR(36) | PK, NOT NULL | 내역 고유 ID |
| user_id | VARCHAR(36) | FK, NOT NULL | 사용자 ID (users.id) |
| seat_number | INTEGER | NOT NULL | 사용한 좌석 번호 |
| ticket_name | VARCHAR(100) | NOT NULL | 이용권명 |
| start_time | TIMESTAMP | NOT NULL | 시작 시간 |
| end_time | TIMESTAMP | NOT NULL | 종료 시간 |
| duration | INTEGER | NOT NULL | 실제 이용 시간 (분) |
| amount | INTEGER | NOT NULL | 결제 금액 (원) |

**인덱스**:
- PRIMARY KEY: `id`
- INDEX: `user_id, start_time DESC`
- INDEX: `start_time`

**외래키**:
- `user_id` → `users.id` (ON DELETE CASCADE)

**비즈니스 룰**:
- 세션 종료 시 자동 생성
- 통계 및 매출 집계에 사용
- 삭제 불가 (감사 추적)

### 3.6 payments (결제) ※ 향후 확장

**목적**: 결제 정보 관리 (향후 실제 결제 시스템 연동)

| 컬럼명 | 데이터 타입 | 제약조건 | 설명 |
|-------|------------|---------|------|
| id | VARCHAR(36) | PK, NOT NULL | 결제 고유 ID |
| user_id | VARCHAR(36) | FK, NOT NULL | 사용자 ID |
| ticket_id | VARCHAR(36) | FK, NOT NULL | 이용권 ID |
| amount | INTEGER | NOT NULL | 결제 금액 |
| method | ENUM | NOT NULL | 결제 수단: 'card', 'cash', 'transfer' |
| status | ENUM | NOT NULL | 상태: 'pending', 'completed', 'failed' |
| created_at | TIMESTAMP | DEFAULT NOW() | 결제 일시 |

**인덱스**:
- PRIMARY KEY: `id`
- INDEX: `user_id, created_at DESC`
- INDEX: `status, created_at`

**외래키**:
- `user_id` → `users.id` (ON DELETE CASCADE)
- `ticket_id` → `tickets.id` (ON DELETE RESTRICT)

## 4. 관계 설명

### 4.1 users ↔ sessions (1:N)
- 한 사용자는 여러 세션을 가질 수 있음
- 하나의 세션은 한 사용자에게만 속함
- CASCADE DELETE: 사용자 삭제 시 관련 세션도 삭제

### 4.2 seats ↔ sessions (1:N)
- 한 좌석은 여러 세션에서 사용될 수 있음 (시간대별)
- 하나의 세션은 하나의 좌석만 사용
- RESTRICT DELETE: 좌석 삭제 시 관련 세션이 있으면 삭제 불가

### 4.3 tickets ↔ sessions (1:N)
- 하나의 이용권 타입은 여러 세션에서 사용될 수 있음
- 하나의 세션은 하나의 이용권만 사용
- RESTRICT DELETE: 이용권 삭제 시 관련 세션이 있으면 삭제 불가

### 4.4 users ↔ usage_history (1:N)
- 한 사용자는 여러 이용 내역을 가질 수 있음
- 하나의 내역은 한 사용자에게만 속함
- CASCADE DELETE: 사용자 삭제 시 관련 내역도 삭제

### 4.5 sessions → usage_history (1:1)
- 세션 종료 시 이용 내역 생성
- 논리적 관계 (외래키 미설정)

## 5. ENUM 타입 정의

### 5.1 seat.type (좌석 타입)
```sql
ENUM('single', 'double', 'group')
```
- `single`: 1인석
- `double`: 2인석
- `group`: 단체석

### 5.2 seat.status (좌석 상태)
```sql
ENUM('available', 'occupied', 'reserved')
```
- `available`: 이용 가능
- `occupied`: 사용 중
- `reserved`: 예약됨 (향후 기능)

### 5.3 ticket.type (이용권 타입)
```sql
ENUM('hourly', 'daily', 'weekly', 'monthly')
```
- `hourly`: 시간권 (2시간, 4시간)
- `daily`: 일일권 (종일)
- `weekly`: 주간권
- `monthly`: 월간권

### 5.4 session.status (세션 상태)
```sql
ENUM('active', 'completed', 'expired')
```
- `active`: 이용 중
- `completed`: 정상 종료
- `expired`: 시간 만료 (향후 기능)

### 5.5 payment.method (결제 수단)
```sql
ENUM('card', 'cash', 'transfer')
```
- `card`: 카드 결제
- `cash`: 현금 결제
- `transfer`: 간편 결제

### 5.6 payment.status (결제 상태)
```sql
ENUM('pending', 'completed', 'failed')
```
- `pending`: 결제 대기
- `completed`: 결제 완료
- `failed`: 결제 실패

## 6. 데이터베이스 생성 SQL (참고)

### 6.1 MySQL 예시

```sql
-- 데이터베이스 생성
CREATE DATABASE study_cafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE study_cafe;

-- users 테이블
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
);

-- seats 테이블
CREATE TABLE seats (
    id VARCHAR(36) PRIMARY KEY,
    number INTEGER NOT NULL UNIQUE,
    type ENUM('single', 'double', 'group') NOT NULL,
    status ENUM('available', 'occupied', 'reserved') NOT NULL DEFAULT 'available',
    floor INTEGER NOT NULL,
    INDEX idx_status_floor (status, floor)
);

-- tickets 테이블
CREATE TABLE tickets (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    duration INTEGER NOT NULL,
    price INTEGER NOT NULL,
    description TEXT,
    INDEX idx_type (type)
);

-- sessions 테이블
CREATE TABLE sessions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    seat_id VARCHAR(36) NOT NULL,
    ticket_id VARCHAR(36) NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NULL,
    remaining_min INTEGER NOT NULL,
    status ENUM('active', 'completed', 'expired') NOT NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_seat_status (seat_id, status),
    INDEX idx_start_time (start_time),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE RESTRICT,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT
);

-- usage_history 테이블
CREATE TABLE usage_history (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    seat_number INTEGER NOT NULL,
    ticket_name VARCHAR(100) NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    duration INTEGER NOT NULL,
    amount INTEGER NOT NULL,
    INDEX idx_user_start (user_id, start_time DESC),
    INDEX idx_start_time (start_time),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- payments 테이블 (향후 사용)
CREATE TABLE payments (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    ticket_id VARCHAR(36) NOT NULL,
    amount INTEGER NOT NULL,
    method ENUM('card', 'cash', 'transfer') NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_created (user_id, created_at DESC),
    INDEX idx_status_created (status, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT
);
```

## 7. 초기 데이터 (Seed Data)

### 7.1 좌석 초기화
```sql
-- 1-20번: 1인석
-- 21-28번: 2인석
-- 29-30번: 단체석
-- 1-15번: 1층, 16-30번: 2층
INSERT INTO seats (id, number, type, status, floor)
SELECT
    CONCAT('seat-', n),
    n,
    CASE
        WHEN n <= 20 THEN 'single'
        WHEN n <= 28 THEN 'double'
        ELSE 'group'
    END,
    'available',
    CASE WHEN n <= 15 THEN 1 ELSE 2 END
FROM
    (SELECT @row := @row + 1 AS n FROM
     (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) a,
     (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) b,
     (SELECT @row := 0) r
     LIMIT 30) numbers;
```

### 7.2 이용권 초기화
```sql
INSERT INTO tickets (id, name, type, duration, price, description) VALUES
('ticket-1', '2시간 이용권', 'hourly', 120, 4000, '2시간 자유롭게 이용 가능'),
('ticket-2', '4시간 이용권', 'hourly', 240, 7000, '4시간 자유롭게 이용 가능'),
('ticket-3', '종일 이용권', 'daily', 720, 15000, '12시간 자유롭게 이용 가능'),
('ticket-4', '주간 이용권', 'weekly', 10080, 80000, '1주일 무제한 이용 가능'),
('ticket-5', '월간 이용권', 'monthly', 43200, 250000, '1개월 무제한 이용 가능');
```

### 7.3 테스트 사용자
```sql
INSERT INTO users (id, username, email, phone, password, is_admin, created_at) VALUES
('1', 'admin', 'admin@studycafe.com', '010-1234-5678', '$2b$10$...', TRUE, NOW()),
('2', '홍길동', 'hong@example.com', '010-9876-5432', '$2b$10$...', FALSE, NOW());
```

## 8. 성능 최적화 전략

### 8.1 인덱스 전략
- PK 및 FK에 자동 인덱스
- 자주 조회되는 컬럼에 인덱스 추가
  - users.email (로그인)
  - sessions.user_id + status (현재 세션 조회)
  - usage_history.user_id + start_time (내역 조회)

### 8.2 쿼리 최적화
- JOIN 사용 시 인덱스 활용
- WHERE 절에 인덱스 컬럼 우선 사용
- SELECT *보다 필요한 컬럼만 조회

### 8.3 데이터 아카이빙
- usage_history 테이블 정기적 아카이빙 (1년 단위)
- 파티셔닝 고려 (날짜 기준)

## 9. 백업 및 복구 전략

### 9.1 백업
- 일일 전체 백업
- 시간별 트랜잭션 로그 백업
- 주간 풀 백업 + 일일 증분 백업

### 9.2 복구
- Point-in-Time Recovery 지원
- 백업 테스트 정기 수행

## 10. 마이그레이션 계획

### 10.1 LocalStorage → Database
현재 LocalStorage의 데이터를 실제 DB로 마이그레이션하는 절차:

1. 데이터베이스 생성 및 테이블 생성
2. LocalStorage 데이터 추출
3. 데이터 변환 (JSON → SQL)
4. 데이터 INSERT
5. 검증 및 테스트

### 10.2 주의사항
- 데이터 타입 변환 검증
- 외래키 제약조건 확인
- 트랜잭션 사용
- 롤백 계획 수립
