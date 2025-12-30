# FastAPI PHP Framework

PHP 프레임워크(Laravel, CodeIgniter 등)의 익숙한 구조를 FastAPI로 구현한 웹 애플리케이션 프레임워크입니다.

## 특징

- 🚀 **PHP 스타일 구조**: Laravel, CodeIgniter와 유사한 디렉토리 구조
- ⚡ **FastAPI 성능**: 비동기 처리와 높은 성능
- 📝 **MVC 패턴**: Model-View-Controller 아키텍처
- 🔒 **타입 안전성**: Pydantic 스키마를 통한 데이터 검증
- 🗄️ **ORM 지원**: SQLAlchemy를 통한 데이터베이스 관리
- 🎨 **템플릿 엔진**: Jinja2를 통한 동적 페이지 렌더링

## 디렉토리 구조

```
fastapi-php-framework/
├── app/                      # 애플리케이션 코드
│   ├── controllers/          # 컨트롤러 (요청 처리)
│   ├── models/              # 데이터베이스 모델
│   ├── schemas/             # Pydantic 스키마 (데이터 검증)
│   └── views/               # 템플릿 파일
│       └── templates/
├── config/                  # 설정 파일
│   ├── settings.py         # 애플리케이션 설정
│   └── database.py         # 데이터베이스 설정
├── core/                    # 프레임워크 핵심
│   ├── controller.py       # 베이스 컨트롤러
│   ├── database.py         # 데이터베이스 관리
│   ├── router.py           # 라우터
│   └── response.py         # 응답 헬퍼
├── database/               # 데이터베이스 파일
│   └── migrations/         # 마이그레이션
├── public/                 # 정적 파일
│   ├── css/
│   ├── js/
│   └── images/
├── routes/                 # 라우트 정의
│   ├── web.py             # 웹 라우트
│   └── api.py             # API 라우트
├── uploads/                # 업로드 파일
├── main.py                 # 애플리케이션 진입점
├── requirements.txt        # Python 패키지
└── .env.example           # 환경 변수 예제
```

## 설치 및 실행

### 1. 가상환경 생성

```bash
python -m venv venv
source venv/bin/activate  # Linux/Mac
# venv\Scripts\activate  # Windows
```

### 2. 패키지 설치

```bash
pip install -r requirements.txt
```

### 3. 환경 변수 설정

```bash
cp .env.example .env
# .env 파일을 편집하여 필요한 설정을 변경하세요
```

### 4. 애플리케이션 실행

```bash
python main.py
```

또는 uvicorn으로 직접 실행:

```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### 5. 브라우저 접속

- 메인 페이지: http://localhost:8000
- API 문서: http://localhost:8000/api/docs
- ReDoc: http://localhost:8000/api/redoc

## 사용 방법

### 컨트롤러 생성

```python
# app/controllers/example_controller.py
from core.controller import BaseController

class ExampleController(BaseController):
    def index(self):
        """목록 조회"""
        return self.success({"message": "Hello World"})

    def show(self, id: int):
        """상세 조회"""
        return self.success({"id": id})
```

### 모델 생성

```python
# app/models/example.py
from sqlalchemy import Column, Integer, String
from core.database import Base

class Example(Base):
    __tablename__ = "examples"

    id = Column(Integer, primary_key=True)
    name = Column(String(100))

    def to_dict(self):
        return {"id": self.id, "name": self.name}
```

### 스키마 생성

```python
# app/schemas/example_schema.py
from pydantic import BaseModel

class ExampleCreate(BaseModel):
    name: str

class ExampleResponse(BaseModel):
    id: int
    name: str

    class Config:
        from_attributes = True
```

### 라우트 등록

```python
# routes/api.py
from fastapi import APIRouter, Request, Depends
from app.controllers import ExampleController
from core.database import get_db
from sqlalchemy.orm import Session

api_router = APIRouter(prefix="/api")

@api_router.get("/examples")
async def get_examples(request: Request, db: Session = Depends(get_db)):
    controller = ExampleController()
    controller.set_request(request).set_db(db)
    return controller.index()
```

## API 엔드포인트

### 사용자 관리

- `GET /api/users` - 사용자 목록 조회
- `GET /api/users/{id}` - 사용자 상세 조회
- `POST /api/users` - 사용자 생성
- `PUT /api/users/{id}` - 사용자 수정
- `DELETE /api/users/{id}` - 사용자 삭제

### 게시글 관리

- `GET /api/posts` - 게시글 목록 조회
- `GET /api/posts/{id}` - 게시글 상세 조회
- `POST /api/posts` - 게시글 생성
- `PUT /api/posts/{id}` - 게시글 수정
- `DELETE /api/posts/{id}` - 게시글 삭제

## 데이터베이스 설정

### SQLite (기본)

```env
DATABASE_URL=sqlite:///./database/app.db
```

### PostgreSQL

```env
DATABASE_URL=postgresql://user:password@localhost/dbname
```

### MySQL

```env
DATABASE_URL=mysql+pymysql://user:password@localhost/dbname
```

## 주요 기능

### 1. BaseController

PHP 컨트롤러와 유사한 베이스 컨트롤러:

```python
class MyController(BaseController):
    def my_action(self):
        # JSON 응답
        return self.success(data, "Success message")

        # 에러 응답
        return self.error("Error message", 400)

        # 템플릿 렌더링
        return self.view("template.html", {"key": "value"})

        # 리다이렉트
        return self.redirect("/path")
```

### 2. 데이터 검증

Pydantic 스키마를 통한 자동 데이터 검증:

```python
class UserCreate(BaseModel):
    name: str = Field(..., min_length=2, max_length=100)
    email: EmailStr
    password: str = Field(..., min_length=6)
```

### 3. ORM

SQLAlchemy를 통한 데이터베이스 작업:

```python
# 조회
users = self.db.query(User).all()

# 생성
user = User(name="John", email="john@example.com")
self.db.add(user)
self.db.commit()

# 수정
user.name = "Jane"
self.db.commit()

# 삭제
self.db.delete(user)
self.db.commit()
```

### 4. 템플릿 렌더링

Jinja2 템플릿 엔진:

```python
return self.view("home/index.html", {
    "title": "Welcome",
    "message": "Hello World"
})
```

## 개발 팁

### 1. 자동 리로드

개발 중에는 `--reload` 옵션을 사용하여 코드 변경 시 자동으로 서버를 재시작합니다:

```bash
uvicorn main:app --reload
```

### 2. 디버그 모드

`.env` 파일에서 디버그 모드 활성화:

```env
APP_DEBUG=True
DB_ECHO=True
```

### 3. API 문서

FastAPI는 자동으로 API 문서를 생성합니다:
- Swagger UI: `/api/docs`
- ReDoc: `/api/redoc`

## 배포

### 프로덕션 서버 실행

```bash
uvicorn main:app --host 0.0.0.0 --port 8000 --workers 4
```

### Gunicorn 사용

```bash
gunicorn main:app -w 4 -k uvicorn.workers.UvicornWorker --bind 0.0.0.0:8000
```

## 라이선스

MIT License

## 기여

이슈와 PR을 환영합니다!

## 참고

이 프레임워크는 학습 및 개발 목적으로 만들어졌습니다.
PHP 개발자가 FastAPI를 쉽게 배우고 사용할 수 있도록 설계되었습니다.
