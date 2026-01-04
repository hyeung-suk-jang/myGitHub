# Study Cafe Homepage V3 🎓

프리미엄 스터디 카페 홈페이지 세 번째 버전입니다.

## ✨ V3 주요 기능

### 🌟 새로운 기능
- **다크 모드 지원** - 사용자 취향에 맞는 테마 선택
- **향상된 UI/UX** - 현대적이고 세련된 디자인
- **후기 섹션** - 실제 이용자 후기 확인
- **FAQ 섹션** - 자주 묻는 질문 아코디언 형식
- **개선된 갤러리** - 인터랙티브 이미지 갤러리
- **알림 시스템** - 사용자 친화적인 알림 메시지
- **스크롤 진행 바** - 페이지 읽기 진행도 표시
- **채팅 버튼** - 실시간 상담 지원 (준비 중)

### 🔄 기존 기능 개선
- **실시간 좌석 현황** - 30초마다 자동 갱신
- **온라인 예약 시스템** - 모달 기반 예약 인터페이스
- **반응형 디자인** - 모든 기기에서 최적화
- **부드러운 애니메이션** - 스크롤 기반 애니메이션
- **문의 폼** - 개선된 문의 제출 시스템

## 🛠 기술 스택

### Frontend
- HTML5
- CSS3 (CSS Variables for theming)
- JavaScript (ES6+)
- jQuery 3.7.1
- Font Awesome 6.5.1
- Google Fonts (Noto Sans KR)

### Backend
- Python 3.11+
- FastAPI 0.109.0
- Uvicorn (ASGI Server)
- Pydantic (Data Validation)

## 📦 설치 방법

### 1. 저장소 클론
```bash
git clone <repository-url>
cd study-cafe-homepage-v3
```

### 2. 가상환경 생성 및 활성화
```bash
python -m venv venv
source venv/bin/activate  # Linux/Mac
# venv\Scripts\activate  # Windows
```

### 3. 의존성 설치
```bash
pip install -r requirements.txt
```

### 4. 서버 실행
```bash
python main.py
```

### 5. 브라우저에서 접속
```
http://localhost:8000
```

## 📁 프로젝트 구조

```
study-cafe-homepage-v3/
├── app/
│   ├── api/
│   │   ├── __init__.py
│   │   ├── seats.py
│   │   ├── reservations.py
│   │   └── contact.py
│   ├── models/
│   │   ├── __init__.py
│   │   └── seat.py
│   └── __init__.py
├── static/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── templates/
│   └── index.html
├── main.py
├── requirements.txt
├── .gitignore
└── README.md
```

## 🎨 디자인 특징

### 색상 팔레트
- **Primary**: #6366f1 (Indigo)
- **Success**: #10b981 (Green)
- **Warning**: #f59e0b (Amber)
- **Error**: #ef4444 (Red)

### 그라데이션
- Blue: #667eea → #764ba2
- Purple: #f093fb → #f5576c
- Green: #4facfe → #00f2fe
- Orange: #fa709a → #fee140

## 🔌 API 엔드포인트

- `GET /api/seats` - 모든 좌석 조회
- `GET /api/seats/statistics` - 좌석 통계
- `POST /api/reservations` - 예약 생성
- `POST /api/contact` - 문의 제출
- `GET /health` - 헬스 체크
- `GET /api/docs` - API 문서

## 🌙 다크 모드

다크 모드는 CSS Variables를 사용하여 구현되었으며, localStorage에 저장됩니다.

## 📱 반응형 브레이크포인트

- Desktop: 1024px+
- Tablet: 768px - 1023px
- Mobile: ~767px

## 📝 라이센스

MIT License

---

Made with ❤️ by Study Cafe Team
