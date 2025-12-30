"""
Post Schema
게시글 데이터 검증 스키마
"""

from pydantic import BaseModel, Field
from typing import Optional
from datetime import datetime


class PostBase(BaseModel):
    """게시글 기본 스키마"""
    title: str = Field(..., min_length=1, max_length=200)
    content: str = Field(..., min_length=1)


class PostCreate(PostBase):
    """게시글 생성 스키마"""
    user_id: int


class PostUpdate(BaseModel):
    """게시글 수정 스키마"""
    title: Optional[str] = Field(None, min_length=1, max_length=200)
    content: Optional[str] = Field(None, min_length=1)


class PostResponse(PostBase):
    """게시글 응답 스키마"""
    id: int
    user_id: int
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None

    class Config:
        from_attributes = True
