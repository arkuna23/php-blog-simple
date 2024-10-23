CREATE DATABASE website;

USE website;

CREATE TABLE tb_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password CHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 插入示例数据
INSERT INTO tb_users (username, password) VALUES
('user1', SHA2('password1', 256)),
('user2', SHA2('password2', 256)),
('admin', SHA2('adminpassword', 256));

CREATE TABLE tb_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE USER 'server'@'localhost' IDENTIFIED BY '134679852';
GRANT ALL PRIVILEGES ON website.* TO 'server'@'localhost';
FLUSH PRIVILEGES;
