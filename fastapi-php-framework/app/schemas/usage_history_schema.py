"""
UsageHistory Schemas
이용 내역 관련 스키마
"""

from pydantic import BaseModel, Field
from typing import Optional
from datetime import datetime


class UsageHistoryResponse(BaseModel):
    """이용 내역 응답"""
    id: str
    user_id: str
    seat_number: int = Field(..., ge=1, le=30, description="좌석 번호")
    ticket_name: str = Field(..., description="이용권명")
    start_time: Optional[datetime] = None
    end_time: Optional[datetime] = None
    duration: int = Field(..., description="이용 시간 (분)")
    amount: int = Field(..., description="결제 금액")

    class Config:
        from_attributes = True


class AdminStats(BaseModel):
    """관리자 통계"""
    total_seats: int = Field(..., description="전체 좌석 수")
    available_seats: int = Field(..., description="이용 가능 좌석 수")
    occupied_seats: int = Field(..., description="사용 중 좌석 수")
    total_revenue: int = Field(..., description="총 매출")
    today_revenue: int = Field(..., description="오늘 매출")
    active_users: int = Field(..., description="현재 이용 중 사용자 수")
