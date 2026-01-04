"""
Seat Models
좌석 관련 데이터 모델
"""

from enum import Enum
from typing import Optional
from datetime import datetime
from pydantic import BaseModel, Field


class SeatType(str, Enum):
    """좌석 타입"""
    FIXED = "fixed"  # 고정석
    FREE = "free"    # 자유석
    STUDY_ROOM = "study_room"  # 스터디룸


class SeatStatus(str, Enum):
    """좌석 상태"""
    AVAILABLE = "available"  # 이용 가능
    OCCUPIED = "occupied"    # 사용 중
    RESERVED = "reserved"    # 예약됨
    MAINTENANCE = "maintenance"  # 정비 중


class Seat(BaseModel):
    """좌석 모델"""
    id: int = Field(..., description="좌석 ID")
    seat_number: str = Field(..., description="좌석 번호")
    seat_type: SeatType = Field(..., description="좌석 타입")
    status: SeatStatus = Field(default=SeatStatus.AVAILABLE, description="좌석 상태")
    floor: int = Field(..., description="층")
    capacity: int = Field(default=1, description="수용 인원")
    description: Optional[str] = Field(None, description="좌석 설명")


class Reservation(BaseModel):
    """예약 모델"""
    id: Optional[int] = Field(None, description="예약 ID")
    seat_id: int = Field(..., description="좌석 ID")
    user_name: str = Field(..., min_length=2, max_length=50, description="예약자 이름")
    user_phone: str = Field(..., pattern=r"^\d{3}-\d{4}-\d{4}$", description="예약자 연락처")
    user_email: str = Field(..., description="예약자 이메일")
    start_time: datetime = Field(..., description="시작 시간")
    end_time: datetime = Field(..., description="종료 시간")
    created_at: datetime = Field(default_factory=datetime.now, description="예약 생성 시간")
    status: str = Field(default="active", description="예약 상태")


class ContactMessage(BaseModel):
    """문의 메시지 모델"""
    name: str = Field(..., min_length=2, max_length=50, description="이름")
    phone: str = Field(..., description="연락처")
    email: str = Field(..., description="이메일")
    subject: str = Field(..., min_length=5, max_length=100, description="제목")
    message: str = Field(..., min_length=10, max_length=1000, description="문의 내용")
    created_at: datetime = Field(default_factory=datetime.now, description="생성 시간")
