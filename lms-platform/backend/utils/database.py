import sqlite3
import os
from contextlib import contextmanager
from config.config import Config

def init_database():
    """데이터베이스 초기화"""
    db_path = Config.DATABASE_PATH

    # 데이터베이스 디렉토리가 없으면 생성
    os.makedirs(os.path.dirname(db_path), exist_ok=True)

    # 스키마 파일 읽기
    schema_path = os.path.join(os.path.dirname(db_path), 'schema.sql')

    if os.path.exists(schema_path):
        with open(schema_path, 'r', encoding='utf-8') as f:
            schema = f.read()

        # 데이터베이스 생성 및 스키마 실행
        conn = sqlite3.connect(db_path)
        conn.executescript(schema)
        conn.commit()
        conn.close()
        print(f"데이터베이스가 초기화되었습니다: {db_path}")
    else:
        print(f"스키마 파일을 찾을 수 없습니다: {schema_path}")

@contextmanager
def get_db_connection():
    """데이터베이스 연결 컨텍스트 매니저"""
    conn = sqlite3.connect(Config.DATABASE_PATH)
    conn.row_factory = sqlite3.Row  # 딕셔너리 형태로 결과 반환
    try:
        yield conn
    finally:
        conn.close()

def execute_query(query, params=None, fetch_one=False, fetch_all=False, commit=False):
    """쿼리 실행 헬퍼 함수"""
    with get_db_connection() as conn:
        cursor = conn.cursor()

        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)

        result = None

        if fetch_one:
            result = cursor.fetchone()
            if result:
                result = dict(result)
        elif fetch_all:
            result = cursor.fetchall()
            result = [dict(row) for row in result]

        if commit:
            conn.commit()
            result = cursor.lastrowid

        cursor.close()
        return result

def insert_sample_data():
    """샘플 데이터 삽입"""
    with get_db_connection() as conn:
        cursor = conn.cursor()

        # 관리자 계정 생성 (비밀번호: admin123)
        cursor.execute("""
            INSERT OR IGNORE INTO users (username, password, email, full_name, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        """, ('admin', 'admin123', 'admin@lms.com', '시스템 관리자', 'admin', 1))

        # 강사 계정 생성 (비밀번호: instructor123)
        cursor.execute("""
            INSERT OR IGNORE INTO users (username, password, email, full_name, role, phone, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, ('instructor1', 'instructor123', 'instructor1@lms.com', '김강사', 'instructor', '010-1234-5678', 1))

        # 학생 계정 생성 (비밀번호: student123)
        cursor.execute("""
            INSERT OR IGNORE INTO users (username, password, email, full_name, role, phone, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, ('student1', 'student123', 'student1@lms.com', '이학생', 'student', '010-9876-5432', 1))

        cursor.execute("""
            INSERT OR IGNORE INTO users (username, password, email, full_name, role, phone, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, ('student2', 'student123', 'student2@lms.com', '박학생', 'student', '010-1111-2222', 1))

        # 과정 생성
        cursor.execute("""
            INSERT OR IGNORE INTO courses (course_name, course_code, description, start_date, end_date, max_students, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, ('웹 개발 기초', 'WEB101', 'HTML, CSS, JavaScript를 활용한 웹 개발 기초 과정', '2026-01-06', '2026-03-31', 30, 1))

        cursor.execute("""
            INSERT OR IGNORE INTO courses (course_name, course_code, description, start_date, end_date, max_students, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, ('Python 프로그래밍', 'PY101', 'Python 기초부터 실전까지', '2026-01-06', '2026-04-30', 25, 1))

        # 반 생성
        cursor.execute("""
            INSERT OR IGNORE INTO classes (course_id, class_name, instructor_id, room_number, schedule, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        """, (1, '웹개발 A반', 2, '101호', '{"요일": "월,수,금", "시간": "09:00-12:00"}', '2026-01-06', '2026-03-31'))

        conn.commit()
        cursor.close()
        print("샘플 데이터가 삽입되었습니다.")
