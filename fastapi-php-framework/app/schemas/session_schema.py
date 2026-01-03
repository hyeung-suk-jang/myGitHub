"""
Session Schemas
세션 관련 스키마
"""

from pydantic import BaseModel, Field
from typing import Literal, Optional
from datetime import datetime


class SessionCreate(BaseModel):
    """세션 생성 요청"""
    user_id: str = Field(..., description="사용자 ID")
    seat_id: str = Field(..., description="좌석 ID")
    ticket_id: str = Field(..., description="이용권 ID")
    payment_method: Literal["card", "cash", "transfer"] = Field(..., description="결제 수단")


class SessionResponse(BaseModel):
    """세션 응답"""
    id: str
    user_id: str
    seat_id: str
    ticket_id: str
    start_time: Optional[datetime] = None
    end_time: Optional[datetime] = None
    remaining_minutes: int
    status: Literal["active", "completed", "expired"]

    class Config:
        from_attributes = True


class SessionEnd(BaseModel):
    """세션 종료 요청"""
    session_id: str = Field(..., description="세션 ID")


class SessionWithDetails(BaseModel):
    """세션 상세 정보"""
    session: SessionResponse
    seat_number: int
    ticket_name: str
    ticket_price: int
