"""
API Routes
API 라우트 - PHP의 api.php와 유사
"""

from fastapi import APIRouter, Request, Depends
from app.controllers import UserController, PostController
from app.schemas import UserCreate, UserUpdate, PostCreate, PostUpdate
from core.database import get_db
from sqlalchemy.orm import Session

api_router = APIRouter(prefix="/api")


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
