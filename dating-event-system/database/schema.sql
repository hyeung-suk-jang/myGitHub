-- 결혼정보 회사 미팅 행사 시스템 데이터베이스 스키마

-- 관리자 테이블
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 사용자 테이블 (프로필 정보)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    gender ENUM('M', 'F') NOT NULL,
    birth_date DATE NOT NULL,
    email VARCHAR(100),
    address TEXT,
    occupation VARCHAR(100),
    company VARCHAR(100),
    education VARCHAR(100),
    height INT,
    religion VARCHAR(50),
    smoking ENUM('Y', 'N'),
    drinking VARCHAR(50),
    hobby TEXT,
    introduction TEXT,
    profile_image VARCHAR(255),
    is_existing_customer BOOLEAN DEFAULT FALSE,
    customer_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 행사 테이블
CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    location_detail TEXT,
    max_participants INT NOT NULL,
    male_capacity INT NOT NULL,
    female_capacity INT NOT NULL,
    registration_fee INT NOT NULL,
    registration_start_date DATETIME NOT NULL,
    registration_end_date DATETIME NOT NULL,
    status ENUM('upcoming', 'registration_open', 'registration_closed', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    schedule_detail TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 행사 신청 테이블
CREATE TABLE IF NOT EXISTS registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_date DATETIME,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 서류 테이블
CREATE TABLE IF NOT EXISTS documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    document_type ENUM('id_card', 'salary_statement', 'residence_certificate', 'family_certificate') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 결제 테이블
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL,
    amount INT NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 체크인 테이블
CREATE TABLE IF NOT EXISTS checkins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL,
    checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    checkin_location VARCHAR(100),
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_checkin (registration_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 조별 그룹 테이블
CREATE TABLE IF NOT EXISTS groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    group_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 그룹 멤버 테이블
CREATE TABLE IF NOT EXISTS group_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_member (group_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 선택 테이블 (1,2,3차)
CREATE TABLE IF NOT EXISTS selections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    round INT NOT NULL COMMENT '1차, 2차, 3차',
    selector_id INT NOT NULL COMMENT '선택하는 사람',
    selected_id INT NOT NULL COMMENT '선택받는 사람',
    preference INT NOT NULL COMMENT '1지망, 2지망, 3지망',
    selection_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (selector_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_selection (event_id, round, selector_id, preference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 매칭 결과 테이블
CREATE TABLE IF NOT EXISTS matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    round INT NOT NULL COMMENT '1차, 2차, 3차',
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    match_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('matched', 'dating', 'completed') DEFAULT 'matched',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 데이트 신청 테이블 (매칭되지 않은 사람들)
CREATE TABLE IF NOT EXISTS date_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    round INT NOT NULL COMMENT '해당 라운드',
    requester_id INT NOT NULL COMMENT '신청자',
    receiver_id INT NOT NULL COMMENT '신청받는 사람',
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_time DATETIME,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 선택 라운드 상태 테이블
CREATE TABLE IF NOT EXISTS selection_rounds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    round INT NOT NULL COMMENT '1차, 2차, 3차',
    status ENUM('waiting', 'active', 'processing', 'completed') DEFAULT 'waiting',
    start_time DATETIME,
    end_time DATETIME,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_round (event_id, round)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 기본 관리자 계정 생성 (비밀번호: admin123)
INSERT INTO admins (username, password, name, email)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '시스템 관리자', 'admin@example.com')
ON DUPLICATE KEY UPDATE username=username;
