-- =========================================
-- Database schema & procedures for CRM
-- Soft delete strategy with views
-- =========================================

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- CONTACTS TABLE
CREATE TABLE IF NOT EXISTS contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(25),
  company VARCHAR(150),
  tags JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY unique_email_active (email, deleted_at),
  INDEX idx_contacts_search (name, email, company),
  INDEX idx_contacts_deleted (deleted_at)
) ENGINE=InnoDB;

-- AUDIT LOGS TABLE
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

-- =========================================
-- VIEWS
-- =========================================

-- Active contacts (not deleted)
CREATE OR REPLACE VIEW active_contacts AS
SELECT * FROM contacts WHERE deleted_at IS NULL;

-- Deleted contacts (soft-deleted)
CREATE OR REPLACE VIEW deleted_contacts AS
SELECT * FROM contacts WHERE deleted_at IS NOT NULL;

-- =========================================
-- STORED PROCEDURES
-- =========================================
DELIMITER //

-- Create a contact (with unique email check)
CREATE PROCEDURE CreateContact (
  IN p_name VARCHAR(120),
  IN p_email VARCHAR(190),
  IN p_phone VARCHAR(25),
  IN p_company VARCHAR(150),
  IN p_tags JSON
)
BEGIN
  IF EXISTS (SELECT 1 FROM contacts WHERE email = p_email AND deleted_at IS NULL) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists';
  ELSE
    INSERT INTO contacts (name, email, phone, company, tags)
    VALUES (p_name, p_email, p_phone, p_company, p_tags);
  END IF;
END//

-- Update a contact (with unique email check excluding itself)
CREATE PROCEDURE UpdateContact (
  IN p_id INT,
  IN p_name VARCHAR(120),
  IN p_email VARCHAR(190),
  IN p_phone VARCHAR(25),
  IN p_company VARCHAR(150),
  IN p_tags JSON
)
BEGIN
  IF EXISTS (SELECT 1 FROM contacts WHERE email = p_email AND deleted_at IS NULL AND id != p_id) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Email already exists';
  ELSE
    UPDATE contacts
    SET name = p_name,
        email = p_email,
        phone = p_phone,
        company = p_company,
        tags = p_tags
    WHERE id = p_id;
  END IF;
END//

-- Soft delete a contact
CREATE PROCEDURE SoftDeleteContact (
  IN p_id INT
)
BEGIN
  UPDATE contacts
  SET deleted_at = NOW()
  WHERE id = p_id;
END//

DELIMITER ;
