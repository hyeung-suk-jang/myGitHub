"""
Home Controller
홈 페이지 컨트롤러
"""

from core.controller import BaseController
from fastapi.responses import HTMLResponse


class HomeController(BaseController):
    """홈 컨트롤러 - PHP의 HomeController와 유사"""

    def index(self):
        """메인 페이지"""
        return self.view("home/index.html", {
            "title": "Welcome to FastAPI PHP Framework",
            "message": "PHP 스타일의 FastAPI 프레임워크입니다."
        })

    def about(self):
        """소개 페이지"""
        return self.view("home/about.html", {
            "title": "About Us"
        })

    def contact(self):
        """연락처 페이지"""
        return self.view("home/contact.html", {
            "title": "Contact Us"
        })

    def health(self):
        """헬스체크 API"""
        return self.success({
            "status": "healthy",
            "version": "1.0.0"
        })
