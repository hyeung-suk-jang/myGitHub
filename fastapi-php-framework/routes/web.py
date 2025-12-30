"""
Web Routes
웹 페이지 라우트 - PHP의 web.php와 유사
"""

from fastapi import APIRouter, Request, Depends
from app.controllers import HomeController
from core.database import get_db
from sqlalchemy.orm import Session

web_router = APIRouter()


@web_router.get("/")
async def index(request: Request, db: Session = Depends(get_db)):
    """메인 페이지"""
    controller = HomeController()
    controller.set_request(request).set_db(db)
    return controller.index()


@web_router.get("/about")
async def about(request: Request, db: Session = Depends(get_db)):
    """소개 페이지"""
    controller = HomeController()
    controller.set_request(request).set_db(db)
    return controller.about()


@web_router.get("/contact")
async def contact(request: Request, db: Session = Depends(get_db)):
    """연락처 페이지"""
    controller = HomeController()
    controller.set_request(request).set_db(db)
    return controller.contact()


@web_router.get("/health")
async def health(request: Request, db: Session = Depends(get_db)):
    """헬스체크"""
    controller = HomeController()
    controller.set_request(request).set_db(db)
    return controller.health()
