"""
Database Module
간단한 인메모리 데이터베이스 (실제 프로덕션에서는 PostgreSQL, MySQL 등 사용)
"""

from typing import List, Optional
from datetime import datetime, timedelta
from .models.seat import Seat, SeatType, SeatStatus, Reservation, ContactMessage


class Database:
    """인메모리 데이터베이스"""

    def __init__(self):
        self.seats: List[Seat] = []
        self.reservations: List[Reservation] = []
        self.contact_messages: List[ContactMessage] = []
        self._init_seats()

    def _init_seats(self):
        """초기 좌석 데이터 생성"""
        # 1층 고정석 (1-30번)
        for i in range(1, 31):
            self.seats.append(Seat(
                id=i,
                seat_number=f"F1-{i:03d}",
                seat_type=SeatType.FIXED,
                status=SeatStatus.AVAILABLE if i % 4 != 0 else SeatStatus.OCCUPIED,
                floor=1,
                capacity=1,
                description="개인 전용 고정석, 수납함 제공"
            ))

        # 2층 자유석 (31-70번)
        for i in range(31, 71):
            self.seats.append(Seat(
                id=i,
                seat_number=f"F2-{i-30:03d}",
                seat_type=SeatType.FREE,
                status=SeatStatus.AVAILABLE if i % 5 != 0 else SeatStatus.OCCUPIED,
                floor=2,
                capacity=1,
                description="자유롭게 이용 가능한 좌석"
            ))

        # 3층 고정석 (71-100번)
        for i in range(71, 101):
            self.seats.append(Seat(
                id=i,
                seat_number=f"F3-{i-70:03d}",
                seat_type=SeatType.FIXED,
                status=SeatStatus.AVAILABLE if i % 3 != 0 else SeatStatus.OCCUPIED,
                floor=3,
                capacity=1,
                description="개인 전용 고정석, 수납함 제공"
            ))

        # 4층 스터디룸 (101-110번)
        for i in range(101, 111):
            self.seats.append(Seat(
                id=i,
                seat_number=f"SR-{i-100:02d}",
                seat_type=SeatType.STUDY_ROOM,
                status=SeatStatus.AVAILABLE if i % 3 != 0 else SeatStatus.RESERVED,
                floor=4,
                capacity=6 if i % 2 == 0 else 4,
                description=f"{6 if i % 2 == 0 else 4}인용 프리미엄 스터디룸"
            ))

    # Seat Operations
    def get_all_seats(self) -> List[Seat]:
        """모든 좌석 조회"""
        return self.seats

    def get_seat_by_id(self, seat_id: int) -> Optional[Seat]:
        """ID로 좌석 조회"""
        for seat in self.seats:
            if seat.id == seat_id:
                return seat
        return None

    def get_seats_by_type(self, seat_type: SeatType) -> List[Seat]:
        """타입별 좌석 조회"""
        return [seat for seat in self.seats if seat.seat_type == seat_type]

    def get_seats_by_status(self, status: SeatStatus) -> List[Seat]:
        """상태별 좌석 조회"""
        return [seat for seat in self.seats if seat.status == status]

    def get_seats_by_floor(self, floor: int) -> List[Seat]:
        """층별 좌석 조회"""
        return [seat for seat in self.seats if seat.floor == floor]

    def update_seat_status(self, seat_id: int, status: SeatStatus) -> bool:
        """좌석 상태 업데이트"""
        seat = self.get_seat_by_id(seat_id)
        if seat:
            seat.status = status
            return True
        return False

    def get_seat_statistics(self) -> dict:
        """좌석 통계 조회"""
        total = len(self.seats)
        available = len([s for s in self.seats if s.status == SeatStatus.AVAILABLE])
        occupied = len([s for s in self.seats if s.status == SeatStatus.OCCUPIED])
        reserved = len([s for s in self.seats if s.status == SeatStatus.RESERVED])

        return {
            "total": total,
            "available": available,
            "occupied": occupied,
            "reserved": reserved,
            "availability_rate": round((available / total) * 100, 1) if total > 0 else 0,
            "by_type": {
                "fixed": len([s for s in self.seats if s.seat_type == SeatType.FIXED]),
                "free": len([s for s in self.seats if s.seat_type == SeatType.FREE]),
                "study_room": len([s for s in self.seats if s.seat_type == SeatType.STUDY_ROOM])
            }
        }

    # Reservation Operations
    def create_reservation(self, reservation: Reservation) -> Reservation:
        """예약 생성"""
        reservation.id = len(self.reservations) + 1
        self.reservations.append(reservation)
        # 좌석 상태를 예약됨으로 변경
        self.update_seat_status(reservation.seat_id, SeatStatus.RESERVED)
        return reservation

    def get_reservation_by_id(self, reservation_id: int) -> Optional[Reservation]:
        """ID로 예약 조회"""
        for reservation in self.reservations:
            if reservation.id == reservation_id:
                return reservation
        return None

    def get_reservations_by_seat(self, seat_id: int) -> List[Reservation]:
        """좌석별 예약 조회"""
        return [r for r in self.reservations if r.seat_id == seat_id and r.status == "active"]

    def cancel_reservation(self, reservation_id: int) -> bool:
        """예약 취소"""
        reservation = self.get_reservation_by_id(reservation_id)
        if reservation:
            reservation.status = "cancelled"
            # 좌석 상태를 이용 가능으로 변경
            self.update_seat_status(reservation.seat_id, SeatStatus.AVAILABLE)
            return True
        return False

    def is_seat_available(self, seat_id: int, start_time: datetime, end_time: datetime) -> bool:
        """좌석 예약 가능 여부 확인"""
        seat_reservations = self.get_reservations_by_seat(seat_id)
        for reservation in seat_reservations:
            # 시간 겹침 체크
            if (start_time < reservation.end_time and end_time > reservation.start_time):
                return False
        return True

    # Contact Message Operations
    def create_contact_message(self, message: ContactMessage) -> ContactMessage:
        """문의 메시지 생성"""
        self.contact_messages.append(message)
        return message

    def get_all_contact_messages(self) -> List[ContactMessage]:
        """모든 문의 메시지 조회"""
        return self.contact_messages


# 전역 데이터베이스 인스턴스
db = Database()
