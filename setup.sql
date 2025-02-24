-- Mail Tracker Veritabanı Kurulumu
-- @author A. Kerem Gök
CREATE DATABASE IF NOT EXISTS mail_tracker;
USE mail_tracker;
-- Admin tablosu
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Admin kullanıcı ekleme
-- Şifre: admin123
INSERT INTO admins (username, password, role)
VALUES (
        'admin',
        '$2y$10$aaDLoyB2323v7D05uIfLD.iQBa9xpUOCSkZ6cUQEsLCebnaQmZ2VG',
        'admin'
    );
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id VARCHAR(32) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    opened_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- E-posta şablonları tablosu
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    html_content TEXT NOT NULL,
    category VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Kampanya takibi tablosu
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    tracking_prefix VARCHAR(32) NOT NULL,
    start_date DATETIME,
    end_date DATETIME,
    total_sent INT DEFAULT 0,
    total_opened INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- Coğrafi konum tablosu
CREATE TABLE IF NOT EXISTS geo_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_id INT NOT NULL,
    country VARCHAR(100),
    city VARCHAR(100),
    region VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (log_id) REFERENCES email_logs(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;