DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(32) UNIQUE NOT NULL, 
    fullname VARCHAR(64) NOT NULL, 
    email VARCHAR(128),
    phone VARCHAR(16),

    -- BCrypt always generates 60 characters long but leave space if PASSWORD_DEFAULT changes 
    pw_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- INSERT INTO Users VALUES (...);