import os
from pathlib import Path

# 프로젝트 루트 디렉토리
BASE_DIR = Path(__file__).resolve().parent.parent.parent

class Config:
    """기본 설정"""
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'dev-secret-key-change-in-production'

    # 데이터베이스 설정
    DATABASE_PATH = os.path.join(BASE_DIR, 'database', 'lms.db')

    # 파일 업로드 설정
    UPLOAD_FOLDER = os.path.join(BASE_DIR, 'uploads')
    MAX_CONTENT_LENGTH = 100 * 1024 * 1024  # 100MB
    ALLOWED_EXTENSIONS = {'pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'mp4', 'avi', 'mov'}

    # 세션 설정
    SESSION_TYPE = 'filesystem'
    PERMANENT_SESSION_LIFETIME = 3600  # 1시간

    # CORS 설정
    CORS_HEADERS = 'Content-Type'

class DevelopmentConfig(Config):
    """개발 환경 설정"""
    DEBUG = True
    TESTING = False

class ProductionConfig(Config):
    """프로덕션 환경 설정"""
    DEBUG = False
    TESTING = False

class TestingConfig(Config):
    """테스트 환경 설정"""
    DEBUG = True
    TESTING = True
    DATABASE_PATH = ':memory:'

# 환경별 설정 매핑
config_by_name = {
    'development': DevelopmentConfig,
    'production': ProductionConfig,
    'testing': TestingConfig,
    'default': DevelopmentConfig
}
