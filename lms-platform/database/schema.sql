-- LMS 플랫폼 데이터베이스 스키마

-- 사용자 테이블
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL, -- 'admin', 'instructor', 'student', 'assistant', 'system_admin'
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1
);

-- 강사 프로필 테이블
CREATE TABLE IF NOT EXISTS instructor_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    bio TEXT,
    specialization VARCHAR(200),
    contract_info TEXT,
    hourly_rate DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 과정 테이블
CREATE TABLE IF NOT EXISTS courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_name VARCHAR(200) NOT NULL,
    course_code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    max_students INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1
);

-- 반 테이블
CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id INTEGER NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    instructor_id INTEGER,
    room_number VARCHAR(50),
    schedule TEXT, -- JSON 형태로 시간표 저장
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(id)
);

-- 수강 신청 테이블
CREATE TABLE IF NOT EXISTS enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'active', -- 'active', 'completed', 'dropped'
    completion_date DATE,
    final_grade DECIMAL(5, 2),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE(student_id, class_id)
);

-- 교재 테이블
CREATE TABLE IF NOT EXISTS materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    file_type VARCHAR(50), -- 'pdf', 'video', 'link', 'document'
    video_url VARCHAR(500),
    course_id INTEGER,
    tags VARCHAR(500), -- JSON 배열
    difficulty VARCHAR(20), -- 'beginner', 'intermediate', 'advanced'
    version INTEGER DEFAULT 1,
    uploaded_by INTEGER,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- 교재 접근 권한 테이블
CREATE TABLE IF NOT EXISTS material_permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    material_id INTEGER NOT NULL,
    class_id INTEGER,
    user_id INTEGER,
    can_download BOOLEAN DEFAULT 1,
    can_view BOOLEAN DEFAULT 1,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 문제 은행 테이블
CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    question_text TEXT NOT NULL,
    question_type VARCHAR(20) NOT NULL, -- 'multiple_choice', 'subjective', 'matching'
    category VARCHAR(100),
    chapter VARCHAR(100),
    difficulty VARCHAR(20), -- 'easy', 'medium', 'hard'
    keywords VARCHAR(500), -- JSON 배열
    correct_answer TEXT, -- JSON 형태
    options TEXT, -- JSON 배열 (객관식 옵션)
    points INTEGER DEFAULT 1,
    created_by INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- 시험지 테이블
CREATE TABLE IF NOT EXISTS exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_name VARCHAR(200) NOT NULL,
    description TEXT,
    course_id INTEGER,
    duration_minutes INTEGER, -- 시험 시간 (분)
    pass_score DECIMAL(5, 2),
    shuffle_questions BOOLEAN DEFAULT 0,
    shuffle_options BOOLEAN DEFAULT 0,
    allow_retake BOOLEAN DEFAULT 0,
    max_retakes INTEGER DEFAULT 0,
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    created_by INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- 시험지-문제 매핑 테이블
CREATE TABLE IF NOT EXISTS exam_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    order_num INTEGER,
    points INTEGER DEFAULT 1,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- 시험 배정 테이블
CREATE TABLE IF NOT EXISTS exam_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER NOT NULL,
    class_id INTEGER,
    student_id INTEGER,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- 시험 응시 테이블
CREATE TABLE IF NOT EXISTS exam_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    attempt_number INTEGER DEFAULT 1,
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    submit_time TIMESTAMP,
    score DECIMAL(5, 2),
    status VARCHAR(20) DEFAULT 'in_progress', -- 'in_progress', 'completed', 'graded'
    ip_address VARCHAR(50),
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 학생 답안 테이블
CREATE TABLE IF NOT EXISTS student_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    attempt_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    student_answer TEXT, -- JSON 형태
    is_correct BOOLEAN,
    points_earned DECIMAL(5, 2),
    graded_by INTEGER,
    feedback TEXT,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id),
    FOREIGN KEY (graded_by) REFERENCES users(id)
);

-- 출결 관리 테이블
CREATE TABLE IF NOT EXISTS attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    attendance_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'present', -- 'present', 'absent', 'late', 'excused'
    notes TEXT,
    recorded_by INTEGER,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- 학습 진도 테이블
CREATE TABLE IF NOT EXISTS learning_progress (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    material_id INTEGER NOT NULL,
    progress_percentage DECIMAL(5, 2) DEFAULT 0,
    last_accessed TIMESTAMP,
    completed BOOLEAN DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    UNIQUE(student_id, material_id)
);

-- 공지사항 테이블
CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    target_role VARCHAR(20), -- NULL이면 전체, 'student', 'instructor' 등
    class_id INTEGER,
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_important BOOLEAN DEFAULT 0,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- 상담 메모 테이블
CREATE TABLE IF NOT EXISTS counseling_notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    counselor_id INTEGER NOT NULL,
    note_date DATE NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (counselor_id) REFERENCES users(id)
);

-- 강의 정산 테이블
CREATE TABLE IF NOT EXISTS instructor_settlements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    instructor_id INTEGER NOT NULL,
    month VARCHAR(7) NOT NULL, -- YYYY-MM 형식
    total_hours DECIMAL(10, 2),
    total_classes INTEGER,
    total_amount DECIMAL(10, 2),
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'paid'
    settlement_date DATE,
    FOREIGN KEY (instructor_id) REFERENCES users(id)
);

-- 과제 테이블
CREATE TABLE IF NOT EXISTS assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    due_date TIMESTAMP,
    max_score INTEGER DEFAULT 100,
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- 과제 제출 테이블
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    assignment_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(500),
    content TEXT,
    score DECIMAL(5, 2),
    feedback TEXT,
    graded_by INTEGER,
    graded_at TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id)
);

-- 인덱스 생성
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_enrollments_student ON enrollments(student_id);
CREATE INDEX idx_enrollments_class ON enrollments(class_id);
CREATE INDEX idx_exams_course ON exams(course_id);
CREATE INDEX idx_exam_attempts_exam ON exam_attempts(exam_id);
CREATE INDEX idx_exam_attempts_student ON exam_attempts(student_id);
CREATE INDEX idx_attendance_class_date ON attendance(class_id, attendance_date);
CREATE INDEX idx_materials_course ON materials(course_id);
