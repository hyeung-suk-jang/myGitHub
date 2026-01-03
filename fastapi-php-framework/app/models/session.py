"""
Session Model
이용 세션 모델
"""

from sqlalchemy import Column, Integer, String, DateTime, Enum, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from core.database import Base
from datetime import datetime
import enum


class SessionStatus(str, enum.Enum):
    """세션 상태"""
    ACTIVE = "active"
    COMPLETED = "completed"
    EXPIRED = "expired"


class Session(Base):
    """이용 세션 모델"""

    __tablename__ = "sessions"

    id = Column(String(36), primary_key=True, index=True)
    user_id = Column(String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    seat_id = Column(String(36), ForeignKey("seats.id", ondelete="RESTRICT"), nullable=False, index=True)
    ticket_id = Column(String(36), ForeignKey("tickets.id", ondelete="RESTRICT"), nullable=False)
    start_time = Column(DateTime(timezone=True), server_default=func.now(), nullable=False, index=True)
    end_time = Column(DateTime(timezone=True), nullable=True)
    remaining_minutes = Column(Integer, nullable=False)
    status = Column(Enum(SessionStatus), default=SessionStatus.ACTIVE, nullable=False, index=True)

    # 관계 설정
    user = relationship("User", back_populates="sessions")
    seat = relationship("Seat", back_populates="sessions")
    ticket = relationship("Ticket", back_populates="sessions")

    def __repr__(self):
        return f"<Session(id={self.id}, user_id={self.user_id}, status='{self.status}')>"

    def to_dict(self):
        """모델을 딕셔너리로 변환"""
        return {
            "id": self.id,
            "user_id": self.user_id,
            "seat_id": self.seat_id,
            "ticket_id": self.ticket_id,
            "start_time": self.start_time.isoformat() if self.start_time else None,
            "end_time": self.end_time.isoformat() if self.end_time else None,
            "remaining_minutes": self.remaining_minutes,
            "status": self.status.value if isinstance(self.status, SessionStatus) else self.status,
        }
