"""
API Routes
API 라우트 - 스터디 카페 키오스크
"""

from fastapi import APIRouter, Request, Depends
from app.controllers import (
    UserController, PostController,
    AuthController, SeatController, TicketController,
    SessionController, AdminController, HistoryController
)
from app.schemas import (
    UserCreate, UserUpdate, PostCreate, PostUpdate,
    UserLogin, UserRegister, SessionCreate
)
from core.database import get_db
from sqlalchemy.orm import Session

api_router = APIRouter(prefix="/api")


# ==================== Study Cafe Kiosk API ====================

# Auth Routes
@api_router.post("/auth/login", tags=["인증"])
async def login(
    data: UserLogin,
    request: Request,
    db: Session = Depends(get_db)
):
    """로그인"""
    controller = AuthController()
    controller.set_request(request).set_db(db)
    return controller.login(data)


@api_router.post("/auth/register", tags=["인증"])
async def register(
    data: UserRegister,
    request: Request,
    db: Session = Depends(get_db)
):
    """회원가입"""
    controller = AuthController()
    controller.set_request(request).set_db(db)
    return controller.register(data)


@api_router.post("/auth/logout", tags=["인증"])
async def logout(
    request: Request,
    db: Session = Depends(get_db)
):
    """로그아웃"""
    controller = AuthController()
    controller.set_request(request).set_db(db)
    return controller.logout()


@api_router.get("/auth/me/{user_id}", tags=["인증"])
async def get_me(
    user_id: str,
    request: Request,
    db: Session = Depends(get_db)
):
    """현재 사용자 정보"""
    controller = AuthController()
    controller.set_request(request).set_db(db)
    return controller.me(user_id)


# Seat Routes
@api_router.get("/seats", tags=["좌석"])
async def get_seats(
    request: Request,
    floor: int = None,
    db: Session = Depends(get_db)
):
    """좌석 목록 조회"""
    controller = SeatController()
    controller.set_request(request).set_db(db)
    return controller.index(floor)


@api_router.get("/seats/available", tags=["좌석"])
async def get_available_seats(
    request: Request,
    db: Session = Depends(get_db)
):
    """이용 가능한 좌석 조회"""
    controller = SeatController()
    controller.set_request(request).set_db(db)
    return controller.available_seats()


@api_router.get("/seats/{seat_id}", tags=["좌석"])
async def get_seat(
    seat_id: str,
    request: Request,
    db: Session = Depends(get_db)
):
    """좌석 상세 조회"""
    controller = SeatController()
    controller.set_request(request).set_db(db)
    return controller.show(seat_id)


# Ticket Routes
@api_router.get("/tickets", tags=["이용권"])
async def get_tickets(
    request: Request,
    ticket_type: str = None,
    db: Session = Depends(get_db)
):
    """이용권 목록 조회"""
    controller = TicketController()
    controller.set_request(request).set_db(db)
    return controller.index(ticket_type)


@api_router.get("/tickets/{ticket_id}", tags=["이용권"])
async def get_ticket(
    ticket_id: str,
    request: Request,
    db: Session = Depends(get_db)
):
    """이용권 상세 조회"""
    controller = TicketController()
    controller.set_request(request).set_db(db)
    return controller.show(ticket_id)


# Session Routes
@api_router.post("/sessions/start", tags=["세션"])
async def start_session(
    data: SessionCreate,
    request: Request,
    db: Session = Depends(get_db)
):
    """세션 시작 (입실)"""
    controller = SessionController()
    controller.set_request(request).set_db(db)
    return controller.start(data)


@api_router.post("/sessions/{session_id}/end", tags=["세션"])
async def end_session(
    session_id: str,
    request: Request,
    db: Session = Depends(get_db)
):
    """세션 종료 (퇴실)"""
    controller = SessionController()
    controller.set_request(request).set_db(db)
    return controller.end(session_id)


@api_router.get("/sessions/active/{user_id}", tags=["세션"])
async def get_active_session(
    user_id: str,
    request: Request,
    db: Session = Depends(get_db)
):
    """사용자 활성 세션 조회"""
    controller = SessionController()
    controller.set_request(request).set_db(db)
    return controller.get_active_session(user_id)


@api_router.get("/sessions/all-active", tags=["세션"])
async def get_all_active_sessions(
    request: Request,
    db: Session = Depends(get_db)
):
    """모든 활성 세션 조회 (관리자)"""
    controller = SessionController()
    controller.set_request(request).set_db(db)
    return controller.get_all_active_sessions()


# History Routes
@api_router.get("/history/{user_id}", tags=["이용 내역"])
async def get_user_history(
    user_id: str,
    request: Request,
    limit: int = 50,
    db: Session = Depends(get_db)
):
    """사용자 이용 내역 조회"""
    controller = HistoryController()
    controller.set_request(request).set_db(db)
    return controller.get_user_history(user_id, limit)


@api_router.get("/history/detail/{history_id}", tags=["이용 내역"])
async def get_history_detail(
    history_id: str,
    request: Request,
    db: Session = Depends(get_db)
):
    """이용 내역 상세 조회"""
    controller = HistoryController()
    controller.set_request(request).set_db(db)
    return controller.get_history_detail(history_id)


# Admin Routes
@api_router.get("/admin/dashboard", tags=["관리자"])
async def get_dashboard(
    request: Request,
    db: Session = Depends(get_db)
):
    """관리자 대시보드"""
    controller = AdminController()
    controller.set_request(request).set_db(db)
    return controller.dashboard()


@api_router.get("/admin/history", tags=["관리자"])
async def get_all_history(
    request: Request,
    limit: int = 100,
    db: Session = Depends(get_db)
):
    """모든 이용 내역 조회"""
    controller = AdminController()
    controller.set_request(request).set_db(db)
    return controller.all_usage_history(limit)


@api_router.get("/admin/revenue", tags=["관리자"])
async def get_revenue(
    request: Request,
    days: int = 7,
    db: Session = Depends(get_db)
):
    """기간별 매출 조회"""
    controller = AdminController()
    controller.set_request(request).set_db(db)
    return controller.revenue_by_period(days)


# ==================== Original Routes ====================


# User Routes (RESTful)
@api_router.get("/users")
async def get_users(
    request: Request,
    page: int = 1,
    limit: int = 15,
    db: Session = Depends(get_db)
):
    """사용자 목록 조회"""
    controller = UserController()
    controller.set_request(request).set_db(db)
    return controller.index(page, limit)


@api_router.get("/users/{id}")
async def get_user(
    id: int,
    request: Request,
    db: Session = Depends(get_db)
):
    """사용자 상세 조회"""
    controller = UserController()
    controller.set_request(request).set_db(db)
    return controller.show(id)


@api_router.post("/users")
async def create_user(
    data: UserCreate,
    request: Request,
    db: Session = Depends(get_db)
):
    """사용자 생성"""
    controller = UserController()
    controller.set_request(request).set_db(db)
    return controller.store(data)


@api_router.put("/users/{id}")
async def update_user(
    id: int,
    data: UserUpdate,
    request: Request,
    db: Session = Depends(get_db)
):
    """사용자 수정"""
    controller = UserController()
    controller.set_request(request).set_db(db)
    return controller.update(id, data)


@api_router.delete("/users/{id}")
async def delete_user(
    id: int,
    request: Request,
    db: Session = Depends(get_db)
):
    """사용자 삭제"""
    controller = UserController()
    controller.set_request(request).set_db(db)
    return controller.destroy(id)


# Post Routes (RESTful)
@api_router.get("/posts")
async def get_posts(
    request: Request,
    page: int = 1,
    limit: int = 15,
    db: Session = Depends(get_db)
):
    """게시글 목록 조회"""
    controller = PostController()
    controller.set_request(request).set_db(db)
    return controller.index(page, limit)


@api_router.get("/posts/{id}")
async def get_post(
    id: int,
    request: Request,
    db: Session = Depends(get_db)
):
    """게시글 상세 조회"""
    controller = PostController()
    controller.set_request(request).set_db(db)
    return controller.show(id)


@api_router.post("/posts")
async def create_post(
    data: PostCreate,
    request: Request,
    db: Session = Depends(get_db)
):
    """게시글 생성"""
    controller = PostController()
    controller.set_request(request).set_db(db)
    return controller.store(data)


@api_router.put("/posts/{id}")
async def update_post(
    id: int,
    data: PostUpdate,
    request: Request,
    db: Session = Depends(get_db)
):
    """게시글 수정"""
    controller = PostController()
    controller.set_request(request).set_db(db)
    return controller.update(id, data)


@api_router.delete("/posts/{id}")
async def delete_post(
    id: int,
    request: Request,
    db: Session = Depends(get_db)
):
    """게시글 삭제"""
    controller = PostController()
    controller.set_request(request).set_db(db)
    return controller.destroy(id)
