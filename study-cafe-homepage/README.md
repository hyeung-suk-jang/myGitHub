# Study Cafe Homepage - 10가지 디자인 버전

프리미엄 스터디 카페 홈페이지 - **10가지 완전히 다른 모던 디자인**

## 🎨 디자인 버전 (10종)

### V1 - 그라디언트 테마 (기본)
- **포트**: 8000
- **실행**: `python main.py`
- **특징**: 보라색 그라디언트, 부드러운 애니메이션, 카드 레이아웃
- **스타일**: 모던, 프로페셔널

### V2 - 다크 모드 ⚫
- **포트**: 8002
- **실행**: `python main2.py`
- **특징**: 검정/보라 조합, 별빛 파티클, 네온 글로우
- **스타일**: 세련됨, 프리미엄, 야간 최적화

### V3 - 미니멀리즘 ⚪
- **포트**: 8003
- **실행**: `python main3.py`
- **특징**: 흰색 배경, Helvetica, 넓은 여백
- **스타일**: 깔끔함, 단순함, 우아함

### V4 - 네온 사이버펑크 🌈
- **포트**: 8004
- **실행**: `python main4.py`
- **특징**: 형광색(블루/핑크/그린), 그리드 배경, 깜빡임 효과
- **스타일**: 미래지향적, 게이밍, 에너제틱

### V5 - Glassmorphism 🔮
- **포트**: 8005
- **실행**: `python main5.py`
- **특징**: 반투명 유리 효과, Backdrop blur, 밝은 그라디언트
- **스타일**: 투명함, 현대적, iOS 스타일

### V6 - 비디오 배경 🎬
- **포트**: 8006
- **실행**: `python main6.py`
- **특징**: 풀스크린 컨셉, 강한 오버레이, 핑크 액센트
- **스타일**: 다이나믹, 임팩트, 스토리텔링

### V7 - 분할 스크린 ⬛⬜
- **포트**: 8007
- **실행**: `python main7.py`
- **특징**: 좌우 대칭, 검정/노랑 대비, 대담한 보더
- **스타일**: 강렬함, 균형감, 브루탈리즘

### V8 - 3D 인터랙티브 🎪
- **포트**: 8008
- **실행**: `python main8.py`
- **특징**: 3D transform, 패럴랙스, 카드 회전, 깊이감
- **스타일**: 입체감, 인터랙티브, 몰입감

### V9 - 캐러셀/카드 🎴
- **포트**: 8009
- **실행**: `python main9.py`
- **특징**: 화려한 카드, 둥근 모서리, 파스텔 배경, 회전 효과
- **스타일**: 밝음, 친근함, 모던 UI

### V10 - 타이포그래피 📝
- **포트**: 8010
- **실행**: `python main10.py`
- **특징**: Playfair Display, 거대한 텍스트, 텍스트 중심
- **스타일**: 우아함, 편집 디자인, 매거진

## 기술 스택

- **Frontend**: HTML5, CSS3, JavaScript, jQuery
- **Backend**: Python, FastAPI
- **Template Engine**: Jinja2

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

### 3. 원하는 버전 실행

```bash
# V1 - 기본 (포트 8000)
python main.py

# V2 - 다크 모드 (포트 8002)
python main2.py

# V3 - 미니멀리즘 (포트 8003)
python main3.py

# V4 - 사이버펑크 (포트 8004)
python main4.py

# V5 - Glassmorphism (포트 8005)
python main5.py

# V6 - 비디오 배경 (포트 8006)
python main6.py

# V7 - 분할 스크린 (포트 8007)
python main7.py

# V8 - 3D 인터랙티브 (포트 8008)
python main8.py

# V9 - 캐러셀 (포트 8009)
python main9.py

# V10 - 타이포그래피 (포트 8010)
python main10.py
```

### 4. 브라우저에서 접속

- **V1**: http://localhost:8000
- **V2**: http://localhost:8002
- **V3**: http://localhost:8003
- **V4**: http://localhost:8004
- **V5**: http://localhost:8005
- **V6**: http://localhost:8006
- **V7**: http://localhost:8007
- **V8**: http://localhost:8008
- **V9**: http://localhost:8009
- **V10**: http://localhost:8010

각 버전의 API 문서는 해당 포트의 `/api/docs`에서 확인할 수 있습니다.

## 프로젝트 구조

```
study-cafe-homepage/
├── app/
│   ├── __init__.py
│   └── api/__init__.py          # API 라우트 (문의 폼, 헬스 체크)
├── static/
│   ├── css/
│   │   ├── style.css           # V1 스타일
│   │   ├── style2.css          # V2 다크 모드
│   │   ├── style3.css          # V3 미니멀
│   │   ├── style4.css          # V4 사이버펑크
│   │   ├── style5.css          # V5 Glassmorphism
│   │   ├── style6.css          # V6 비디오
│   │   ├── style7.css          # V7 분할 스크린
│   │   ├── style8.css          # V8 3D
│   │   ├── style9.css          # V9 캐러셀
│   │   └── style10.css         # V10 타이포그래피
│   ├── js/
│   │   └── main.js             # 공통 JavaScript
│   └── images/                 # 이미지 폴더
├── templates/
│   ├── index.html              # V1 메인 페이지
│   ├── index2.html             # V2 다크 모드
│   ├── index3.html             # V3 미니멀
│   ├── index4.html             # V4 사이버펑크
│   ├── index5.html             # V5 Glassmorphism
│   ├── index6.html             # V6 비디오
│   ├── index7.html             # V7 분할 스크린
│   ├── index8.html             # V8 3D
│   ├── index9.html             # V9 캐러셀
│   └── index10.html            # V10 타이포그래피
├── main.py                     # V1 FastAPI 앱
├── main2.py                    # V2 FastAPI 앱
├── main3.py                    # V3 FastAPI 앱
├── main4.py                    # V4 FastAPI 앱
├── main5.py                    # V5 FastAPI 앱
├── main6.py                    # V6 FastAPI 앱
├── main7.py                    # V7 FastAPI 앱
├── main8.py                    # V8 FastAPI 앱
├── main9.py                    # V9 FastAPI 앱
├── main10.py                   # V10 FastAPI 앱
├── requirements.txt            # Python 의존성
├── .gitignore
└── README.md
```

## 주요 페이지 섹션

1. **Hero Section**: 메인 비주얼과 통계
2. **Features**: 스터디 카페 특징 (Wi-Fi, 음료, 보안 등)
3. **Facilities**: 시설 안내 (고정석, 자유석, 스터디룸)
4. **Pricing**: 요금제 안내 (시간권, 정기권)
5. **Location**: 오시는 길
6. **Contact**: 문의 폼

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

## 디자인 선택 가이드

### 비즈니스 타입별 추천

**프리미엄 스터디 카페**
- ✅ V2 (다크 모드) - 세련되고 고급스러움
- ✅ V5 (Glassmorphism) - 현대적이고 트렌디
- ✅ V10 (타이포그래피) - 우아하고 품격있음

**젊은층 타겟**
- ✅ V4 (사이버펑크) - 에너제틱하고 독특
- ✅ V8 (3D 인터랙티브) - 재미있고 몰입감
- ✅ V9 (캐러셀) - 밝고 친근함

**전문적/비즈니스**
- ✅ V3 (미니멀리즘) - 깔끔하고 프로페셔널
- ✅ V7 (분할 스크린) - 강렬하고 임팩트
- ✅ V6 (비디오 배경) - 스토리텔링 중심

**범용/일반**
- ✅ V1 (기본) - 무난하고 모던함

## 커스터마이징

### 색상 변경
각 버전의 CSS 파일에서 `:root` 변수를 수정하세요:

```css
:root {
    --primary-color: #4f46e5;
    --secondary-color: #10b981;
    /* ... */
}
```

### 내용 수정
- 텍스트: `templates/indexN.html` 수정
- 스타일: `static/css/styleN.css` 수정
- 기능: `static/js/main.js` 수정

## 라이선스

MIT License

## 문의

프로젝트 관련 문의사항이 있으시면 Issues를 통해 연락해주세요.
