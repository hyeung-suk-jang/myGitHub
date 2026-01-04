# Study Cafe Homepage

프리미엄 스터디 카페 홈페이지 - 모던한 디자인과 FastAPI 백엔드

## 기술 스택

- **Frontend**: HTML5, CSS3, JavaScript, jQuery
- **Backend**: Python, FastAPI
- **Template Engine**: Jinja2

## 주요 기능

### 프론트엔드
- ✨ 모던하고 반응형 디자인
- 🎨 그라디언트와 부드러운 애니메이션
- 📱 모바일 최적화
- 🎯 부드러운 스크롤 및 네비게이션
- 💳 시간권/정기권 요금제 전환
- 📧 문의 폼

### 백엔드
- ⚡ FastAPI 고성능 서버
- 📝 문의 폼 API
- 🔍 API 문서 자동 생성 (Swagger/ReDoc)
- 🎯 RESTful API 설계

## 프로젝트 구조

```
study-cafe-homepage/
├── app/
│   ├── __init__.py
│   └── api/
│       └── __init__.py          # API 라우트
├── static/
│   ├── css/
│   │   └── style.css           # 메인 스타일시트
│   ├── js/
│   │   └── main.js             # 메인 JavaScript
│   └── images/                 # 이미지 파일
├── templates/
│   └── index.html              # 메인 페이지
├── main.py                     # FastAPI 애플리케이션
├── requirements.txt            # Python 의존성
└── README.md                   # 프로젝트 문서
```

## 설치 및 실행

### 1. 가상환경 생성 (권장)

```bash
python -m venv venv

# Windows
venv\Scripts\activate

# Mac/Linux
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

### 문의 폼 제출
```
POST /api/contact
Content-Type: application/json

{
  "name": "홍길동",
  "phone": "010-1234-5678",
  "email": "user@example.com",
  "subject": "문의 제목",
  "message": "문의 내용"
}
```

### 문의 목록 조회 (관리자용)
```
GET /api/contacts
```

### 헬스 체크
```
GET /api/health
```

## 주요 페이지 섹션

1. **Hero Section**: 메인 비주얼과 통계
2. **Features**: 스터디 카페 특징 (Wi-Fi, 음료, 보안 등)
3. **Facilities**: 시설 안내 (고정석, 자유석, 스터디룸)
4. **Pricing**: 요금제 안내 (시간권, 정기권)
5. **Location**: 오시는 길
6. **Contact**: 문의 폼

## 디자인 특징

- 🎨 모던한 그라디언트 디자인
- 💫 부드러운 스크롤 애니메이션
- 📊 카드 기반 레이아웃
- 🌈 일관된 컬러 스킴
- 📱 완전한 반응형 디자인

## 커스터마이징

### 색상 변경
`static/css/style.css` 파일의 CSS 변수를 수정하세요:

```css
:root {
    --primary-color: #4f46e5;
    --secondary-color: #10b981;
    --accent-color: #f59e0b;
    /* ... */
}
```

### 내용 수정
- 텍스트: `templates/index.html` 수정
- 스타일: `static/css/style.css` 수정
- 기능: `static/js/main.js` 수정

## 개발 모드

개발 모드에서는 코드 변경 시 자동으로 서버가 재시작됩니다:

```bash
uvicorn main:app --reload
```

## 프로덕션 배포

### 1. Gunicorn 사용 (Linux/Mac)

```bash
pip install gunicorn
gunicorn -w 4 -k uvicorn.workers.UvicornWorker main:app
```

### 2. Docker 사용

```dockerfile
FROM python:3.11-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
```

## 향후 개발 계획

- [ ] 데이터베이스 연동 (SQLite/MySQL/PostgreSQL)
- [ ] 회원가입/로그인 기능
- [ ] 좌석 예약 시스템
- [ ] 온라인 결제 연동
- [ ] 관리자 대시보드
- [ ] 이메일 알림 기능

## 라이선스

MIT License

## 문의

프로젝트 관련 문의사항이 있으시면 Issues를 통해 연락해주세요.
