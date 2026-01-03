"""
History Controller
이용 내역 컨트롤러
"""

from core.controller import BaseController
from app.models import UsageHistory
from app.schemas import UsageHistoryResponse


class HistoryController(BaseController):
    """이용 내역 관련 컨트롤러"""

    def get_user_history(self, user_id: str, limit: int = 50):
        """사용자 이용 내역 조회"""
        try:
            histories = self.db.query(UsageHistory).filter(
                UsageHistory.user_id == user_id
            ).order_by(
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

    def get_history_detail(self, history_id: str):
        """이용 내역 상세 조회"""
        try:
            history = self.db.query(UsageHistory).filter(
                UsageHistory.id == history_id
            ).first()

            if not history:
                return self.error("이용 내역을 찾을 수 없습니다.", 404)

            history_data = UsageHistoryResponse.model_validate(history).model_dump()
            return self.success(history_data, "이용 내역 상세 조회 성공")

        except Exception as e:
            return self.error(f"이용 내역 조회 중 오류가 발생했습니다: {str(e)}", 500)
