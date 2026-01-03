"""
Admin Controller
관리자 컨트롤러
"""

from core.controller import BaseController
from app.models import Seat, SeatStatus, Session, SessionStatus, UsageHistory
from app.schemas import AdminStats, UsageHistoryResponse
from sqlalchemy import func
from datetime import datetime, timedelta


class AdminController(BaseController):
    """관리자 관련 컨트롤러"""

    def dashboard(self):
        """관리자 대시보드 통계"""
        try:
            # 전체 좌석 수
            total_seats = self.db.query(func.count(Seat.id)).scalar()

            # 이용 가능 좌석 수
            available_seats = self.db.query(func.count(Seat.id)).filter(
                Seat.status == SeatStatus.AVAILABLE
            ).scalar()

            # 사용 중 좌석 수
            occupied_seats = self.db.query(func.count(Seat.id)).filter(
                Seat.status == SeatStatus.OCCUPIED
            ).scalar()

            # 총 매출
            total_revenue = self.db.query(func.sum(UsageHistory.amount)).scalar() or 0

            # 오늘 매출
            today_start = datetime.now().replace(hour=0, minute=0, second=0, microsecond=0)
            today_revenue = self.db.query(func.sum(UsageHistory.amount)).filter(
                UsageHistory.start_time >= today_start
            ).scalar() or 0

            # 현재 이용 중인 사용자 수
            active_users = self.db.query(func.count(Session.id)).filter(
                Session.status == SessionStatus.ACTIVE
            ).scalar()

            stats = AdminStats(
                total_seats=total_seats,
                available_seats=available_seats,
                occupied_seats=occupied_seats,
                total_revenue=total_revenue,
                today_revenue=today_revenue,
                active_users=active_users
            )

            return self.success(stats.model_dump(), "대시보드 통계 조회 성공")

        except Exception as e:
            return self.error(f"대시보드 통계 조회 중 오류가 발생했습니다: {str(e)}", 500)

    def all_usage_history(self, limit: int = 100):
        """모든 이용 내역 조회"""
        try:
            histories = self.db.query(UsageHistory).order_by(
                UsageHistory.start_time.desc()
            ).limit(limit).all()

            histories_data = [
                UsageHistoryResponse.model_validate(history).model_dump()
                for history in histories
            ]

            return self.success(
                histories_data,
                f"이용 내역 조회 성공 (총 {len(histories_data)}개)"
            )

        except Exception as e:
            return self.error(f"이용 내역 조회 중 오류가 발생했습니다: {str(e)}", 500)

    def revenue_by_period(self, days: int = 7):
        """기간별 매출 조회"""
        try:
            start_date = datetime.now() - timedelta(days=days)

            revenue = self.db.query(
                func.date(UsageHistory.start_time).label('date'),
                func.sum(UsageHistory.amount).label('total')
            ).filter(
                UsageHistory.start_time >= start_date
            ).group_by(
                func.date(UsageHistory.start_time)
            ).all()

            revenue_data = [
                {"date": str(r.date), "total": r.total}
                for r in revenue
            ]

            return self.success(revenue_data, f"최근 {days}일 매출 조회 성공")

        except Exception as e:
            return self.error(f"매출 조회 중 오류가 발생했습니다: {str(e)}", 500)
