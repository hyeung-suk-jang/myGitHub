"""
Auth Controller
인증 컨트롤러
"""

from core.controller import BaseController
from app.models import User
from app.schemas import UserLogin, UserRegister, LoginResponse, UserResponse
from sqlalchemy.orm import Session
import uuid


class AuthController(BaseController):
    """인증 관련 컨트롤러"""

    def login(self, data: UserLogin):
        """로그인"""
        try:
            # 사용자 조회
            user = self.db.query(User).filter(
                User.email == data.email,
                User.password == data.password  # 실제 환경에서는 해싱된 비밀번호 비교 필요
            ).first()

            if not user:
                return self.error("이메일 또는 비밀번호가 올바르지 않습니다.", 401)

            # 로그인 성공
            user_response = UserResponse.model_validate(user)
            return self.success(
                LoginResponse(user=user_response, message="로그인 성공").model_dump(),
                "로그인에 성공했습니다."
            )

        except Exception as e:
            return self.error(f"로그인 중 오류가 발생했습니다: {str(e)}", 500)

    def register(self, data: UserRegister):
        """회원가입"""
        try:
            # 이메일 중복 확인
            existing_user = self.db.query(User).filter(User.email == data.email).first()
            if existing_user:
                return self.error("이미 사용 중인 이메일입니다.", 400)

            # 새 사용자 생성
            new_user = User(
                id=str(uuid.uuid4()),
                username=data.username,
                email=data.email,
                phone=data.phone,
                password=data.password,  # 실제 환경에서는 비밀번호 해싱 필요
                is_admin=False
            )

            self.db.add(new_user)
            self.db.commit()
            self.db.refresh(new_user)

            # 회원가입 성공
            user_response = UserResponse.model_validate(new_user)
            return self.success(
                LoginResponse(user=user_response, message="회원가입 성공").model_dump(),
                "회원가입에 성공했습니다."
            )

        except Exception as e:
            self.db.rollback()
            return self.error(f"회원가입 중 오류가 발생했습니다: {str(e)}", 500)

    def logout(self):
        """로그아웃"""
        return self.success({"message": "로그아웃되었습니다."}, "로그아웃되었습니다.")

    def me(self, user_id: str):
        """현재 사용자 정보 조회"""
        try:
            user = self.db.query(User).filter(User.id == user_id).first()
            if not user:
                return self.error("사용자를 찾을 수 없습니다.", 404)

            user_response = UserResponse.model_validate(user)
            return self.success(user_response.model_dump(), "사용자 정보 조회 성공")

        except Exception as e:
            return self.error(f"사용자 정보 조회 중 오류가 발생했습니다: {str(e)}", 500)
