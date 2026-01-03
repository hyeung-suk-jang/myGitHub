"""
Seat Controller
좌석 컨트롤러
"""

from core.controller import BaseController
from app.models import Seat, SeatStatus
from app.schemas import SeatResponse
from typing import List, Optional


class SeatController(BaseController):
    """좌석 관련 컨트롤러"""

    def index(self, floor: Optional[int] = None):
        """좌석 목록 조회"""
        try:
            query = self.db.query(Seat)

            # 층수 필터링
            if floor:
                query = query.filter(Seat.floor == floor)

            seats = query.order_by(Seat.number).all()

            seats_data = [SeatResponse.model_validate(seat).model_dump() for seat in seats]

            return self.success(seats_data, f"좌석 목록 조회 성공 (총 {len(seats_data)}석)")

        except Exception as e:
            return self.error(f"좌석 목록 조회 중 오류가 발생했습니다: {str(e)}", 500)

    def show(self, seat_id: str):
        """좌석 상세 조회"""
        try:
            seat = self.db.query(Seat).filter(Seat.id == seat_id).first()

            if not seat:
                return self.error("좌석을 찾을 수 없습니다.", 404)

            seat_data = SeatResponse.model_validate(seat).model_dump()
            return self.success(seat_data, "좌석 조회 성공")

        except Exception as e:
            return self.error(f"좌석 조회 중 오류가 발생했습니다: {str(e)}", 500)

    def available_seats(self):
        """이용 가능한 좌석 조회"""
        try:
            seats = self.db.query(Seat).filter(
                Seat.status == SeatStatus.AVAILABLE
            ).order_by(Seat.number).all()

            seats_data = [SeatResponse.model_validate(seat).model_dump() for seat in seats]

            return self.success(
                seats_data,
                f"이용 가능한 좌석 조회 성공 (총 {len(seats_data)}석)"
            )

        except Exception as e:
            return self.error(f"이용 가능한 좌석 조회 중 오류가 발생했습니다: {str(e)}", 500)

    def update_status(self, seat_id: str, status: str):
        """좌석 상태 업데이트"""
        try:
            seat = self.db.query(Seat).filter(Seat.id == seat_id).first()

            if not seat:
                return self.error("좌석을 찾을 수 없습니다.", 404)

            # 상태 업데이트
            seat.status = SeatStatus(status)
            self.db.commit()
            self.db.refresh(seat)

            seat_data = SeatResponse.model_validate(seat).model_dump()
            return self.success(seat_data, "좌석 상태 업데이트 성공")

        except Exception as e:
            self.db.rollback()
            return self.error(f"좌석 상태 업데이트 중 오류가 발생했습니다: {str(e)}", 500)
