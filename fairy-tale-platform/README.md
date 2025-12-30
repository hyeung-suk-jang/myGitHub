# 동화책 열람 플랫폼

작가와 독자를 연결하는 동화책 열람 플랫폼입니다. 작가들이 자신의 동화책을 등록하고, 독자들이 결제 후 열람할 수 있으며, 수익을 플랫폼과 작가가 배분하는 시스템입니다.

## 주요 기능

### 작가 기능
- 회원가입 및 로그인 (작가 계정)
- 동화책 작품 등록 및 관리
- 작품 수정 및 삭제
- 수익 현황 확인
- 정산 요청 (최소 10,000원)
- 정산 내역 조회

### 독자 기능
- 회원가입 및 로그인 (독자 계정)
- 동화책 목록 조회
- 동화책 구매 (결제)
- 구매한 동화책 열람
- 결제 내역 조회

### 플랫폼 기능
- 결제 시 자동 수익 배분 (플랫폼 30%, 작가 70%)
- 작가 잔액 관리
- 정산 시스템

## 기술 스택

### Backend
- **Python 3.8+**
- **FastAPI** - 웹 프레임워크
- **SQLAlchemy** - ORM
- **SQLite** - 데이터베이스
- **JWT** - 인증
- **Uvicorn** - ASGI 서버

### Frontend
- **HTML5**
- **CSS3**
- **JavaScript**
- **jQuery**
- **Jinja2** - 템플릿 엔진

## 프로젝트 구조

```
fairy-tale-platform/
├── backend/
│   ├── main.py                 # FastAPI 애플리케이션
│   ├── database.py             # 데이터베이스 설정
│   ├── models.py               # SQLAlchemy 모델
│   ├── schemas.py              # Pydantic 스키마
│   ├── auth.py                 # 인증 관련 함수
│   ├── requirements.txt        # Python 패키지
│   └── routes/
│       ├── users.py           # 사용자 API
│       ├── books.py           # 동화책 API
│       ├── payments.py        # 결제 API
│       └── settlements.py     # 정산 API
└── frontend/
    ├── static/
    │   ├── css/
    │   │   └── style.css      # 스타일시트
    │   └── js/
    │       └── app.js         # JavaScript
    └── templates/
        ├── index.html                  # 메인 페이지
        ├── login.html                  # 로그인
        ├── register.html               # 회원가입
        ├── author_dashboard.html       # 작가 대시보드
        ├── reader_dashboard.html       # 독자 대시보드
        ├── book_upload.html            # 작품 등록
        └── book_view.html              # 작품 보기
```

## 설치 및 실행

### 1. 필요 패키지 설치

```bash
cd fairy-tale-platform/backend
pip install -r requirements.txt
```

### 2. 서버 실행

```bash
cd fairy-tale-platform/backend
python main.py
```

또는 uvicorn 직접 실행:

```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### 3. 브라우저에서 접속

```
http://localhost:8000
```

## API 엔드포인트

### 사용자 관련
- `POST /api/users/register` - 회원가입
- `POST /api/users/login` - 로그인
- `GET /api/users/me` - 현재 사용자 정보
- `GET /api/users/balance` - 잔액 조회

### 동화책 관련
- `POST /api/books/` - 동화책 등록 (작가 전용)
- `GET /api/books/` - 동화책 목록
- `GET /api/books/{book_id}` - 동화책 상세 정보
- `GET /api/books/{book_id}/content` - 동화책 내용 (구매자/작가만)
- `GET /api/books/my-books` - 내 작품 목록 (작가 전용)
- `PUT /api/books/{book_id}` - 동화책 수정 (작가 전용)
- `DELETE /api/books/{book_id}` - 동화책 삭제 (작가 전용)
- `GET /api/books/{book_id}/purchased` - 구매 여부 확인

### 결제 관련
- `POST /api/payments/` - 동화책 구매
- `GET /api/payments/my-payments` - 내 결제 내역
- `GET /api/payments/my-earnings` - 내 수익 현황 (작가 전용)
- `GET /api/payments/purchased-books` - 구매한 동화책 목록

### 정산 관련
- `POST /api/settlements/` - 정산 요청 (작가 전용)
- `GET /api/settlements/my-settlements` - 내 정산 내역 (작가 전용)
- `GET /api/settlements/{settlement_id}` - 정산 상세 정보 (작가 전용)

## 데이터베이스 모델

### User (사용자)
- id: 고유 ID
- email: 이메일
- username: 사용자명
- hashed_password: 암호화된 비밀번호
- user_type: 사용자 유형 (author/reader)
- balance: 잔액 (작가용)
- created_at: 생성일시

### Book (동화책)
- id: 고유 ID
- title: 제목
- description: 설명
- author_id: 작가 ID
- price: 가격
- cover_image: 표지 이미지 URL
- content: 동화 내용
- is_published: 공개 여부
- views: 조회수
- created_at: 생성일시
- updated_at: 수정일시

### Payment (결제)
- id: 고유 ID
- user_id: 독자 ID
- book_id: 동화책 ID
- amount: 결제 금액
- platform_fee: 플랫폼 수수료
- author_revenue: 작가 수익
- payment_method: 결제 수단
- payment_status: 결제 상태
- created_at: 결제일시

### Settlement (정산)
- id: 고유 ID
- author_id: 작가 ID
- amount: 정산 금액
- settlement_date: 정산 요청일
- status: 정산 상태 (pending/completed/failed)
- bank_account: 계좌 정보
- notes: 메모

## 수익 배분 구조

- **플랫폼 수수료**: 30%
- **작가 수익**: 70%

예시: 10,000원 동화책 판매 시
- 플랫폼: 3,000원
- 작가: 7,000원

## 보안

- JWT 기반 인증 시스템
- 비밀번호 bcrypt 해싱
- Bearer 토큰 인증
- 작가/독자 권한 구분

## 주의사항

1. **SECRET_KEY 변경**: `backend/auth.py`의 `SECRET_KEY`를 운영 환경에서는 반드시 변경해야 합니다.
2. **데이터베이스**: 현재 SQLite를 사용하고 있으며, 운영 환경에서는 PostgreSQL 등으로 변경을 권장합니다.
3. **결제 시스템**: 현재는 간단한 결제 시뮬레이션이며, 실제 운영 시 PG사 연동이 필요합니다.
4. **이미지 업로드**: 현재는 이미지 URL만 저장하며, 실제 파일 업로드 기능은 추가 구현이 필요합니다.

## 개발 계획

- [ ] 실제 결제 시스템 연동 (PG사)
- [ ] 이미지 업로드 기능
- [ ] 동화책 카테고리 및 검색 기능
- [ ] 리뷰 및 평점 시스템
- [ ] 관리자 페이지
- [ ] 이메일 인증
- [ ] 소셜 로그인

## 라이선스

MIT License

## 문의

프로젝트 관련 문의사항이 있으시면 이슈를 등록해주세요.
