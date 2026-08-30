CREATE DATABASE secure_course
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE secure_course;


-- =========================================================
-- 1. USERS
-- =========================================================

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    avatar VARCHAR(255) DEFAULT NULL,

    role ENUM('student', 'admin')
        NOT NULL DEFAULT 'student',

    balance DECIMAL(12,2)
        NOT NULL DEFAULT 0,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =========================================================
-- 2. CATEGORIES
-- =========================================================

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL UNIQUE,

    slug VARCHAR(120) NOT NULL UNIQUE,

    description TEXT,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- 3. COURSES
-- =========================================================

CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    category_id INT UNSIGNED DEFAULT NULL,

    title VARCHAR(255) NOT NULL,

    slug VARCHAR(255) NOT NULL UNIQUE,

    description TEXT,

    thumbnail VARCHAR(255) DEFAULT NULL,

    price DECIMAL(12,2)
        NOT NULL DEFAULT 0,

    duration_days INT
        NOT NULL DEFAULT 30,

    status ENUM(
        'draft',
        'published'
    )
    NOT NULL DEFAULT 'published',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_courses_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE SET NULL
);


-- =========================================================
-- 4. LESSONS
-- =========================================================

CREATE TABLE lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    course_id INT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,

    content LONGTEXT,

    video_url VARCHAR(500) DEFAULT NULL,

    file_path VARCHAR(500) DEFAULT NULL,

    lesson_order INT
        NOT NULL DEFAULT 1,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lessons_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE
);


-- =========================================================
-- 5. ENROLLMENTS
-- User được quyền học khóa học
-- =========================================================

CREATE TABLE enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    course_id INT UNSIGNED NOT NULL,

    enrolled_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    start_date DATETIME NOT NULL,

    end_date DATETIME NOT NULL,

    status ENUM(
        'active',
        'expired',
        'cancelled'
    )
    NOT NULL DEFAULT 'active',

    UNIQUE KEY unique_enrollment (
        user_id,
        course_id
    ),

    CONSTRAINT fk_enrollments_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_enrollments_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE
);


-- =========================================================
-- 6. LESSON PROGRESS
-- =========================================================

CREATE TABLE lesson_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    lesson_id INT UNSIGNED NOT NULL,

    completed TINYINT(1)
        NOT NULL DEFAULT 0,

    completed_at DATETIME DEFAULT NULL,

    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_progress (
        user_id,
        lesson_id
    ),

    CONSTRAINT fk_progress_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_progress_lesson
        FOREIGN KEY (lesson_id)
        REFERENCES lessons(id)
        ON DELETE CASCADE
);


-- =========================================================
-- 7. FAVORITES
-- =========================================================

CREATE TABLE favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    course_id INT UNSIGNED NOT NULL,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_favorite (
        user_id,
        course_id
    ),

    CONSTRAINT fk_favorites_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_favorites_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE
);


-- =========================================================
-- 8. REVIEWS
-- =========================================================

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    course_id INT UNSIGNED NOT NULL,

    rating TINYINT UNSIGNED NOT NULL,

    comment TEXT,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT chk_rating
        CHECK (rating BETWEEN 1 AND 5),

    UNIQUE KEY unique_review (
        user_id,
        course_id
    ),

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reviews_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE
);


-- =========================================================
-- 9. TRANSACTIONS
-- =========================================================

CREATE TABLE transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL,

    type ENUM(
        'deposit',
        'purchase',
        'refund'
    ) NOT NULL,

    amount DECIMAL(12,2) NOT NULL,

    description VARCHAR(255),

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_transactions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =========================================================
-- 10. ACCESS LOGS
-- =========================================================

CREATE TABLE access_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED DEFAULT NULL,

    course_id INT UNSIGNED DEFAULT NULL,

    lesson_id INT UNSIGNED DEFAULT NULL,

    action VARCHAR(100) NOT NULL,

    ip_address VARCHAR(45),

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_access_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_access_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_access_lesson
        FOREIGN KEY (lesson_id)
        REFERENCES lessons(id)
        ON DELETE SET NULL
);


-- =========================================================
-- 11. DỮ LIỆU MẪU - CATEGORY
-- =========================================================

INSERT INTO categories
(
    name,
    slug,
    description
)
VALUES
(
    'Lập trình',
    'lap-trinh',
    'Các khóa học lập trình.'
),
(
    'An toàn thông tin',
    'an-toan-thong-tin',
    'Các khóa học về cybersecurity.'
),
(
    'Mạng máy tính',
    'mang-may-tinh',
    'Các khóa học về network.'
);


-- =========================================================
-- 12. DỮ LIỆU MẪU - COURSES
-- =========================================================

INSERT INTO courses
(
    category_id,
    title,
    slug,
    description,
    thumbnail,
    price,
    duration_days,
    status
)
VALUES
(
    1,
    'PHP Cơ bản',
    'php-co-ban',
    'Khóa học PHP dành cho người mới bắt đầu.',
    NULL,
    100000,
    30,
    'published'
),
(
    2,
    'PHP và Bảo mật Web',
    'php-va-bao-mat-web',
    'Học PHP kết hợp kiến thức bảo mật web.',
    NULL,
    150000,
    30,
    'published'
),
(
    3,
    'Mạng máy tính cơ bản',
    'mang-may-tinh-co-ban',
    'Tìm hiểu các kiến thức nền tảng về mạng máy tính.',
    NULL,
    120000,
    30,
    'published'
);


-- =========================================================
-- 13. DỮ LIỆU MẪU - LESSONS
-- =========================================================

INSERT INTO lessons
(
    course_id,
    title,
    content,
    video_url,
    file_path,
    lesson_order
)
VALUES
(
    1,
    'Bài 1 - Giới thiệu PHP',
    'PHP là ngôn ngữ lập trình phía máy chủ. Trong bài học này chúng ta tìm hiểu PHP và cách PHP hoạt động.',
    NULL,
    NULL,
    1
),
(
    1,
    'Bài 2 - Biến và kiểu dữ liệu',
    'Tìm hiểu biến và các kiểu dữ liệu cơ bản trong PHP.',
    NULL,
    NULL,
    2
),
(
    1,
    'Bài 3 - Form trong PHP',
    'Tìm hiểu cách tạo và xử lý dữ liệu từ form bằng PHP.',
    NULL,
    NULL,
    3
),
(
    2,
    'Bài 1 - Tổng quan bảo mật Web',
    'Giới thiệu các vấn đề bảo mật web phổ biến.',
    NULL,
    NULL,
    1
),
(
    2,
    'Bài 2 - SQL Injection',
    'Tìm hiểu SQL Injection và cách phòng chống.',
    NULL,
    NULL,
    2
),
(
    3,
    'Bài 1 - Mô hình mạng',
    'Tìm hiểu mô hình và kiến trúc mạng máy tính.',
    NULL,
    NULL,
    1
);
