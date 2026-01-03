"""
Seed Data
초기 데이터 삽입 스크립트
"""

import sys
import os

# 프로젝트 루트를 Python 경로에 추가
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from core.database import db
from app.models import User, Seat, SeatType, SeatStatus, Ticket, TicketType
import uuid


def seed_users():
    """테스트 사용자 생성"""
    print("사용자 데이터 삽입 중...")

    users = [
        User(
            id="1",
            username="admin",
            email="admin@studycafe.com",
            phone="010-1234-5678",
            password="admin123",
            is_admin=True
        ),
        User(
            id="2",
            username="홍길동",
            email="hong@example.com",
            phone="010-9876-5432",
            password="user123",
            is_admin=False
        ),
    ]

    session = db.get_session()
    try:
        for user in users:
            # 중복 확인
            existing = session.query(User).filter(User.email == user.email).first()
            if not existing:
                session.add(user)
        session.commit()
        print(f"✓ {len(users)}명의 사용자 추가 완료")
    except Exception as e:
        session.rollback()
        print(f"✗ 사용자 추가 실패: {e}")
    finally:
        session.close()


def seed_seats():
    """좌석 데이터 생성"""
    print("좌석 데이터 삽입 중...")

    seats = []
    for i in range(1, 31):
        # 좌석 타입 결정
        if i <= 20:
            seat_type = SeatType.SINGLE
        elif i <= 28:
            seat_type = SeatType.DOUBLE
        else:
            seat_type = SeatType.GROUP

        # 층수 결정 (1-15: 1층, 16-30: 2층)
        floor = 1 if i <= 15 else 2

        seat = Seat(
            id=f"seat-{i}",
            number=i,
            type=seat_type,
            status=SeatStatus.AVAILABLE,
            floor=floor
        )
        seats.append(seat)

    session = db.get_session()
    try:
        for seat in seats:
            # 중복 확인
            existing = session.query(Seat).filter(Seat.number == seat.number).first()
            if not existing:
                session.add(seat)
        session.commit()
        print(f"✓ {len(seats)}개의 좌석 추가 완료")
    except Exception as e:
        session.rollback()
        print(f"✗ 좌석 추가 실패: {e}")
    finally:
        session.close()


def seed_tickets():
    """이용권 데이터 생성"""
    print("이용권 데이터 삽입 중...")

    tickets = [
        Ticket(
            id="ticket-1",
            name="2시간 이용권",
            type=TicketType.HOURLY,
            duration=120,
            price=4000,
            description="2시간 자유롭게 이용 가능"
        ),
        Ticket(
            id="ticket-2",
            name="4시간 이용권",
            type=TicketType.HOURLY,
            duration=240,
            price=7000,
            description="4시간 자유롭게 이용 가능"
        ),
        Ticket(
            id="ticket-3",
            name="종일 이용권",
            type=TicketType.DAILY,
            duration=720,
            price=15000,
            description="12시간 자유롭게 이용 가능"
        ),
        Ticket(
            id="ticket-4",
            name="주간 이용권",
            type=TicketType.WEEKLY,
            duration=10080,
            price=80000,
            description="1주일 무제한 이용 가능"
        ),
        Ticket(
            id="ticket-5",
            name="월간 이용권",
            type=TicketType.MONTHLY,
            duration=43200,
            price=250000,
            description="1개월 무제한 이용 가능"
        ),
    ]

    session = db.get_session()
    try:
        for ticket in tickets:
            # 중복 확인
            existing = session.query(Ticket).filter(Ticket.id == ticket.id).first()
            if not existing:
                session.add(ticket)
        session.commit()
        print(f"✓ {len(tickets)}개의 이용권 추가 완료")
    except Exception as e:
        session.rollback()
        print(f"✗ 이용권 추가 실패: {e}")
    finally:
        session.close()


def main():
    """메인 실행 함수"""
    print("=" * 50)
    print("스터디 카페 키오스크 초기 데이터 삽입")
    print("=" * 50)

    # 데이터베이스 테이블 생성
    print("\n데이터베이스 테이블 생성 중...")
    try:
        db.create_tables()
        print("✓ 데이터베이스 테이블 생성 완료")
    except Exception as e:
        print(f"✗ 데이터베이스 테이블 생성 실패: {e}")
        return

    print()

    # 초기 데이터 삽입
    seed_users()
    seed_seats()
    seed_tickets()

    print("\n" + "=" * 50)
    print("초기 데이터 삽입 완료!")
    print("=" * 50)
    print("\n테스트 계정:")
    print("관리자 - admin@studycafe.com / admin123")
    print("일반 사용자 - hong@example.com / user123")
    print()


if __name__ == "__main__":
    main()
