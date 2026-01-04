# Study Cafe Homepage V2

프리미엄 스터디 카페 홈페이지 - 실시간 좌석 현황 및 온라인 예약 시스템

## 주요 기능

### V2 새로운 기능
- ✨ **실시간 좌석 현황**: 실시간으로 좌석 이용 가능 여부 확인
- 📅 **온라인 예약 시스템**: 원하는 좌석을 미리 예약
- 🏢 **컨셉 섹션**: 스터디 카페의 철학과 컨셉 소개
- 🖼️ **갤러리**: 카페 내부 공간 미리보기
- 📊 **좌석 통계**: 전체 좌석 이용률 및 통계 제공
- 🔄 **자동 갱신**: 30초마다 좌석 현황 자동 업데이트

### 기본 기능
- 🎨 **모던한 디자인**: 그라디언트와 애니메이션을 활용한 현대적인 UI
- 📱 **반응형 웹**: 모바일, 태블릿, 데스크톱 모든 기기 지원
- 🏪 **시설 안내**: 고정석, 자유석, 스터디룸 상세 정보
- 💰 **이용 요금**: 시간권 및 정기권 요금제
- 📍 **위치 안내**: 오시는 길 및 연락처 정보
- 📧 **문의하기**: 온라인 문의 폼

## 기술 스택

### Backend
- **FastAPI** - 고성능 Python 웹 프레임워크
- **Pydantic** - 데이터 유효성 검증
- **Uvicorn** - ASGI 서버

### Frontend
- **HTML5** - 시맨틱 마크업
- **CSS3** - 그라디언트, 애니메이션, Flexbox, Grid
- **JavaScript (jQuery)** - 인터랙션 및 API 연동

## 설치 및 실행

### 1. 가상환경 생성 및 활성화

```bash
python -m venv venv

# Windows
venv\Scripts\activate

# Linux/Mac
source venv/bin/activate
```

### 2. 의존성 설치

```bash
pip install -r requirements.txt
```

### 3. 서버 실행

```bash
python main.py
```

또는

```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### 4. 브라우저에서 접속

- **홈페이지**: http://localhost:8000
- **API 문서**: http://localhost:8000/api/docs
- **ReDoc**: http://localhost:8000/api/redoc

## API 엔드포인트

### 좌석 관리

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/seats` | 좌석 목록 조회 (필터링 가능) |
| GET | `/api/seats/{seat_id}` | 특정 좌석 상세 조회 |
| GET | `/api/seats/statistics` | 좌석 통계 조회 |
| PATCH | `/api/seats/{seat_id}/status` | 좌석 상태 업데이트 |

### 예약 관리

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/reservations` | 예약 생성 |
| GET | `/api/reservations/{reservation_id}` | 예약 상세 조회 |
| DELETE | `/api/reservations/{reservation_id}` | 예약 취소 |
| GET | `/api/reservations/seat/{seat_id}` | 좌석별 예약 목록 조회 |

### 문의

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/contact` | 문의 메시지 생성 |
| GET | `/api/contact` | 문의 목록 조회 (관리자) |

## 프로젝트 구조

```
study-cafe-homepage-v2/
├── app/
│   ├── __init__.py
│   ├── database.py              # 인메모리 데이터베이스
│   ├── models/
│   │   ├── __init__.py
│   │   └── seat.py              # 데이터 모델
│   └── api/
│       ├── __init__.py
│       ├── contact.py           # 문의 API
│       ├── seats.py             # 좌석 API
│       └── reservations.py      # 예약 API
├── static/
│   ├── css/
│   │   └── style.css           # 스타일시트
│   ├── js/
│   │   └── main.js             # JavaScript
│   └── images/                 # 이미지 파일
├── templates/
│   └── index.html              # 메인 HTML
├── main.py                     # FastAPI 앱
├── requirements.txt            # Python 의존성
├── .gitignore
└── README.md
```

## 좌석 타입

- **고정석 (fixed)**: 개인 전용 좌석, 수납함 제공
- **자유석 (free)**: 자유롭게 이용 가능한 좌석
- **스터디룸 (study_room)**: 그룹 스터디를 위한 전용 공간

## 좌석 상태

- **이용가능 (available)**: 예약 및 이용 가능
- **사용중 (occupied)**: 현재 사용 중
- **예약됨 (reserved)**: 예약된 좌석
- **정비중 (maintenance)**: 정비 중

## 개발 노트

### V1 대비 개선사항

1. **API 기반 아키텍처**: RESTful API 구조로 확장성 향상
2. **실시간 기능**: 좌석 현황 실시간 조회 및 자동 갱신
3. **예약 시스템**: 온라인 예약 기능 추가
4. **향상된 UX**: 더 직관적이고 현대적인 사용자 경험
5. **데이터 검증**: Pydantic을 통한 강력한 데이터 유효성 검증

### 향후 개선 계획

- [ ] 실제 데이터베이스 연동 (PostgreSQL, MySQL)
- [ ] 사용자 인증 및 회원가입
- [ ] 결제 시스템 통합
- [ ] 관리자 대시보드
- [ ] 푸시 알림
- [ ] 카카오톡 알림톡 연동
- [ ] 이미지 갤러리 실제 사진 업로드
- [ ] 지도 API 연동 (카카오맵, 네이버맵)

## 라이선스

이 프로젝트는 교육 및 포트폴리오 목적으로 제작되었습니다.

## 문의

- 이메일: info@studycafe.com
- 전화: 02-1234-5678
- 주소: 서울특별시 강남구 테헤란로 123

---

© 2024 Study Cafe V2. All rights reserved.
