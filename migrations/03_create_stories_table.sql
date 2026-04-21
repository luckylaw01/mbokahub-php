CREATE TABLE IF NOT EXISTS stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    media_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255),
    caption VARCHAR(100),
    type ENUM('fundi_work', 'tvet_spotlight', 'announcement') DEFAULT 'fundi_work',
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 24 HOUR),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO stories (user_id, media_url, caption, type, is_verified) 
SELECT id, 'assets/images/story1.jpg', 'Modern Cabinet Installation', 'fundi_work', TRUE FROM users WHERE role='fundi' LIMIT 1;

INSERT INTO stories (user_id, media_url, caption, type, is_verified) 
SELECT id, 'assets/images/story2.jpg', 'Kabete Polytechnic Graduation', 'tvet_spotlight', TRUE FROM users WHERE role='admin' LIMIT 1;
