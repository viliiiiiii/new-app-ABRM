CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role_key VARCHAR(50) NOT NULL UNIQUE
);
CREATE TABLE sectors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    status ENUM('active','suspended') DEFAULT 'active'
);
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT,
    role_key VARCHAR(50) DEFAULT 'user',
    sector_id INT,
    status ENUM('active','suspended') DEFAULT 'active',
    theme_preference VARCHAR(20) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (sector_id) REFERENCES sectors(id)
);
CREATE TABLE effective_permissions (
    user_id INT NOT NULL,
    permission_key VARCHAR(100) NOT NULL
);
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(64),
    success TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(64),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE lost_and_found_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    status VARCHAR(50) DEFAULT 'new',
    location VARCHAR(120),
    description TEXT,
    found_at DATE,
    created_by INT,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE taxi_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_time DATETIME,
    start_location VARCHAR(120),
    destination VARCHAR(120),
    guest_name VARCHAR(120),
    room_number VARCHAR(30),
    driver_name VARCHAR(120),
    price DECIMAL(10,2),
    deleted_at TIMESTAMP NULL
);
CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(80),
    name VARCHAR(160),
    location VARCHAR(120),
    quantity_on_hand INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'active',
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE doctor_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(30),
    time_called DATETIME,
    doctor_name VARCHAR(120),
    reason TEXT,
    status VARCHAR(50),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    title VARCHAR(255),
    body TEXT,
    note_type VARCHAR(50) DEFAULT 'Personal',
    deleted_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message VARCHAR(255),
    link VARCHAR(255),
    type VARCHAR(20) DEFAULT 'info',
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(50),
    module VARCHAR(80),
    record_id INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE sector_supervisors (
    sector_id INT,
    user_id INT
);

INSERT INTO roles (name, role_key) VALUES
('Application Owner', 'app_owner'),
('Administrator', 'admin'),
('Standard User', 'user');
INSERT INTO sectors (name) VALUES ('Front Desk'), ('Security');
INSERT INTO users (name, email, password_hash, role_id, role_key, sector_id)
VALUES ('Owner', 'owner@example.com', '$2y$12$jtPEmPvLDkfpL9zPQTQOhO6/JxSY.mQi1wfGn3q7UvLqxZWu.pRc6', 1, 'app_owner', 1);
