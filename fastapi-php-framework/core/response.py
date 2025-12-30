"""
Response Helper Module
응답 헬퍼 함수들
"""

from typing import Any, Optional, Dict
from fastapi.responses import JSONResponse, HTMLResponse, RedirectResponse, FileResponse


class Response:
    """응답 헬퍼 클래스 - PHP의 Response 파사드와 유사"""

    @staticmethod
    def json(
        data: Any,
        status_code: int = 200,
        headers: Optional[Dict[str, str]] = None
    ) -> JSONResponse:
        """JSON 응답 생성"""
        return JSONResponse(
            content=data,
            status_code=status_code,
            headers=headers
        )

    @staticmethod
    def success(
        data: Any = None,
        message: str = "Success",
        status_code: int = 200
    ) -> JSONResponse:
        """성공 응답"""
        return Response.json({
            "success": True,
            "message": message,
            "data": data
        }, status_code=status_code)

    @staticmethod
    def error(
        message: str = "Error",
        errors: Optional[Dict] = None,
        status_code: int = 400
    ) -> JSONResponse:
        """에러 응답"""
        content = {
            "success": False,
            "message": message
        }
        if errors:
            content["errors"] = errors
        return Response.json(content, status_code=status_code)

    @staticmethod
    def html(content: str, status_code: int = 200) -> HTMLResponse:
        """HTML 응답"""
        return HTMLResponse(content=content, status_code=status_code)

    @staticmethod
    def redirect(url: str, status_code: int = 302) -> RedirectResponse:
        """리다이렉트 응답"""
        return RedirectResponse(url=url, status_code=status_code)

    @staticmethod
    def file(
        path: str,
        filename: Optional[str] = None,
        media_type: Optional[str] = None
    ) -> FileResponse:
        """파일 응답"""
        return FileResponse(
            path=path,
            filename=filename,
            media_type=media_type
        )

    @staticmethod
    def download(path: str, filename: str) -> FileResponse:
        """파일 다운로드 응답"""
        return FileResponse(
            path=path,
            filename=filename,
            media_type="application/octet-stream"
        )

    @staticmethod
    def paginate(
        data: list,
        total: int,
        page: int = 1,
        per_page: int = 15
    ) -> JSONResponse:
        """페이지네이션 응답"""
        total_pages = (total + per_page - 1) // per_page
        return Response.json({
            "success": True,
            "data": data,
            "pagination": {
                "total": total,
                "per_page": per_page,
                "current_page": page,
                "total_pages": total_pages,
                "from": (page - 1) * per_page + 1 if data else 0,
                "to": (page - 1) * per_page + len(data)
            }
        })
