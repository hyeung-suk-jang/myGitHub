"""
API Router
API 라우터 패키지
"""

from fastapi import APIRouter
from .contact import router as contact_router
from .seats import router as seats_router
from .reservations import router as reservations_router

# 메인 API 라우터
router = APIRouter()

# 각 라우터 포함
router.include_router(contact_router, prefix="/contact", tags=["Contact"])
router.include_router(seats_router, prefix="/seats", tags=["Seats"])
router.include_router(reservations_router, prefix="/reservations", tags=["Reservations"])

__all__ = ["router"]
