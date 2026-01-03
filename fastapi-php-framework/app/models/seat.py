"""
Seat Model
좌석 모델
"""

from sqlalchemy import Column, Integer, String, Enum
from sqlalchemy.orm import relationship
from core.database import Base
import enum


class SeatType(str, enum.Enum):
    """좌석 타입"""
    SINGLE = "single"
    DOUBLE = "double"
    GROUP = "group"


class SeatStatus(str, enum.Enum):
    """좌석 상태"""
    AVAILABLE = "available"
    OCCUPIED = "occupied"
    RESERVED = "reserved"


class Seat(Base):
    """좌석 모델"""

    __tablename__ = "seats"

    id = Column(String(36), primary_key=True, index=True)
    number = Column(Integer, unique=True, nullable=False, index=True)
    type = Column(Enum(SeatType), nullable=False)
    status = Column(Enum(SeatStatus), default=SeatStatus.AVAILABLE, nullable=False)
    floor = Column(Integer, nullable=False)

    # 관계 설정
    sessions = relationship("Session", back_populates="seat")

    def __repr__(self):
        return f"<Seat(id={self.id}, number={self.number}, status='{self.status}')>"

    def to_dict(self):
        """모델을 딕셔너리로 변환"""
        return {
            "id": self.id,
            "number": self.number,
            "type": self.type.value if isinstance(self.type, SeatType) else self.type,
            "status": self.status.value if isinstance(self.status, SeatStatus) else self.status,
            "floor": self.floor,
        }
