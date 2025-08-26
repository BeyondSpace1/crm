-- Create CRM Database and Tables
CREATE DATABASE IF NOT EXISTS crm;
USE crm;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(25),
    company VARCHAR(150),
    tags JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
) ENGINE=InnoDB;

-- Create indexes for performance
CREATE INDEX idx_contacts_email ON contacts(email);
CREATE INDEX idx_contacts_search ON contacts(name, email, company);
CREATE INDEX idx_contacts_deleted ON contacts(deleted_at);

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ts DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actor_id INT NOT NULL,
    actor_email VARCHAR(190) NOT NULL,
    action ENUM('create','update','delete') NOT NULL,
    entity VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    changes JSON NULL
) ENGINE=InnoDB;

-- Seed admin user (password: Admin@123)
-- Generate the hash using: echo password_hash('Admin@123', PASSWORD_DEFAULT);
INSERT IGNORE INTO users (name, email, password, role) 
VALUES ('Admin', 'admin@example.com', '$2y$10$YourGeneratedHashHere', 'admin');

-- Sample users for testing
-- INSERT IGNORE INTO users (name, email, password, role) VALUES
-- ('Editor User', 'editor@example.com', '$2y$10$YourGeneratedHashHere', 'editor'),
-- ('Viewer User', 'viewer@example.com', '$2y$10$YourGeneratedHashHere', 'viewer');

-- Sample contacts for testing
INSERT IGNORE INTO contacts (name, email, phone, company, tags) VALUES
('John Doe', 'john@example.com', '+1-555-0123', 'Acme Corp', '["customer", "vip"]'),
('Jane Smith', 'jane@example.com', '+1-555-0124', 'Tech Solutions', '["lead", "potential"]'),
('Bob Johnson', 'bob@example.com', '+1-555-0125', 'Global Industries', '["partner"]');