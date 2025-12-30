"""
Controller Base Module
PHP 스타일의 컨트롤러 베이스 클래스
"""

from typing import Optional, Dict, Any
from fastapi import Request, Response
from fastapi.responses import JSONResponse, HTMLResponse
from fastapi.templating import Jinja2Templates
from sqlalchemy.orm import Session


class BaseController:
    """컨트롤러 베이스 클래스 - PHP 컨트롤러와 유사한 구조"""

    def __init__(self):
        self.templates = Jinja2Templates(directory="app/views/templates")
        self.request: Optional[Request] = None
        self.db: Optional[Session] = None

    def set_request(self, request: Request):
        """요청 객체 설정"""
        self.request = request
        return self

    def set_db(self, db: Session):
        """데이터베이스 세션 설정"""
        self.db = db
        return self

    def view(
        self,
        template: str,
        data: Optional[Dict[str, Any]] = None,
        status_code: int = 200
    ) -> HTMLResponse:
        """템플릿 렌더링 (PHP의 view와 유사)"""
        context = data or {}
        if self.request:
            context["request"] = self.request
        return self.templates.TemplateResponse(
            template,
            context,
            status_code=status_code
        )

    def json(
        self,
        data: Any,
        status_code: int = 200,
        message: Optional[str] = None
    ) -> JSONResponse:
        """JSON 응답 반환"""
        response_data = {
            "success": 200 <= status_code < 300,
            "data": data
        }
        if message:
            response_data["message"] = message
        return JSONResponse(content=response_data, status_code=status_code)

    def success(
        self,
        data: Any = None,
        message: str = "Success"
    ) -> JSONResponse:
        """성공 응답"""
        return self.json(data, status_code=200, message=message)

    def error(
        self,
        message: str = "Error",
        status_code: int = 400,
        data: Any = None
    ) -> JSONResponse:
        """에러 응답"""
        return self.json(data, status_code=status_code, message=message)

    def redirect(self, url: str, status_code: int = 302) -> Response:
        """리다이렉트 응답"""
        return Response(
            status_code=status_code,
            headers={"Location": url}
        )

    def validate(self, data: Dict, rules: Dict) -> tuple[bool, Dict]:
        """
        간단한 데이터 검증
        Returns: (is_valid, errors)
        """
        errors = {}
        for field, rule_list in rules.items():
            value = data.get(field)
            for rule in rule_list:
                if rule == "required" and not value:
                    errors[field] = f"{field} is required"
                elif rule.startswith("min:"):
                    min_length = int(rule.split(":")[1])
                    if value and len(str(value)) < min_length:
                        errors[field] = f"{field} must be at least {min_length} characters"
                elif rule.startswith("max:"):
                    max_length = int(rule.split(":")[1])
                    if value and len(str(value)) > max_length:
                        errors[field] = f"{field} must not exceed {max_length} characters"
        return len(errors) == 0, errors
