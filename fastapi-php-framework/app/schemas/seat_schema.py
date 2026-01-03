"""
Seat Schemas
좌석 관련 스키마
"""

from pydantic import BaseModel, Field
from typing import Literal


class SeatResponse(BaseModel):
    """좌석 응답"""
    id: str
    number: int = Field(..., ge=1, le=30, description="좌석 번호")
    type: Literal["single", "double", "group"] = Field(..., description="좌석 타입")
    status: Literal["available", "occupied", "reserved"] = Field(..., description="좌석 상태")
    floor: int = Field(..., ge=1, le=2, description="층수")

    class Config:
        from_attributes = True


class SeatSelect(BaseModel):
    """좌석 선택 요청"""
    seat_id: str = Field(..., description="좌석 ID")
