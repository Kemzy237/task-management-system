CREATE DATABASE IF NOT EXISTS task_management_db;
USE task_management_db;

-- DEPARTMENT first (referenced by users)
CREATE TABLE department(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- USERS
CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    contact VARCHAR(10) NOT NULL,
    department INT,
    FOREIGN KEY (department) REFERENCES department(id)
);

-- TASKS
CREATE TABLE tasks(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    assigned_to INT,
    due_date DATE,
    status ENUM('pending','in_progress','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- NOTIFICATIONS
CREATE TABLE notifications(
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    recipient INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (recipient) REFERENCES users(id)
);

-- DEPARTMENT MEMBERS
CREATE TABLE departmentMembers(
    department_id INT,
    user_id INT,
    role ENUM('admin', 'employee') NOT NULL,
    PRIMARY KEY (department_id, user_id),
    FOREIGN KEY (department_id) REFERENCES department(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `created_at`, `contact`) VALUES (NULL, 'kemzy wil', 'kemzy', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'admin', current_timestamp(), '653426838');

