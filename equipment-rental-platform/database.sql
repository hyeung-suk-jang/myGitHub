-- 고가 장비 대여 플랫폼 데이터베이스 스키마
-- MySQL/MariaDB

CREATE DATABASE IF NOT EXISTS equipment_rental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE equipment_rental;

-- 사용자 테이블
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('supplier', 'renter', 'both', 'admin') DEFAULT 'renter',
    identity_verified BOOLEAN DEFAULT FALSE,
    identity_document VARCHAR(255), -- 신분증 파일 경로
    profile_image VARCHAR(255),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 장비 카테고리 테이블
CREATE TABLE IF NOT EXISTS equipment_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    parent_category_id INT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES equipment_categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 장비 테이블
CREATE TABLE IF NOT EXISTS equipment (
    equipment_id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    category_id INT,
    equipment_name VARCHAR(255) NOT NULL,
    model_name VARCHAR(255),
    serial_number VARCHAR(255),
    brand VARCHAR(100),
    purchase_price DECIMAL(12, 2),
    daily_rate DECIMAL(10, 2) NOT NULL,
    deposit_amount DECIMAL(10, 2) NOT NULL,
    condition_grade ENUM('S', 'A', 'B', 'C') DEFAULT 'A',
    description TEXT,
    specifications JSON, -- 장비 사양 (JSON 형태)
    location VARCHAR(255),
    available_from DATE,
    available_to DATE,
    status ENUM('available', 'reserved', 'in_use', 'returned', 'inspecting', 'maintenance', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    total_rental_count INT DEFAULT 0,
    average_rating DECIMAL(3, 2) DEFAULT 0.00,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(category_id) ON DELETE SET NULL,
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_daily_rate (daily_rate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 장비 이미지 테이블
CREATE TABLE IF NOT EXISTS equipment_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE CASCADE,
    INDEX idx_equipment (equipment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 대여 예약 테이블
CREATE TABLE IF NOT EXISTS rentals (
    rental_id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT NOT NULL,
    renter_id INT NOT NULL,
    owner_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    daily_rate DECIMAL(10, 2) NOT NULL,
    total_days INT NOT NULL,
    rental_fee DECIMAL(12, 2) NOT NULL,
    deposit_amount DECIMAL(10, 2) NOT NULL,
    platform_commission DECIMAL(10, 2) NOT NULL,
    owner_payout DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'active', 'completed', 'cancelled', 'disputed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded', 'partially_refunded') DEFAULT 'unpaid',
    pickup_method ENUM('delivery', 'in_person') DEFAULT 'delivery',
    tracking_number VARCHAR(100),
    pickup_confirmed_at TIMESTAMP NULL,
    return_confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE CASCADE,
    FOREIGN KEY (renter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_renter (renter_id),
    INDEX idx_owner (owner_id),
    INDEX idx_equipment (equipment_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 검수 리포트 테이블
CREATE TABLE IF NOT EXISTS inspection_reports (
    report_id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    inspector_id INT NOT NULL,
    inspection_type ENUM('pre_rental', 'post_rental') NOT NULL,
    condition_grade ENUM('S', 'A', 'B', 'C', 'damaged') NOT NULL,
    notes TEXT,
    damage_description TEXT,
    estimated_repair_cost DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE,
    FOREIGN KEY (inspector_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_rental (rental_id),
    INDEX idx_type (inspection_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 검수 이미지 테이블
CREATE TABLE IF NOT EXISTS inspection_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_description VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES inspection_reports(report_id) ON DELETE CASCADE,
    INDEX idx_report (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 결제 테이블
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    payer_id INT NOT NULL,
    payment_type ENUM('rental_fee', 'deposit', 'repair_cost', 'refund') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    pg_response JSON, -- PG사 응답 데이터
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE,
    FOREIGN KEY (payer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_rental (rental_id),
    INDEX idx_payer (payer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 정산 테이블
CREATE TABLE IF NOT EXISTS settlements (
    settlement_id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    owner_id INT NOT NULL,
    rental_fee DECIMAL(12, 2) NOT NULL,
    platform_commission DECIMAL(10, 2) NOT NULL,
    payout_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    payout_date DATE NULL,
    bank_account VARCHAR(100),
    bank_name VARCHAR(100),
    account_holder VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 리뷰 테이블
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    equipment_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    review_type ENUM('equipment', 'owner', 'renter') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_equipment (equipment_id),
    INDEX idx_reviewer (reviewer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 분쟁 테이블
CREATE TABLE IF NOT EXISTS disputes (
    dispute_id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    reporter_id INT NOT NULL,
    dispute_type ENUM('damage', 'lost', 'late_return', 'payment', 'other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    resolution TEXT,
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_rental (rental_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 알림 테이블
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('rental', 'payment', 'settlement', 'review', 'dispute', 'system') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT, -- 관련 엔티티 ID (rental_id, payment_id 등)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 보험 테이블
CREATE TABLE IF NOT EXISTS insurance (
    insurance_id INT PRIMARY KEY AUTO_INCREMENT,
    rental_id INT NOT NULL,
    policy_number VARCHAR(100),
    provider VARCHAR(100),
    coverage_amount DECIMAL(12, 2),
    premium DECIMAL(10, 2),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'claimed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE CASCADE,
    INDEX idx_rental (rental_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 장비 대여 이력 테이블
CREATE TABLE IF NOT EXISTS equipment_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT NOT NULL,
    event_type ENUM('created', 'rented', 'returned', 'repaired', 'inspected', 'status_changed') NOT NULL,
    event_description TEXT,
    rental_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE CASCADE,
    FOREIGN KEY (rental_id) REFERENCES rentals(rental_id) ON DELETE SET NULL,
    INDEX idx_equipment (equipment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 초기 카테고리 데이터 삽입
INSERT INTO equipment_categories (category_name, description) VALUES
('카메라 및 영상장비', '전문가용 카메라, 렌즈, 영상 촬영 장비'),
('음향 장비', '마이크, 믹서, 스피커 등 음향 관련 장비'),
('조명 장비', '스튜디오 조명, LED 라이트, 조명 스탠드'),
('드론', '촬영용 드론 및 액세서리'),
('컴퓨터 및 IT 장비', '노트북, 워크스테이션, 서버 등'),
('건설 장비', '전동 공구, 측정 장비'),
('스포츠 및 레저', '캠핑 장비, 수상스포츠 장비'),
('기타', '기타 고가 장비');

-- 관리자 계정 생성 (비밀번호: admin123!)
INSERT INTO users (email, password_hash, username, user_type, identity_verified) VALUES
('admin@equiprent.com', '$2y$10$YourHashedPasswordHere', '시스템 관리자', 'admin', TRUE);
