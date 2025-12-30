"""
Main Application Entry Point
애플리케이션 진입점 - PHP의 index.php와 유사
"""

from fastapi import FastAPI
from fastapi.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
from config.settings import settings
from core.database import db
from routes import web_router, api_router
import uvicorn


# FastAPI 앱 생성
app = FastAPI(
    title=settings.APP_NAME,
    debug=settings.APP_DEBUG,
    docs_url="/api/docs",
    redoc_url="/api/redoc",
)

# CORS 미들웨어 설정
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=settings.CORS_ALLOW_CREDENTIALS,
    allow_methods=settings.CORS_ALLOW_METHODS,
    allow_headers=settings.CORS_ALLOW_HEADERS,
)

# 정적 파일 서빙
app.mount("/static", StaticFiles(directory="public"), name="static")
app.mount("/uploads", StaticFiles(directory="uploads"), name="uploads")

# 라우트 등록
app.include_router(web_router)
app.include_router(api_router)


@app.on_event("startup")
async def startup_event():
    """애플리케이션 시작 이벤트"""
    print("=" * 50)
    print(f"Starting {settings.APP_NAME}")
    print(f"Environment: {settings.APP_ENV}")
    print(f"Debug Mode: {settings.APP_DEBUG}")
    print("=" * 50)

    # 데이터베이스 테이블 생성
    try:
        db.create_tables()
        print("✓ Database tables created successfully")
    except Exception as e:
        print(f"✗ Database initialization failed: {e}")


@app.on_event("shutdown")
async def shutdown_event():
    """애플리케이션 종료 이벤트"""
    print("=" * 50)
    print(f"Shutting down {settings.APP_NAME}")
    print("=" * 50)


if __name__ == "__main__":
    # 개발 서버 실행 (PHP의 php -S와 유사)
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=8000,
        reload=settings.APP_DEBUG,
        log_level="info"
    )
