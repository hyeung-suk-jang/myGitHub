"""
API Router
"""

from fastapi import APIRouter
from .seats import router as seats_router
from .reservations import router as reservations_router
from .contact import router as contact_router

router = APIRouter()

router.include_router(seats_router, prefix="/seats", tags=["Seats"])
router.include_router(reservations_router, prefix="/reservations", tags=["Reservations"])
router.include_router(contact_router, prefix="/contact", tags=["Contact"])
