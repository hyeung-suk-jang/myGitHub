# 스터디 카페 키오스크 시스템 (FastAPI)

스터디 카페에서 키오스크를 대체할 수 있는 FastAPI 기반 백엔드 시스템입니다.

## 개요

이 프로젝트는 React + TypeScript로 작성된 **study-cafe-kiosk** 프론트엔드 앱을 FastAPI 백엔드로 변환한 것입니다. PHP 프레임워크(Laravel, CodeIgniter) 스타일의 구조를 따르면서 FastAPI의 성능과 현대적인 기능을 활용합니다.

## 주요 기능

### 사용자 기능
- **회원 가입 및 로그인**: 이메일 기반 인증 시스템
- **좌석 선택**: 1인석, 2인석, 단체석 중 선택 (총 30석)
  - 1층: 15석
  - 2층: 15석
  - 실시간 좌석 상태 확인 (이용 가능/사용 중/예약됨)
- **이용권 선택**: 다양한 요금제 제공
  - 2시간 이용권: 4,000원
  - 4시간 이용권: 7,000원
  - 종일 이용권: 15,000원
  - 주간 이용권: 80,000원
  - 월간 이용권: 250,000원
- **결제 시스템**: 카드, 간편결제, 현금 결제 지원
- **입실/퇴실 관리**: 원활한 입퇴실 프로세스
- **이용 내역**: 과거 이용 내역 조회

### 관리자 기능
- **대시보드**: 스터디 카페 운영 현황 실시간 모니터링
  - 전체 좌석 현황
  - 이용 가능/사용 중 좌석 통계
  - 총 매출 및 오늘 매출 현황
- **좌석 관리**: 실시간 좌석 상태 확인
- **세션 관리**: 현재 이용 중인 세션 목록
- **이용 내역**: 모든 사용자의 이용 기록 조회
- **매출 통계**: 기간별 매출 현황

## 기술 스택

- **FastAPI**: 고성능 비동기 웹 프레임워크
- **SQLAlchemy**: ORM (Object-Relational Mapping)
- **SQLite**: 데이터베이스 (개발용, 프로덕션에서는 PostgreSQL/MySQL 권장)
- **Pydantic**: 데이터 검증 및 스키마
- **Uvicorn**: ASGI 서버

## 프로젝트 구조

```
fastapi-php-framework/
├── app/
│   ├── controllers/          # 컨트롤러
│   │   ├── auth_controller.py       # 인증
│   │   ├── seat_controller.py       # 좌석
│   │   ├── ticket_controller.py     # 이용권
│   │   ├── session_controller.py    # 세션
│   │   ├── admin_controller.py      # 관리자
│   │   └── history_controller.py    # 이용 내역
│   ├── models/              # 데이터베이스 모델
│   │   ├── user.py                  # 사용자
│   │   ├── seat.py                  # 좌석
│   │   ├── ticket.py                # 이용권
│   │   ├── session.py               # 세션
│   │   └── usage_history.py         # 이용 내역
│   └── schemas/             # Pydantic 스키마
│       ├── auth_schema.py
│       ├── seat_schema.py
│       ├── ticket_schema.py
│       ├── session_schema.py
│       └── usage_history_schema.py
├── database/
│   └── seed_data.py         # 초기 데이터 삽입 스크립트
├── routes/
│   └── api.py               # API 라우트
└── main.py                  # 애플리케이션 진입점
```

## 설치 및 실행

### 1. 가상환경 생성 및 활성화

```bash
python -m venv venv
source venv/bin/activate  # Linux/Mac
# venv\Scripts\activate  # Windows
```

### 2. 패키지 설치

```bash
pip install -r requirements.txt
```

### 3. 초기 데이터 삽입

```bash
python database/seed_data.py
```

이 스크립트는 다음을 수행합니다:
- 데이터베이스 테이블 생성
- 테스트 사용자 2명 추가
- 좌석 30개 추가
- 이용권 5개 추가

### 4. 애플리케이션 실행

```bash
python main.py
```

또는 uvicorn으로 직접 실행:

```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### 5. API 문서 확인

- Swagger UI: http://localhost:8000/api/docs
- ReDoc: http://localhost:8000/api/redoc

## API 엔드포인트

### 인증 (Auth)

- `POST /api/auth/login` - 로그인
- `POST /api/auth/register` - 회원가입
- `POST /api/auth/logout` - 로그아웃
- `GET /api/auth/me/{user_id}` - 현재 사용자 정보

### 좌석 (Seats)

- `GET /api/seats` - 좌석 목록 조회
- `GET /api/seats/available` - 이용 가능한 좌석 조회
- `GET /api/seats/{seat_id}` - 좌석 상세 조회

### 이용권 (Tickets)

- `GET /api/tickets` - 이용권 목록 조회
- `GET /api/tickets/{ticket_id}` - 이용권 상세 조회

### 세션 (Sessions)

- `POST /api/sessions/start` - 세션 시작 (입실)
- `POST /api/sessions/{session_id}/end` - 세션 종료 (퇴실)
- `GET /api/sessions/active/{user_id}` - 사용자 활성 세션 조회
- `GET /api/sessions/all-active` - 모든 활성 세션 조회 (관리자)

### 이용 내역 (History)

- `GET /api/history/{user_id}` - 사용자 이용 내역 조회
- `GET /api/history/detail/{history_id}` - 이용 내역 상세 조회

### 관리자 (Admin)

- `GET /api/admin/dashboard` - 관리자 대시보드
- `GET /api/admin/history` - 모든 이용 내역 조회
- `GET /api/admin/revenue` - 기간별 매출 조회

## 테스트 계정

### 관리자
- 이메일: admin@studycafe.com
- 비밀번호: admin123

### 일반 사용자
- 이메일: hong@example.com
- 비밀번호: user123

## 데이터베이스 구조

### Users (사용자)
- id, username, email, phone, password, is_admin, created_at

### Seats (좌석)
- id, number, type, status, floor

### Tickets (이용권)
- id, name, type, duration, price, description

### Sessions (이용 세션)
- id, user_id, seat_id, ticket_id, start_time, end_time, remaining_minutes, status

### UsageHistory (이용 내역)
- id, user_id, seat_number, ticket_name, start_time, end_time, duration, amount

## 사용 예제

### 1. 회원가입

```bash
curl -X POST "http://localhost:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "김철수",
    "email": "kim@example.com",
    "phone": "010-1111-2222",
    "password": "password123"
  }'
```

### 2. 로그인

```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "kim@example.com",
    "password": "password123"
  }'
```

### 3. 이용 가능한 좌석 조회

```bash
curl -X GET "http://localhost:8000/api/seats/available"
```

### 4. 세션 시작 (입실)

```bash
curl -X POST "http://localhost:8000/api/sessions/start" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "2",
    "seat_id": "seat-1",
    "ticket_id": "ticket-1",
    "payment_method": "card"
  }'
```

### 5. 관리자 대시보드 조회

```bash
curl -X GET "http://localhost:8000/api/admin/dashboard"
```

## 프론트엔드 연동

이 백엔드는 기존 **study-cafe-kiosk** React 앱과 연동할 수 있습니다. 프론트엔드의 `useAuthStore`와 `useStudyCafeStore`에서 LocalStorage 대신 이 API를 호출하도록 수정하면 됩니다.

## 향후 개선 사항

- [ ] JWT 토큰 기반 인증
- [ ] 비밀번호 해싱 (bcrypt)
- [ ] 실시간 타이머 기능
- [ ] WebSocket을 통한 실시간 좌석 상태 동기화
- [ ] 푸시 알림 (시간 만료 알림)
- [ ] 사물함 관리 기능
- [ ] 음료/간식 주문 시스템
- [ ] 실제 결제 게이트웨이 연동
- [ ] 통계 및 리포트 기능 확장

## 라이선스

MIT

## 기여

이슈와 PR을 환영합니다!
