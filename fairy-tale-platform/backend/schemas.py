from pydantic import BaseModel, EmailStr
from datetime import datetime
from typing import Optional, List

# User Schemas
class UserBase(BaseModel):
    email: EmailStr
    username: str
    user_type: str  # 'author' or 'reader'

class UserCreate(UserBase):
    password: str

class UserLogin(BaseModel):
    email: EmailStr
    password: str

class User(UserBase):
    id: int
    created_at: datetime
    balance: float

    class Config:
        from_attributes = True

# Book Schemas
class BookBase(BaseModel):
    title: str
    description: Optional[str] = None
    price: float
    cover_image: Optional[str] = None

class BookCreate(BookBase):
    content: str

class BookUpdate(BaseModel):
    title: Optional[str] = None
    description: Optional[str] = None
    price: Optional[float] = None
    cover_image: Optional[str] = None
    content: Optional[str] = None
    is_published: Optional[bool] = None

class Book(BookBase):
    id: int
    author_id: int
    created_at: datetime
    updated_at: datetime
    is_published: bool
    views: int

    class Config:
        from_attributes = True

class BookDetail(Book):
    content: str

# Payment Schemas
class PaymentCreate(BaseModel):
    book_id: int
    payment_method: Optional[str] = "card"

class Payment(BaseModel):
    id: int
    user_id: int
    book_id: int
    amount: float
    platform_fee: float
    author_revenue: float
    payment_method: Optional[str]
    payment_status: str
    created_at: datetime

    class Config:
        from_attributes = True

# Settlement Schemas
class SettlementCreate(BaseModel):
    amount: float
    bank_account: str
    notes: Optional[str] = None

class Settlement(BaseModel):
    id: int
    author_id: int
    amount: float
    settlement_date: datetime
    status: str
    bank_account: Optional[str]
    notes: Optional[str]

    class Config:
        from_attributes = True

# Token Schema
class Token(BaseModel):
    access_token: str
    token_type: str
    user: User
