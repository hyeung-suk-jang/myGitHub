"""
Study Cafe Homepage V3 - FastAPI Application
스터디 카페 홈페이지 V3 메인 애플리케이션
Enhanced with modern features and improved architecture
"""

from fastapi import FastAPI, Request
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware
from app.api import router as api_router
import uvicorn

# FastAPI 앱 생성
app = FastAPI(
    title="Study Cafe Homepage V3",
    description="프리미엄 스터디 카페 홈페이지 V3 - 실시간 좌석 현황, 온라인 예약, 다크모드 지원",
    version="3.0.0",
    docs_url="/api/docs",
    redoc_url="/api/redoc",
)

# CORS 미들웨어 설정
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# 정적 파일 서빙
app.mount("/static", StaticFiles(directory="static"), name="static")

# 템플릿 설정
templates = Jinja2Templates(directory="templates")

# API 라우터 등록
app.include_router(api_router, prefix="/api")


@app.get("/")
async def home(request: Request):
    """홈페이지 메인 페이지"""
    return templates.TemplateResponse("index.html", {"request": request})


@app.get("/health")
async def health_check():
    """헬스 체크 엔드포인트"""
    return {
        "status": "healthy",
        "version": "3.0.0",
        "service": "Study Cafe Homepage V3",
        "features": [
            "Real-time seat tracking",
            "Online reservation system",
            "Dark mode support",
            "Enhanced UI/UX",
            "Mobile responsive"
        ]
    }


@app.on_event("startup")
async def startup_event():
    """애플리케이션 시작 이벤트"""
    print("=" * 80)
    print("🚀 Study Cafe Homepage V3 Starting...")
    print("=" * 80)
    print("📍 Server: http://localhost:8000")
    print("📚 API Docs: http://localhost:8000/api/docs")
    print("🏠 Homepage: http://localhost:8000")
    print("=" * 80)
    print("✨ V3 Features:")
    print("   ✓ Dark Mode Support")
    print("   ✓ Real-time Seat Tracking")
    print("   ✓ Online Reservation System")
    print("   ✓ Enhanced Animations")
    print("   ✓ Mobile Responsive Design")
    print("   ✓ FAQ Section")
    print("   ✓ Review System")
    print("   ✓ Interactive Gallery")
    print("=" * 80)


@app.on_event("shutdown")
async def shutdown_event():
    """애플리케이션 종료 이벤트"""
    print("=" * 80)
    print("👋 Study Cafe Homepage V3 Shutting Down...")
    print("=" * 80)


if __name__ == "__main__":
    # 개발 서버 실행
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=8000,
        reload=True,
        log_level="info"
    )
