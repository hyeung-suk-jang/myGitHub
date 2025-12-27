-- 개발자 전용 전자책 판매 사이트 데이터베이스 스키마
-- Database: dev_ebook_store

CREATE DATABASE IF NOT EXISTS dev_ebook_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dev_ebook_store;

-- 회원 테이블
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    github_username VARCHAR(100),
    profile_image VARCHAR(255),
    role ENUM('user', 'author', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- 프로그래밍 언어 카테고리
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 기술 레벨
CREATE TABLE IF NOT EXISTS skill_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
) ENGINE=InnoDB;

-- 전자책 테이블
CREATE TABLE IF NOT EXISTS ebooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    author_id INT NOT NULL,
    category_id INT NOT NULL,
    skill_level_id INT NOT NULL,
    description TEXT,
    content_preview TEXT,
    cover_image VARCHAR(255),
    file_path VARCHAR(255),
    sample_code_url VARCHAR(255),
    github_repo_url VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2),
    page_count INT,
    published_year INT,
    version VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    views INT DEFAULT 0,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (skill_level_id) REFERENCES skill_levels(id),
    INDEX idx_title (title),
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_skill_level (skill_level_id),
    INDEX idx_price (price)
) ENGINE=InnoDB;

-- 전자책 태그 (기술 스택)
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 전자책-태그 연결 테이블
CREATE TABLE IF NOT EXISTS ebook_tags (
    ebook_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (ebook_id, tag_id),
    FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 리뷰 테이블
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ebook_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    code_quality_rating INT CHECK (code_quality_rating BETWEEN 1 AND 5),
    content_rating INT CHECK (content_rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ebook (ebook_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB;

-- 주문 테이블
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_order_number (order_number),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- 주문 상세 테이블
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    ebook_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (ebook_id) REFERENCES ebooks(id)
) ENGINE=InnoDB;

-- 사용자 라이브러리 (구매한 전자책)
CREATE TABLE IF NOT EXISTS user_library (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ebook_id INT NOT NULL,
    order_id INT NOT NULL,
    download_count INT DEFAULT 0,
    last_read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    UNIQUE KEY unique_user_ebook (user_id, ebook_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- 장바구니 테이블
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ebook_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_ebook (user_id, ebook_id)
) ENGINE=InnoDB;

-- 초기 데이터 삽입

-- 기술 레벨
INSERT INTO skill_levels (name, slug, description) VALUES
('초급', 'beginner', '프로그래밍을 처음 시작하는 입문자를 위한 레벨'),
('중급', 'intermediate', '기본 개념을 이해하고 있는 개발자를 위한 레벨'),
('고급', 'advanced', '심화 내용과 전문적인 주제를 다루는 레벨'),
('전문가', 'expert', '특정 분야의 전문가를 위한 고급 레벨');

-- 카테고리
INSERT INTO categories (name, slug, icon, description) VALUES
('JavaScript', 'javascript', '⚡', '모던 JavaScript, ES6+, Node.js, React, Vue 등'),
('Python', 'python', '🐍', 'Python 기초부터 Django, Flask, 데이터 과학까지'),
('Java', 'java', '☕', 'Java 프로그래밍, Spring, JVM 언어'),
('PHP', 'php', '🐘', 'PHP 웹 개발, Laravel, Symfony'),
('Database', 'database', '💾', 'SQL, NoSQL, 데이터베이스 설계 및 최적화'),
('DevOps', 'devops', '🔧', 'Docker, Kubernetes, CI/CD, 클라우드'),
('Mobile', 'mobile', '📱', 'iOS, Android, React Native, Flutter'),
('Web Development', 'web-development', '🌐', 'HTML, CSS, 웹 표준, 웹 접근성'),
('Algorithm', 'algorithm', '🧮', '알고리즘, 자료구조, 코딩 테스트'),
('Security', 'security', '🔒', '보안, 암호화, 해킹 방어');

-- 기술 태그
INSERT INTO tags (name, slug) VALUES
('React', 'react'),
('Vue.js', 'vuejs'),
('Angular', 'angular'),
('Node.js', 'nodejs'),
('Express', 'express'),
('Django', 'django'),
('Flask', 'flask'),
('Spring Boot', 'spring-boot'),
('Laravel', 'laravel'),
('MySQL', 'mysql'),
('PostgreSQL', 'postgresql'),
('MongoDB', 'mongodb'),
('Docker', 'docker'),
('Kubernetes', 'kubernetes'),
('AWS', 'aws'),
('Git', 'git'),
('TypeScript', 'typescript'),
('GraphQL', 'graphql'),
('REST API', 'rest-api'),
('TDD', 'tdd');

-- 관리자 계정 (비밀번호: admin123)
INSERT INTO users (email, username, password, full_name, role) VALUES
('admin@devbooks.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '관리자', 'admin');
