from sqlalchemy import Column, Integer, String, Float, DateTime, ForeignKey, Boolean, Text
from sqlalchemy.orm import relationship
from datetime import datetime
from database import Base

class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    email = Column(String, unique=True, index=True, nullable=False)
    username = Column(String, unique=True, index=True, nullable=False)
    hashed_password = Column(String, nullable=False)
    user_type = Column(String, nullable=False)  # 'author' or 'reader'
    created_at = Column(DateTime, default=datetime.utcnow)
    balance = Column(Float, default=0.0)  # 작가의 수익 잔액

    # Relationships
    books = relationship("Book", back_populates="author")
    payments = relationship("Payment", back_populates="user")
    settlements = relationship("Settlement", back_populates="author")

class Book(Base):
    __tablename__ = "books"

    id = Column(Integer, primary_key=True, index=True)
    title = Column(String, nullable=False, index=True)
    description = Column(Text)
    author_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    price = Column(Float, nullable=False)
    cover_image = Column(String)  # URL or path to cover image
    content = Column(Text, nullable=False)  # 동화책 내용
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    is_published = Column(Boolean, default=True)
    views = Column(Integer, default=0)

    # Relationships
    author = relationship("User", back_populates="books")
    payments = relationship("Payment", back_populates="book")

class Payment(Base):
    __tablename__ = "payments"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)  # 독자
    book_id = Column(Integer, ForeignKey("books.id"), nullable=False)
    amount = Column(Float, nullable=False)  # 결제 금액
    platform_fee = Column(Float, nullable=False)  # 플랫폼 수수료
    author_revenue = Column(Float, nullable=False)  # 작가 수익
    payment_method = Column(String)  # 결제 수단
    payment_status = Column(String, default="completed")  # completed, failed, refunded
    created_at = Column(DateTime, default=datetime.utcnow)

    # Relationships
    user = relationship("User", back_populates="payments")
    book = relationship("Book", back_populates="payments")

class Settlement(Base):
    __tablename__ = "settlements"

    id = Column(Integer, primary_key=True, index=True)
    author_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    amount = Column(Float, nullable=False)  # 정산 금액
    settlement_date = Column(DateTime, default=datetime.utcnow)
    status = Column(String, default="pending")  # pending, completed, failed
    bank_account = Column(String)  # 입금 계좌
    notes = Column(Text)

    # Relationships
    author = relationship("User", back_populates="settlements")
