"""
Reservations API
예약 관련 API 엔드포인트
"""

from fastapi import APIRouter, HTTPException
from typing import List
from datetime import datetime
from ..models.seat import Reservation
from ..database import db

router = APIRouter()


@router.post("", response_model=Reservation, status_code=201)
async def create_reservation(reservation: Reservation):
    """
    예약 생성

    - **seat_id**: 좌석 ID
    - **user_name**: 예약자 이름
    - **user_phone**: 연락처 (형식: 010-1234-5678)
    - **user_email**: 이메일
    - **start_time**: 시작 시간
    - **end_time**: 종료 시간
    """
    # 좌석 존재 여부 확인
    seat = db.get_seat_by_id(reservation.seat_id)
    if not seat:
        raise HTTPException(status_code=404, detail="좌석을 찾을 수 없습니다.")

    # 시간 유효성 검증
    if reservation.start_time >= reservation.end_time:
        raise HTTPException(status_code=400, detail="종료 시간은 시작 시간보다 늦어야 합니다.")

    if reservation.start_time < datetime.now():
        raise HTTPException(status_code=400, detail="과거 시간으로는 예약할 수 없습니다.")

    # 예약 가능 여부 확인
    if not db.is_seat_available(reservation.seat_id, reservation.start_time, reservation.end_time):
        raise HTTPException(
            status_code=409,
            detail="해당 시간에 이미 예약이 있습니다. 다른 시간을 선택해주세요."
        )

    try:
        created_reservation = db.create_reservation(reservation)
        return created_reservation
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"예약 생성 실패: {str(e)}")


@router.get("/{reservation_id}", response_model=Reservation)
async def get_reservation(reservation_id: int):
    """
    예약 상세 조회

    - **reservation_id**: 예약 ID
    """
    reservation = db.get_reservation_by_id(reservation_id)
    if not reservation:
        raise HTTPException(status_code=404, detail="예약을 찾을 수 없습니다.")
    return reservation


@router.delete("/{reservation_id}")
async def cancel_reservation(reservation_id: int):
    """
    예약 취소

    - **reservation_id**: 예약 ID
    """
    success = db.cancel_reservation(reservation_id)
    if not success:
        raise HTTPException(status_code=404, detail="예약을 찾을 수 없습니다.")

    return {"message": "예약이 취소되었습니다.", "reservation_id": reservation_id}


@router.get("/seat/{seat_id}", response_model=List[Reservation])
async def get_seat_reservations(seat_id: int):
    """
    특정 좌석의 예약 목록 조회

    - **seat_id**: 좌석 ID
    """
    reservations = db.get_reservations_by_seat(seat_id)
    return reservations
