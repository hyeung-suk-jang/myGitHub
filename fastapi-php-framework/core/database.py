"""
Database Core Module
데이터베이스 연결 및 쿼리 관리
"""

from typing import Optional, Any, List, Dict
from sqlalchemy import create_engine, text
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, Session
import os

Base = declarative_base()


class Database:
    """데이터베이스 연결 및 쿼리 처리 클래스"""

    _instance: Optional['Database'] = None
    _engine = None
    _session_factory = None

    def __new__(cls):
        if cls._instance is None:
            cls._instance = super().__new__(cls)
        return cls._instance

    def __init__(self):
        if self._engine is None:
            self.connect()

    def connect(self, database_url: Optional[str] = None):
        """데이터베이스 연결 설정"""
        if database_url is None:
            database_url = os.getenv(
                "DATABASE_URL",
                "sqlite:///./database/app.db"
            )

        self._engine = create_engine(
            database_url,
            echo=os.getenv("DB_ECHO", "False").lower() == "true"
        )
        self._session_factory = sessionmaker(
            autocommit=False,
            autoflush=False,
            bind=self._engine
        )

    def get_session(self) -> Session:
        """데이터베이스 세션 반환"""
        if self._session_factory is None:
            self.connect()
        return self._session_factory()

    def create_tables(self):
        """모든 테이블 생성"""
        Base.metadata.create_all(bind=self._engine)

    def drop_tables(self):
        """모든 테이블 삭제"""
        Base.metadata.drop_all(bind=self._engine)

    def query(self, sql: str, params: Optional[Dict[str, Any]] = None) -> List[Dict]:
        """Raw SQL 쿼리 실행"""
        session = self.get_session()
        try:
            result = session.execute(text(sql), params or {})
            if result.returns_rows:
                return [dict(row._mapping) for row in result]
            session.commit()
            return []
        except Exception as e:
            session.rollback()
            raise e
        finally:
            session.close()

    def execute(self, sql: str, params: Optional[Dict[str, Any]] = None) -> int:
        """SQL 실행 (INSERT, UPDATE, DELETE)"""
        session = self.get_session()
        try:
            result = session.execute(text(sql), params or {})
            session.commit()
            return result.rowcount
        except Exception as e:
            session.rollback()
            raise e
        finally:
            session.close()


# 전역 데이터베이스 인스턴스
db = Database()


def get_db() -> Session:
    """FastAPI dependency용 데이터베이스 세션"""
    session = db.get_session()
    try:
        yield session
    finally:
        session.close()
