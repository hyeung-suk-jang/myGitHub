"""
Routes Module
라우트 정의 - PHP의 routes와 유사
"""

from .web import web_router
from .api import api_router

__all__ = ["web_router", "api_router"]
