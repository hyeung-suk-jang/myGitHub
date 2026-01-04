"""
Seats API
좌석 관리 API
"""

from fastapi import APIRouter, HTTPException
from typing import List
from app.models.seat import Seat, SeatStatus
import random

router = APIRouter()

# Mock data - 실제로는 데이터베이스에서 가져와야 함
def generate_mock_seats() -> List[Seat]:
    """더미 좌석 데이터 생성"""
    seats = []

    # 고정석 (1-60)
    for i in range(1, 61):
        status = random.choice([SeatStatus.AVAILABLE, SeatStatus.OCCUPIED, SeatStatus.RESERVED])
        seats.append(Seat(
            id=i,
            seat_number=f"F-{i:03d}",
            seat_type="fixed",
            status=status
        ))

    # 자유석 (61-120)
    for i in range(61, 121):
        status = random.choice([SeatStatus.AVAILABLE, SeatStatus.OCCUPIED, SeatStatus.RESERVED])
        seats.append(Seat(
            id=i,
            seat_number=f"R-{i-60:03d}",
            seat_type="free",
            status=status
        ))

    # 스터디룸 (121-130)
    for i in range(121, 131):
        status = random.choice([SeatStatus.AVAILABLE, SeatStatus.OCCUPIED, SeatStatus.RESERVED])
        seats.append(Seat(
            id=i,
            seat_number=f"SR-{i-120:02d}",
            seat_type="study_room",
            status=status
        ))

    return seats


@router.get("", response_model=List[Seat])
async def get_seats():
    """
    모든 좌석 정보 조회
    """
    seats = generate_mock_seats()
    return seats


@router.get("/statistics")
async def get_seat_statistics():
    """
    좌석 통계 정보 조회
    """
    seats = generate_mock_seats()

    available = sum(1 for seat in seats if seat.status == SeatStatus.AVAILABLE)
    occupied = sum(1 for seat in seats if seat.status == SeatStatus.OCCUPIED)
    reserved = sum(1 for seat in seats if seat.status == SeatStatus.RESERVED)
    total = len(seats)

    return {
        "available": available,
        "occupied": occupied,
        "reserved": reserved,
        "total": total,
        "occupancy_rate": round((occupied / total) * 100, 2) if total > 0 else 0
    }


@router.get("/{seat_id}", response_model=Seat)
async def get_seat(seat_id: int):
    """
    특정 좌석 정보 조회
    """
    seats = generate_mock_seats()
    seat = next((s for s in seats if s.id == seat_id), None)

    if not seat:
        raise HTTPException(status_code=404, detail="좌석을 찾을 수 없습니다.")

    return seat
