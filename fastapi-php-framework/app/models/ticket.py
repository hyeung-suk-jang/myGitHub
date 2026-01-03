"""
Ticket Model
이용권 모델
"""

from sqlalchemy import Column, Integer, String, Enum, Text
from sqlalchemy.orm import relationship
from core.database import Base
import enum


class TicketType(str, enum.Enum):
    """이용권 타입"""
    HOURLY = "hourly"
    DAILY = "daily"
    WEEKLY = "weekly"
    MONTHLY = "monthly"


class Ticket(Base):
    """이용권 모델"""

    __tablename__ = "tickets"

    id = Column(String(36), primary_key=True, index=True)
    name = Column(String(100), nullable=False)
    type = Column(Enum(TicketType), nullable=False, index=True)
    duration = Column(Integer, nullable=False)  # 분 단위
    price = Column(Integer, nullable=False)
    description = Column(Text, nullable=True)

    # 관계 설정
    sessions = relationship("Session", back_populates="ticket")

    def __repr__(self):
        return f"<Ticket(id={self.id}, name='{self.name}', price={self.price})>"

    def to_dict(self):
        """모델을 딕셔너리로 변환"""
        return {
            "id": self.id,
            "name": self.name,
            "type": self.type.value if isinstance(self.type, TicketType) else self.type,
            "duration": self.duration,
            "price": self.price,
            "description": self.description,
        }
