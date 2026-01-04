"""
Contact API
문의 관련 API 엔드포인트
"""

from fastapi import APIRouter, HTTPException
from typing import List
from ..models.seat import ContactMessage
from ..database import db

router = APIRouter()


@router.post("", response_model=ContactMessage, status_code=201)
async def create_contact_message(message: ContactMessage):
    """
    문의 메시지 생성

    - **name**: 문의자 이름
    - **phone**: 연락처
    - **email**: 이메일
    - **subject**: 제목
    - **message**: 문의 내용
    """
    try:
        created_message = db.create_contact_message(message)
        return created_message
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"문의 메시지 생성 실패: {str(e)}")


@router.get("", response_model=List[ContactMessage])
async def get_all_contact_messages():
    """
    모든 문의 메시지 조회 (관리자용)
    """
    try:
        messages = db.get_all_contact_messages()
        return messages
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"문의 메시지 조회 실패: {str(e)}")
