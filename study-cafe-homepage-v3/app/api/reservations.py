"""
Reservations API
예약 관리 API
"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, EmailStr, Field
from datetime import datetime
from typing import Optional

router = APIRouter()


class ReservationCreate(BaseModel):
    """예약 생성 요청 모델"""
    seat_id: int = Field(..., description="좌석 ID")
    user_name: str = Field(..., min_length=2, max_length=50, description="사용자 이름")
    user_phone: str = Field(..., pattern=r"^\d{3}-\d{4}-\d{4}$", description="연락처")
    user_email: EmailStr = Field(..., description="이메일")
    start_time: datetime = Field(..., description="시작 시간")
    end_time: datetime = Field(..., description="종료 시간")


class ReservationResponse(BaseModel):
    """예약 응답 모델"""
    id: int
    seat_id: int
    user_name: str
    user_phone: str
    user_email: str
    start_time: datetime
    end_time: datetime
    created_at: datetime
    status: str = "confirmed"


# Mock database
mock_reservations = []
reservation_id_counter = 1


@router.post("", response_model=ReservationResponse)
async def create_reservation(reservation: ReservationCreate):
    """
    새로운 예약 생성
    """
    global reservation_id_counter

    # Validation
    if reservation.start_time >= reservation.end_time:
        raise HTTPException(
            status_code=400,
            detail="종료 시간은 시작 시간보다 늦어야 합니다."
        )

    if reservation.start_time < datetime.now():
        raise HTTPException(
            status_code=400,
            detail="과거 시간으로는 예약할 수 없습니다."
        )

    # Create reservation
    new_reservation = ReservationResponse(
        id=reservation_id_counter,
        seat_id=reservation.seat_id,
        user_name=reservation.user_name,
        user_phone=reservation.user_phone,
        user_email=reservation.user_email,
        start_time=reservation.start_time,
        end_time=reservation.end_time,
        created_at=datetime.now(),
        status="confirmed"
    )

    mock_reservations.append(new_reservation)
    reservation_id_counter += 1

    return new_reservation


@router.get("/{reservation_id}", response_model=ReservationResponse)
async def get_reservation(reservation_id: int):
    """
    예약 정보 조회
    """
    reservation = next(
        (r for r in mock_reservations if r.id == reservation_id),
        None
    )

    if not reservation:
        raise HTTPException(status_code=404, detail="예약을 찾을 수 없습니다.")

    return reservation


@router.delete("/{reservation_id}")
async def cancel_reservation(reservation_id: int):
    """
    예약 취소
    """
    global mock_reservations

    reservation = next(
        (r for r in mock_reservations if r.id == reservation_id),
        None
    )

    if not reservation:
        raise HTTPException(status_code=404, detail="예약을 찾을 수 없습니다.")

    mock_reservations = [r for r in mock_reservations if r.id != reservation_id]

    return {"message": "예약이 취소되었습니다.", "reservation_id": reservation_id}
