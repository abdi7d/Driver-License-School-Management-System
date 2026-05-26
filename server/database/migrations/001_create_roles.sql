-- 001_create_roles.sql
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL
);

INSERT INTO roles (name, description) VALUES
    ('admin', 'System administrator'),
    ('manager', 'School manager'),
    ('instructor', 'Driving instructor'),
    ('supervisor', 'Supervisor'),
    ('student', 'Learner driver');
