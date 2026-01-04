"""
Study Cafe Homepage - API Routes
"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, EmailStr
from datetime import datetime
from typing import Optional

router = APIRouter()


# 문의 폼 데이터 모델
class ContactForm(BaseModel):
    name: str
    phone: str
    email: EmailStr
    subject: str
    message: str


# 문의 응답 모델
class ContactResponse(BaseModel):
    success: bool
    message: str
    timestamp: str


# 저장용 간단한 메모리 데이터베이스 (실제로는 DB 사용)
contacts_db = []


@router.post("/contact", response_model=ContactResponse)
async def submit_contact(contact: ContactForm):
    """
    문의 폼 제출 API

    실제 프로덕션에서는:
    - 데이터베이스에 저장
    - 이메일 발송
    - 관리자 알림
    등을 구현해야 합니다.
    """
    try:
        # 현재 시간
        timestamp = datetime.now().isoformat()

        # 문의 데이터 저장 (메모리)
        contact_data = {
            "id": len(contacts_db) + 1,
            "name": contact.name,
            "phone": contact.phone,
            "email": contact.email,
            "subject": contact.subject,
            "message": contact.message,
            "timestamp": timestamp
        }
        contacts_db.append(contact_data)

        # 로그 출력
        print("\n" + "=" * 60)
        print("📧 새로운 문의가 접수되었습니다!")
        print("=" * 60)
        print(f"이름: {contact.name}")
        print(f"연락처: {contact.phone}")
        print(f"이메일: {contact.email}")
        print(f"제목: {contact.subject}")
        print(f"내용: {contact.message}")
        print(f"시간: {timestamp}")
        print("=" * 60 + "\n")

        return ContactResponse(
            success=True,
            message="문의가 성공적으로 접수되었습니다. 빠른 시일 내에 답변 드리겠습니다.",
            timestamp=timestamp
        )

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"문의 접수 중 오류가 발생했습니다: {str(e)}"
        )


@router.get("/contacts")
async def get_contacts():
    """
    모든 문의 조회 (관리자용)
    실제로는 인증/권한 체크가 필요합니다.
    """
    return {
        "total": len(contacts_db),
        "contacts": contacts_db
    }


@router.get("/health")
async def health_check():
    """헬스 체크 엔드포인트"""
    return {
        "status": "healthy",
        "service": "Study Cafe Homepage API",
        "timestamp": datetime.now().isoformat()
    }
