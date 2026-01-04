"""
Seat Model
좌석 데이터 모델
"""

from pydantic import BaseModel
from enum import Enum


class SeatStatus(str, Enum):
    """좌석 상태"""
    AVAILABLE = "available"
    OCCUPIED = "occupied"
    RESERVED = "reserved"


class SeatType(str, Enum):
    """좌석 타입"""
    FIXED = "fixed"
    FREE = "free"
    STUDY_ROOM = "study_room"


class Seat(BaseModel):
    """좌석 모델"""
    id: int
    seat_number: str
    seat_type: str
    status: SeatStatus

    class Config:
        use_enum_values = True
