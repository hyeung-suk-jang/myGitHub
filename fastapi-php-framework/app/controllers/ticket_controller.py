"""
Ticket Controller
이용권 컨트롤러
"""

from core.controller import BaseController
from app.models import Ticket
from app.schemas import TicketResponse
from typing import Optional


class TicketController(BaseController):
    """이용권 관련 컨트롤러"""

    def index(self, ticket_type: Optional[str] = None):
        """이용권 목록 조회"""
        try:
            query = self.db.query(Ticket)

            # 타입 필터링
            if ticket_type:
                query = query.filter(Ticket.type == ticket_type)

            tickets = query.order_by(Ticket.price).all()

            tickets_data = [TicketResponse.model_validate(ticket).model_dump() for ticket in tickets]

            return self.success(tickets_data, f"이용권 목록 조회 성공 (총 {len(tickets_data)}개)")

        except Exception as e:
            return self.error(f"이용권 목록 조회 중 오류가 발생했습니다: {str(e)}", 500)

    def show(self, ticket_id: str):
        """이용권 상세 조회"""
        try:
            ticket = self.db.query(Ticket).filter(Ticket.id == ticket_id).first()

            if not ticket:
                return self.error("이용권을 찾을 수 없습니다.", 404)

            ticket_data = TicketResponse.model_validate(ticket).model_dump()
            return self.success(ticket_data, "이용권 조회 성공")

        except Exception as e:
            return self.error(f"이용권 조회 중 오류가 발생했습니다: {str(e)}", 500)
