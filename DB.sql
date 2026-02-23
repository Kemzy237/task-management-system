CREATE DATABASE task_management_db;
--- USER
CREATE TABLE users(
    id int auto_increment primary key,
    full_name varchar(50) not null,
    username varchar(50) not null,
    password varchar(100) not null,
    role ENUM('admin', 'employee') not null,
    created_at timestamp default current_timestamp,
    contact varchar(10) not null,
    department int,
    foreign key (department) references department(id);
);
--- TASK
CREATE TABLE tasks(
    id INT auto_increment primary key,
    title varchar(100) not null,
    description text,
    assigned_to int,
    due_date date,
    status ENUM('pending','in_progress','completed') default 'pending',
    created_at timestamp default current_timestamp,
    foreign key (assigned_to) references users(id)
);
--- NOTIFICATIONS
CREATE TABLE notifications(
    id INT auto_increment primary key,
    message TEXT not null,
    recipient INT not null,
    type varchar(50) not null,
    date timestamp default current_timestamp,
    is_read boolean default FALSE
);
--- DEPARTMENT
CREATE TABLE department(
    id INT auto_increment primary key,
    name VARCHAR(100) NOT NULL,
    created_at timestamp default current_timestamp
);
--- DEPARTMENT MEMBERS
CREATE TABLE departmentMembers(
    department_id INT,
    user_id INT,
    role ENUM('admin', 'employee') not null,
    primary key (department_id,user_id),
    foreign key (department_id) references department(id),
    foreign key (user_id) references users(id)
);

INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `created_at`, `contact`) VALUES (NULL, 'kemzy wil', 'kemzy', '$2y$10$JCp30cpY3Cc2cl2Y6aefOe.1HTW9ZYRT1iQ2GTuSpoBww0e.l2bxy', 'admin', current_timestamp(), '653426838');

