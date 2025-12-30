"""
User Controller
사용자 관리 컨트롤러
"""

from core.controller import BaseController
from app.models.user import User
from app.schemas.user_schema import UserCreate, UserUpdate
from typing import Optional


class UserController(BaseController):
    """사용자 컨트롤러 - CRUD 작업"""

    def index(self, page: int = 1, limit: int = 15):
        """사용자 목록 조회"""
        try:
            users = self.db.query(User).offset((page - 1) * limit).limit(limit).all()
            total = self.db.query(User).count()

            return self.success({
                "users": [user.to_dict() for user in users],
                "pagination": {
                    "page": page,
                    "limit": limit,
                    "total": total
                }
            })
        except Exception as e:
            return self.error(f"Failed to fetch users: {str(e)}", 500)

    def show(self, id: int):
        """사용자 상세 조회"""
        try:
            user = self.db.query(User).filter(User.id == id).first()
            if not user:
                return self.error("User not found", 404)
            return self.success(user.to_dict())
        except Exception as e:
            return self.error(f"Failed to fetch user: {str(e)}", 500)

    def store(self, data: UserCreate):
        """사용자 생성"""
        try:
            # 중복 이메일 체크
            existing_user = self.db.query(User).filter(User.email == data.email).first()
            if existing_user:
                return self.error("Email already exists", 400)

            # 새 사용자 생성
            user = User(
                name=data.name,
                email=data.email,
                password=data.password  # 실제로는 해싱 필요
            )
            self.db.add(user)
            self.db.commit()
            self.db.refresh(user)

            return self.success(user.to_dict(), "User created successfully")
        except Exception as e:
            self.db.rollback()
            return self.error(f"Failed to create user: {str(e)}", 500)

    def update(self, id: int, data: UserUpdate):
        """사용자 수정"""
        try:
            user = self.db.query(User).filter(User.id == id).first()
            if not user:
                return self.error("User not found", 404)

            # 데이터 업데이트
            if data.name is not None:
                user.name = data.name
            if data.email is not None:
                user.email = data.email
            if data.password is not None:
                user.password = data.password  # 실제로는 해싱 필요

            self.db.commit()
            self.db.refresh(user)

            return self.success(user.to_dict(), "User updated successfully")
        except Exception as e:
            self.db.rollback()
            return self.error(f"Failed to update user: {str(e)}", 500)

    def destroy(self, id: int):
        """사용자 삭제"""
        try:
            user = self.db.query(User).filter(User.id == id).first()
            if not user:
                return self.error("User not found", 404)

            self.db.delete(user)
            self.db.commit()

            return self.success(None, "User deleted successfully")
        except Exception as e:
            self.db.rollback()
            return self.error(f"Failed to delete user: {str(e)}", 500)
