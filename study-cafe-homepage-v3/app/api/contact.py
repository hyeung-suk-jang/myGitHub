"""
Contact API
문의 관리 API
"""

from fastapi import APIRouter
from pydantic import BaseModel, EmailStr, Field
from datetime import datetime

router = APIRouter()


class ContactMessage(BaseModel):
    """문의 메시지 모델"""
    name: str = Field(..., min_length=2, max_length=50, description="이름")
    phone: str = Field(..., description="연락처")
    email: EmailStr = Field(..., description="이메일")
    subject: str = Field(..., min_length=5, max_length=100, description="제목")
    message: str = Field(..., min_length=10, max_length=1000, description="문의 내용")


class ContactResponse(BaseModel):
    """문의 응답 모델"""
    id: int
    name: str
    email: str
    subject: str
    created_at: datetime
    status: str = "received"


# Mock database
mock_contacts = []
contact_id_counter = 1


@router.post("", response_model=ContactResponse)
async def submit_contact(contact: ContactMessage):
    """
    문의 메시지 제출
    """
    global contact_id_counter

    # Create contact message
    new_contact = ContactResponse(
        id=contact_id_counter,
        name=contact.name,
        email=contact.email,
        subject=contact.subject,
        created_at=datetime.now(),
        status="received"
    )

    mock_contacts.append({
        **new_contact.dict(),
        "phone": contact.phone,
        "message": contact.message
    })

    contact_id_counter += 1

    # 실제로는 이메일 전송 또는 알림 시스템 구현
    print(f"📧 New Contact Message from {contact.name} ({contact.email})")
    print(f"   Subject: {contact.subject}")
    print(f"   Message: {contact.message[:50]}...")

    return new_contact


@router.get("")
async def get_contacts():
    """
    모든 문의 메시지 조회 (관리자용)
    """
    return mock_contacts
