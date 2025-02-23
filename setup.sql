-- Mail Tracker Veritabanı Kurulumu
-- @author A. Kerem Gök
CREATE DATABASE IF NOT EXISTS mail_tracker;
USE mail_tracker;
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id VARCHAR(32) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    opened_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Admin tablosu
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Varsayılan admin kullanıcısı (şifre: admin123)
INSERT INTO admins (username, password)
VALUES (
        'admin',
        '$2y$10$92Vq0XyWUsvHQYq0UlyYOeq.hLABXqPVYoiZBqGGGNZyDGpO9mNiO'
    );