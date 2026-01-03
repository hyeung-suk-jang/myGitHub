"""
UsageHistory Model
이용 내역 모델
"""

from sqlalchemy import Column, Integer, String, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from core.database import Base
from datetime import datetime


class UsageHistory(Base):
    """이용 내역 모델"""

    __tablename__ = "usage_history"

    id = Column(String(36), primary_key=True, index=True)
    user_id = Column(String(36), ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    seat_number = Column(Integer, nullable=False)
    ticket_name = Column(String(100), nullable=False)
    start_time = Column(DateTime(timezone=True), nullable=False, index=True)
    end_time = Column(DateTime(timezone=True), nullable=False)
    duration = Column(Integer, nullable=False)  # 분 단위
    amount = Column(Integer, nullable=False)

    # 관계 설정
    user = relationship("User", back_populates="usage_history")

    def __repr__(self):
        return f"<UsageHistory(id={self.id}, user_id={self.user_id}, amount={self.amount})>"

    def to_dict(self):
        """모델을 딕셔너리로 변환"""
        return {
            "id": self.id,
            "user_id": self.user_id,
            "seat_number": self.seat_number,
            "ticket_name": self.ticket_name,
            "start_time": self.start_time.isoformat() if self.start_time else None,
            "end_time": self.end_time.isoformat() if self.end_time else None,
            "duration": self.duration,
            "amount": self.amount,
        }
