"""
Database Configuration
데이터베이스 설정
"""

from typing import Dict, Any
from .settings import settings


# 데이터베이스 연결 설정
database_config: Dict[str, Any] = {
    "url": settings.DATABASE_URL,
    "echo": settings.DB_ECHO,
    "pool_size": 5,
    "max_overflow": 10,
    "pool_pre_ping": True,
    "pool_recycle": 3600,
}


# 데이터베이스 연결 문자열 예시
DATABASE_URLS = {
    "sqlite": "sqlite:///./database/app.db",
    "postgresql": "postgresql://user:password@localhost/dbname",
    "mysql": "mysql+pymysql://user:password@localhost/dbname",
}
