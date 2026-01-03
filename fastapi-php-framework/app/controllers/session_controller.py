"""
Session Controller
세션 컨트롤러
"""

from core.controller import BaseController
from app.models import Session, SessionStatus, Seat, SeatStatus, Ticket, UsageHistory
from app.schemas import SessionCreate, SessionResponse, SessionWithDetails
from datetime import datetime
import uuid


class SessionController(BaseController):
    """세션 관련 컨트롤러"""

    def start(self, data: SessionCreate):
        """세션 시작 (결제 및 입실)"""
        try:
            # 사용자의 활성 세션 확인
            active_session = self.db.query(Session).filter(
                Session.user_id == data.user_id,
                Session.status == SessionStatus.ACTIVE
            ).first()

            if active_session:
                return self.error("이미 이용 중인 세션이 있습니다.", 400)

            # 좌석 확인
            seat = self.db.query(Seat).filter(Seat.id == data.seat_id).first()
            if not seat:
                return self.error("좌석을 찾을 수 없습니다.", 404)
            if seat.status != SeatStatus.AVAILABLE:
                return self.error("선택한 좌석은 이용할 수 없습니다.", 400)

            # 이용권 확인
            ticket = self.db.query(Ticket).filter(Ticket.id == data.ticket_id).first()
            if not ticket:
                return self.error("이용권을 찾을 수 없습니다.", 404)

            # 세션 생성
            new_session = Session(
                id=str(uuid.uuid4()),
                user_id=data.user_id,
                seat_id=data.seat_id,
                ticket_id=data.ticket_id,
                remaining_minutes=ticket.duration,
                status=SessionStatus.ACTIVE
            )

            self.db.add(new_session)

            # 좌석 상태 변경
            seat.status = SeatStatus.OCCUPIED

            self.db.commit()
            self.db.refresh(new_session)

            session_data = SessionResponse.model_validate(new_session).model_dump()
            return self.success(session_data, "입실이 완료되었습니다.")

        except Exception as e:
            self.db.rollback()
            return self.error(f"입실 처리 중 오류가 발생했습니다: {str(e)}", 500)

    def end(self, session_id: str):
        """세션 종료 (퇴실)"""
        try:
            session = self.db.query(Session).filter(Session.id == session_id).first()

            if not session:
                return self.error("세션을 찾을 수 없습니다.", 404)

            if session.status != SessionStatus.ACTIVE:
                return self.error("이미 종료된 세션입니다.", 400)

            # 세션 종료
            session.end_time = datetime.now()
            session.status = SessionStatus.COMPLETED

            # 좌석 상태 변경
            seat = self.db.query(Seat).filter(Seat.id == session.seat_id).first()
            if seat:
                seat.status = SeatStatus.AVAILABLE

            # 이용 내역 생성
            ticket = self.db.query(Ticket).filter(Ticket.id == session.ticket_id).first()
            if seat and ticket:
                duration = int((session.end_time - session.start_time).total_seconds() / 60)

                usage_history = UsageHistory(
                    id=str(uuid.uuid4()),
                    user_id=session.user_id,
                    seat_number=seat.number,
                    ticket_name=ticket.name,
                    start_time=session.start_time,
                    end_time=session.end_time,
                    duration=duration,
                    amount=ticket.price
                )
                self.db.add(usage_history)

            self.db.commit()
            self.db.refresh(session)

            session_data = SessionResponse.model_validate(session).model_dump()
            return self.success(session_data, "퇴실이 완료되었습니다.")

        except Exception as e:
            self.db.rollback()
            return self.error(f"퇴실 처리 중 오류가 발생했습니다: {str(e)}", 500)

    def get_active_session(self, user_id: str):
        """사용자의 활성 세션 조회"""
        try:
            session = self.db.query(Session).filter(
                Session.user_id == user_id,
                Session.status == SessionStatus.ACTIVE
            ).first()

            if not session:
                return self.success(None, "활성 세션이 없습니다.")

            # 좌석 및 이용권 정보 조회
            seat = self.db.query(Seat).filter(Seat.id == session.seat_id).first()
            ticket = self.db.query(Ticket).filter(Ticket.id == session.ticket_id).first()

            session_with_details = SessionWithDetails(
                session=SessionResponse.model_validate(session),
                seat_number=seat.number if seat else 0,
                ticket_name=ticket.name if ticket else "",
                ticket_price=ticket.price if ticket else 0
            )

            return self.success(session_with_details.model_dump(), "활성 세션 조회 성공")

        except Exception as e:
            return self.error(f"활성 세션 조회 중 오류가 발생했습니다: {str(e)}", 500)

    def get_all_active_sessions(self):
        """모든 활성 세션 조회 (관리자용)"""
        try:
            sessions = self.db.query(Session).filter(
                Session.status == SessionStatus.ACTIVE
            ).all()

            sessions_data = []
            for session in sessions:
                seat = self.db.query(Seat).filter(Seat.id == session.seat_id).first()
                ticket = self.db.query(Ticket).filter(Ticket.id == session.ticket_id).first()

                sessions_data.append(SessionWithDetails(
                    session=SessionResponse.model_validate(session),
                    seat_number=seat.number if seat else 0,
                    ticket_name=ticket.name if ticket else "",
                    ticket_price=ticket.price if ticket else 0
                ).model_dump())

            return self.success(sessions_data, f"활성 세션 목록 조회 성공 (총 {len(sessions_data)}개)")

        except Exception as e:
            return self.error(f"활성 세션 목록 조회 중 오류가 발생했습니다: {str(e)}", 500)
