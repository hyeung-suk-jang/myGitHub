"""
Auth Schemas
인증 관련 스키마
"""

from pydantic import BaseModel, EmailStr, Field
from typing import Optional
from datetime import datetime


class UserLogin(BaseModel):
    """로그인 요청"""
    email: EmailStr = Field(..., description="이메일")
    password: str = Field(..., min_length=6, description="비밀번호")


class UserRegister(BaseModel):
    """회원가입 요청"""
    username: str = Field(..., min_length=2, max_length=100, description="사용자 이름")
    email: EmailStr = Field(..., description="이메일")
    phone: str = Field(..., min_length=10, max_length=20, description="전화번호")
    password: str = Field(..., min_length=6, description="비밀번호")


class UserResponse(BaseModel):
    """사용자 응답"""
    id: str
    username: str
    email: str
    phone: str
    is_admin: bool
    created_at: Optional[datetime] = None

    class Config:
        from_attributes = True


class LoginResponse(BaseModel):
    """로그인 응답"""
    user: UserResponse
    message: str = "로그인 성공"
