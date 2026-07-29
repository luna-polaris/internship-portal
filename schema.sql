-- ============================================================
-- Industrial Internship Matching Portal — Database Schema
-- ============================================================
-- This file is a REFERENCE SNAPSHOT of the current database structure,
-- generated from the live MySQL database (mysqldump --no-data).
--
-- It is NOT the primary way to set up the database. The source of truth
-- is the migration files in laravel/database/migrations/ — running
--
--     php artisan migrate
--
-- recreates every table below automatically (including the Laravel
-- framework tables at the bottom) and is the recommended setup path.
-- See the project README.md for full setup instructions.
--
-- This file is only useful if you want to import the schema by hand
-- (e.g. via phpMyAdmin) instead of running migrations.
-- ============================================================

CREATE DATABASE IF NOT EXISTS internship_portal
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;
USE internship_portal;

-- ------------------------------------------------------------
-- Core application tables
-- ------------------------------------------------------------

CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    username VARCHAR(50),                      -- only used by Admin accounts (see admin login)
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(255),              -- storage path, e.g. avatars/xxxx.jpg
    role ENUM('Student','Employer','Admin') NOT NULL,
    status ENUM('Pending','Active','Inactive') NOT NULL DEFAULT 'Pending',
    token VARCHAR(64),                         -- email verification / password reset token
    token_expires_at DATETIME,
    remember_token VARCHAR(100),               -- Laravel's Authenticatable trait

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY users_email_unique (email),
    UNIQUE KEY users_username_unique (username)
);

CREATE TABLE students (
    student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    matric_no VARCHAR(20) NOT NULL,
    university VARCHAR(100),
    faculty VARCHAR(100),
    programme VARCHAR(100),
    cgpa DECIMAL(3,2),
    graduation_year YEAR,
    resume VARCHAR(255),

    UNIQUE KEY students_user_id_unique (user_id),
    UNIQUE KEY students_matric_no_unique (matric_no),
    CONSTRAINT students_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

CREATE TABLE employers (
    employer_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    position VARCHAR(100),
    department VARCHAR(100),

    UNIQUE KEY employers_user_id_unique (user_id),
    CONSTRAINT employers_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

CREATE TABLE admins (
    admin_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    admin_level ENUM('Super Admin','Moderator') NOT NULL DEFAULT 'Moderator',

    UNIQUE KEY admins_user_id_unique (user_id),
    CONSTRAINT admins_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

CREATE TABLE companies (
    company_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id INT UNSIGNED NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    registration_no VARCHAR(50),
    industry VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postcode VARCHAR(10),
    website VARCHAR(150),
    company_email VARCHAR(100),
    company_phone VARCHAR(20),
    description TEXT,
    logo VARCHAR(255),

    UNIQUE KEY companies_employer_id_unique (employer_id),
    CONSTRAINT companies_employer_id_foreign
        FOREIGN KEY (employer_id) REFERENCES employers(employer_id)
        ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Laravel framework tables
-- Created automatically by `php artisan migrate` — listed here only
-- for reference, you should not need to create these by hand.
-- ------------------------------------------------------------

-- Sanctum API tokens (issued on login, used for Bearer-token auth on /api/*)
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY personal_access_tokens_token_unique (token),
    KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id),
    KEY personal_access_tokens_expires_at_index (expires_at)
);

-- Web session storage (SESSION_DRIVER=database)
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,

    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
);

-- Cache storage (CACHE_STORE=database)
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,

    KEY cache_expiration_index (expiration)
);

CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,

    KEY cache_locks_expiration_index (expiration)
);

-- Queue storage (QUEUE_CONNECTION=database)
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,

    KEY jobs_queue_index (queue)
);

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT,
    cancelled_at INT,
    created_at INT NOT NULL,
    finished_at INT
);

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY failed_jobs_uuid_unique (uuid)
);

-- Unused (kept for Laravel's default password-reset flow); this project
-- implements its own reset via users.token / users.token_expires_at instead.
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- Tracks which migrations have run
CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
);
