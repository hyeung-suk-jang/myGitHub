"""
Ticket Schemas
이용권 관련 스키마
"""

from pydantic import BaseModel, Field
from typing import Literal, Optional


class TicketResponse(BaseModel):
    """이용권 응답"""
    id: str
    name: str = Field(..., max_length=100, description="이용권명")
    type: Literal["hourly", "daily", "weekly", "monthly"] = Field(..., description="이용권 타입")
    duration: int = Field(..., gt=0, description="이용 시간 (분)")
    price: int = Field(..., gt=0, description="가격")
    description: Optional[str] = Field(None, description="설명")

    class Config:
        from_attributes = True


class TicketSelect(BaseModel):
    """이용권 선택 요청"""
    ticket_id: str = Field(..., description="이용권 ID")
