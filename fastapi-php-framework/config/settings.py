"""
Application Settings
애플리케이션 전역 설정
"""

from pydantic_settings import BaseSettings
from typing import Optional
import os


class Settings(BaseSettings):
    """애플리케이션 설정 클래스 - PHP의 config와 유사"""

    # 애플리케이션 기본 설정
    APP_NAME: str = "FastAPI PHP Framework"
    APP_ENV: str = "development"
    APP_DEBUG: bool = True
    APP_URL: str = "http://localhost:8000"

    # 데이터베이스 설정
    DATABASE_URL: str = "sqlite:///./database/app.db"
    DB_ECHO: bool = False

    # 보안 설정
    SECRET_KEY: str = "your-secret-key-change-this-in-production"
    ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 30

    # CORS 설정
    CORS_ORIGINS: list = ["*"]
    CORS_ALLOW_CREDENTIALS: bool = True
    CORS_ALLOW_METHODS: list = ["*"]
    CORS_ALLOW_HEADERS: list = ["*"]

    # 파일 업로드 설정
    UPLOAD_DIR: str = "uploads"
    MAX_UPLOAD_SIZE: int = 10 * 1024 * 1024  # 10MB

    # 템플릿 설정
    TEMPLATE_DIR: str = "app/views/templates"

    # 페이지네이션 설정
    DEFAULT_PAGE_SIZE: int = 15
    MAX_PAGE_SIZE: int = 100

    class Config:
        env_file = ".env"
        case_sensitive = True


# 전역 설정 인스턴스
settings = Settings()
