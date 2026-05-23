-- Driver License School Management System
-- MySQL 8+ schema for local development

CREATE DATABASE IF NOT EXISTS driver_license_school
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE driver_license_school;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  full_name VARCHAR(220) GENERATED ALWAYS AS (TRIM(CONCAT(first_name, ' ', last_name))) STORED,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student', 'instructor', 'supervisor', 'manager', 'admin') NOT NULL DEFAULT 'student',
  status ENUM('pending', 'active', 'inactive', 'blocked') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_details (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  national_id VARCHAR(80) NULL,
  date_of_birth DATE NULL,
  region VARCHAR(100) NULL,
  city VARCHAR(100) NULL,
  address VARCHAR(255) NULL,
  license_class VARCHAR(30) NULL,
  experience_level VARCHAR(50) NULL,
  enrollment_status ENUM('pending', 'active', 'graduated', 'dropped') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_details_user (user_id),
  CONSTRAINT fk_student_details_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS instructor_details (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  license_number VARCHAR(80) NOT NULL,
  experience_years INT NOT NULL DEFAULT 0,
  specialization VARCHAR(120) NULL,
  availability ENUM('available', 'busy', 'on_leave') NOT NULL DEFAULT 'available',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_instructor_details_user (user_id),
  UNIQUE KEY uq_instructor_license_number (license_number),
  CONSTRAINT fk_instructor_details_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS training_programs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  theory_hours INT NOT NULL DEFAULT 0,
  practical_hours INT NOT NULL DEFAULT 0,
  fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_training_programs_created_by (created_by),
  CONSTRAINT fk_training_programs_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS enrollments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_user_id INT UNSIGNED NOT NULL,
  program_id INT UNSIGNED NOT NULL,
  assigned_instructor_id INT UNSIGNED NULL,
  assigned_supervisor_id INT UNSIGNED NULL,
  start_date DATE NULL,
  enrollment_date DATE NULL,
  status ENUM('pending', 'active', 'graduated', 'suspended', 'cancelled') NOT NULL DEFAULT 'active',
  progress_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_enrollments_student (student_user_id),
  INDEX idx_enrollments_program (program_id),
  INDEX idx_enrollments_status (status),
  INDEX idx_enrollments_instructor (assigned_instructor_id),
  INDEX idx_enrollments_supervisor (assigned_supervisor_id),
  CONSTRAINT fk_enrollments_student
    FOREIGN KEY (student_user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_enrollments_program
    FOREIGN KEY (program_id) REFERENCES training_programs(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_enrollments_instructor
    FOREIGN KEY (assigned_instructor_id) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_enrollments_supervisor
    FOREIGN KEY (assigned_supervisor_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lessons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT UNSIGNED NOT NULL,
  instructor_id INT UNSIGNED NOT NULL,
  session_date DATETIME NOT NULL,
  lesson_type ENUM('theory', 'practical') NOT NULL DEFAULT 'practical',
  duration_minutes INT NOT NULL DEFAULT 60,
  attendance TINYINT(1) NOT NULL DEFAULT 0,
  performance_score DECIMAL(5,2) NULL,
  notes TEXT NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lessons_enrollment (enrollment_id),
  INDEX idx_lessons_instructor (instructor_id),
  INDEX idx_lessons_session_date (session_date),
  CONSTRAINT fk_lessons_enrollment
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_lessons_instructor
    FOREIGN KEY (instructor_id) REFERENCES users(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_lessons_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_user_id INT UNSIGNED NOT NULL,
  exam_type ENUM('theory', 'practical') NOT NULL,
  scheduled_date DATETIME NOT NULL,
  status ENUM('scheduled', 'passed', 'failed', 'cancelled') NOT NULL DEFAULT 'scheduled',
  score DECIMAL(5,2) NULL,
  result_date DATETIME NULL,
  conducted_by INT UNSIGNED NULL,
  approved TINYINT(1) NOT NULL DEFAULT 0,
  passed TINYINT(1) GENERATED ALWAYS AS (CASE WHEN status = 'passed' THEN 1 ELSE 0 END) STORED,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_exams_student (student_user_id),
  INDEX idx_exams_status (status),
  INDEX idx_exams_exam_type (exam_type),
  INDEX idx_exams_approved (approved),
  CONSTRAINT fk_exams_student
    FOREIGN KEY (student_user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_exams_conducted_by
    FOREIGN KEY (conducted_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS certificates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_user_id INT UNSIGNED NOT NULL,
  program_id INT UNSIGNED NULL,
  certificate_number VARCHAR(80) NOT NULL UNIQUE,
  issue_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  issued_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_certificates_student (student_user_id),
  INDEX idx_certificates_program (program_id),
  CONSTRAINT fk_certificates_student
    FOREIGN KEY (student_user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_certificates_program
    FOREIGN KEY (program_id) REFERENCES training_programs(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_certificates_issued_by
    FOREIGN KEY (issued_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notifications_user_created (user_id, created_at),
  INDEX idx_notifications_user_read (user_id, is_read),
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  document_type VARCHAR(60) NOT NULL DEFAULT 'other',
  file_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_documents_user (user_id),
  CONSTRAINT fk_documents_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed training programs
INSERT IGNORE INTO training_programs (name, theory_hours, practical_hours, fee) VALUES
  ('Level 1 - Motorcycle', 20, 30, 3500.00),
  ('Level 2 - Private Car', 25, 40, 5500.00),
  ('Level 3 - Heavy Truck', 35, 55, 8500.00),
  ('Level 4 - People Transportation', 30, 50, 7800.00),
  ('Level 5 - Bus Driver', 40, 60, 9200.00);
