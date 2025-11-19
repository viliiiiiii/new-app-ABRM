CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role_key VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255)
);

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE user_permissions_override (
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    allow TINYINT(1) DEFAULT 1,
    PRIMARY KEY (user_id, permission_id)
);

CREATE TABLE sectors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    status ENUM('active','suspended') DEFAULT 'active'
);

CREATE TABLE sector_supervisors (
    sector_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (sector_id, user_id)
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
    theme_preset VARCHAR(40) DEFAULT 'default',
    language VARCHAR(10) DEFAULT 'en',
    date_format VARCHAR(20) DEFAULT 'Y-m-d',
    time_format VARCHAR(20) DEFAULT 'H:i',
    profile_picture_path VARCHAR(255),
    failed_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(120) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    type ENUM('info','warning','success','error') DEFAULT 'info',
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE saved_filters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    filters_json JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cms_theme_presets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    preset_key VARCHAR(50) UNIQUE,
    name VARCHAR(120) NOT NULL,
    primary_color VARCHAR(7) DEFAULT '#1f6feb',
    accent_color VARCHAR(7) DEFAULT '#f7d560',
    background_color VARCHAR(7) DEFAULT '#ffffff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cms_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logo_path VARCHAR(255),
    login_logo_path VARCHAR(255),
    font_scale DECIMAL(3,2) DEFAULT 1.00,
    maintenance_hooked_at DATETIME NULL,
    last_backup_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE system_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_text VARCHAR(255),
    message_type ENUM('info','warning','danger') DEFAULT 'info',
    is_enabled TINYINT DEFAULT 0,
    login_message VARCHAR(255)
);

CREATE TABLE lost_and_found_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(36) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    tags TEXT,
    lifecycle_state ENUM('new','under_review','stored','pending_release','released','archived') DEFAULT 'new',
    status VARCHAR(50) DEFAULT 'open',
    location_area VARCHAR(120),
    location_building VARCHAR(120),
    location_floor VARCHAR(50),
    location_exact VARCHAR(150),
    owner_name VARCHAR(120),
    owner_contact VARCHAR(120),
    owner_status VARCHAR(80) DEFAULT 'unknown',
    reminder_date DATE NULL,
    retention_date DATE NULL,
    high_value TINYINT DEFAULT 0,
    sensitive_document TINYINT DEFAULT 0,
    description TEXT,
    notes TEXT,
    found_at DATETIME,
    released_at DATETIME,
    created_by INT,
    updated_by INT,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE lost_and_found_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    is_sensitive TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lost_and_found_states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    state VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    notes TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lost_and_found_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    field VARCHAR(50) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lost_and_found_release_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    recipient_name VARCHAR(120) NOT NULL,
    recipient_id VARCHAR(120),
    recipient_contact VARCHAR(120),
    staff_name VARCHAR(120) NOT NULL,
    staff_signature_path VARCHAR(255),
    recipient_signature_path VARCHAR(255),
    release_pdf_path VARCHAR(255),
    released_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    notes TEXT,
    created_by INT,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(80),
    name VARCHAR(160),
    category VARCHAR(120),
    location VARCHAR(120),
    quantity_on_hand INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    max_stock INT DEFAULT 0,
    condition ENUM('new','used','damaged') DEFAULT 'new',
    status ENUM('active','in_use','in_repair','scrapped','archived') DEFAULT 'active',
    qr_code_path VARCHAR(255),
    notes TEXT,
    deleted_at DATETIME NULL,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE inventory_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    movement_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    from_location VARCHAR(120),
    to_location VARCHAR(120),
    quantity_moved INT NOT NULL,
    moved_by INT,
    reason VARCHAR(120),
    issued_signature_path VARCHAR(255),
    received_signature_path VARCHAR(255),
    notes TEXT
);

CREATE TABLE stocktake_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    location VARCHAR(120) NOT NULL,
    session_date DATE NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE stocktake_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    item_id INT NOT NULL,
    expected_quantity INT DEFAULT 0,
    counted_quantity INT DEFAULT 0,
    variance INT DEFAULT 0,
    notes TEXT
);

CREATE TABLE doctor_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(30),
    time_called DATETIME,
    time_arrived DATETIME,
    doctor_name VARCHAR(120),
    reason TEXT,
    status ENUM('open','closed') DEFAULT 'open',
    deleted_at DATETIME NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    title VARCHAR(255),
    body TEXT,
    note_type ENUM('Personal','Team','Task','Reminder') DEFAULT 'Personal',
    reminder_at DATETIME NULL,
    pinned TINYINT DEFAULT 0,
    is_favourite TINYINT DEFAULT 0,
    tags VARCHAR(255),
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE note_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE note_checklist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    is_done TINYINT DEFAULT 0,
    position INT DEFAULT 0
);

CREATE TABLE note_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    author_id INT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
);

CREATE TABLE note_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT NULL,
    sector_id INT NULL,
    share_type ENUM('user','sector') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE note_mentions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    mentioned_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE note_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY note_user (note_id, user_id)
);

CREATE TABLE note_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    checklist_json JSON,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(50),
    module VARCHAR(80),
    record_id INT,
    description TEXT,
    before_state JSON,
    after_state JSON,
    ip VARCHAR(64),
    severity ENUM('info','warning','critical') DEFAULT 'info',
    alert_flag TINYINT DEFAULT 0,
    alert_message VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (name, role_key) VALUES
('Application Owner', 'app_owner'),
('Administrator', 'admin'),
('Standard User', 'user');

INSERT INTO permissions (permission_key, description) VALUES
('lostandfound.view', 'View Lost & Found items'),
('lostandfound.create', 'Create Lost & Found items'),
('lostandfound.manage', 'Manage Lost & Found states'),
('inventory.manage', 'Manage inventory'),
('notes.manage', 'Manage notes'),
('taxilog.manage', 'Manage taxi log'),
('doctorlog.manage', 'Manage doctor log'),
('users.manage', 'Manage users'),
('sectors.manage', 'Manage sectors'),
('activity.view', 'View activity logs'),
('cms.manage', 'Manage CMS settings');

INSERT INTO role_permissions (role_id, permission_id)
SELECT 1 AS role_id, id FROM permissions;

INSERT INTO role_permissions (role_id, permission_id)
SELECT 2 AS role_id, id FROM permissions WHERE permission_key NOT IN ('cms.manage');

INSERT INTO sectors (name) VALUES ('Front Desk'), ('Security'), ('Operations');

INSERT INTO users (name, email, password_hash, role_id, role_key, sector_id)
VALUES ('Owner', 'owner@example.com', '$2y$12$jtPEmPvLDkfpL9zPQTQOhO6/JxSY.mQi1wfGn3q7UvLqxZWu.pRc6', 1, 'app_owner', 1);

INSERT INTO cms_theme_presets (preset_key, name, primary_color, accent_color, background_color)
VALUES ('default', 'Default', '#1f6feb', '#f7d560', '#ffffff'),
('contrast', 'High contrast', '#000000', '#ffcc00', '#ffffff');

INSERT INTO system_messages (message_text, message_type, is_enabled, login_message)
VALUES ('System running in demo mode', 'info', 0, 'Welcome to ABRM Management');
