"""
Seats API
좌석 관련 API 엔드포인트
"""

from fastapi import APIRouter, HTTPException, Query
from typing import List, Optional
from ..models.seat import Seat, SeatType, SeatStatus
from ..database import db

router = APIRouter()


@router.get("", response_model=List[Seat])
async def get_seats(
    seat_type: Optional[SeatType] = Query(None, description="좌석 타입 필터"),
    status: Optional[SeatStatus] = Query(None, description="좌석 상태 필터"),
    floor: Optional[int] = Query(None, description="층 필터", ge=1, le=4)
):
    """
    좌석 목록 조회

    필터 옵션:
    - **seat_type**: 좌석 타입 (fixed, free, study_room)
    - **status**: 좌석 상태 (available, occupied, reserved, maintenance)
    - **floor**: 층 (1-4)
    """
    try:
        seats = db.get_all_seats()

        # 필터 적용
        if seat_type:
            seats = [s for s in seats if s.seat_type == seat_type]
        if status:
            seats = [s for s in seats if s.status == status]
        if floor:
            seats = [s for s in seats if s.floor == floor]

        return seats
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"좌석 조회 실패: {str(e)}")


@router.get("/statistics")
async def get_seat_statistics():
    """
    좌석 통계 조회

    반환값:
    - 전체 좌석 수
    - 이용 가능 좌석 수
    - 사용 중 좌석 수
    - 예약된 좌석 수
    - 가용률
    - 타입별 좌석 수
    """
    try:
        stats = db.get_seat_statistics()
        return stats
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"통계 조회 실패: {str(e)}")


@router.get("/{seat_id}", response_model=Seat)
async def get_seat(seat_id: int):
    """
    특정 좌석 상세 조회

    - **seat_id**: 좌석 ID
    """
    seat = db.get_seat_by_id(seat_id)
    if not seat:
        raise HTTPException(status_code=404, detail="좌석을 찾을 수 없습니다.")
    return seat


@router.patch("/{seat_id}/status")
async def update_seat_status(seat_id: int, status: SeatStatus):
    """
    좌석 상태 업데이트 (관리자용)

    - **seat_id**: 좌석 ID
    - **status**: 새로운 상태
    """
    success = db.update_seat_status(seat_id, status)
    if not success:
        raise HTTPException(status_code=404, detail="좌석을 찾을 수 없습니다.")

    return {"message": "좌석 상태가 업데이트되었습니다.", "seat_id": seat_id, "status": status}
