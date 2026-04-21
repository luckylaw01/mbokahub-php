-- Add location and bio to users/profiles if needed (assuming from previous context users table has basic info)
-- This migration focuses on the dual-feed data: Jobs for Fundis and Recommended Fundis for Hirers.

-- 1. Create Jobs table (Main feed for Fundis)
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hirer_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(100) NOT NULL,
    budget_range VARCHAR(50), -- e.g. "1000-5000"
    urgency ENUM('standard', 'emergency') DEFAULT 'standard',
    status ENUM('open', 'assigned', 'completed', 'cancelled') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hirer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Seed some jobs
INSERT INTO jobs (hirer_id, category_id, title, description, location, budget_range, urgency)
SELECT id, 1, 'Fix Kitchen Sink Leak', 'The pipe under my sink is leaking water everywhere.', 'Westlands, Nairobi', '1500-2500', 'emergency'
FROM users WHERE role='hirer' LIMIT 1;

INSERT INTO jobs (hirer_id, category_id, title, description, location, budget_range, urgency)
SELECT id, 2, 'Install 3 Outdoor Lights', 'Need new security lights installed in the backyard.', 'Syokimau, Machakos', '3000-5000', 'standard'
FROM users WHERE role='hirer' LIMIT 1;

-- 3. Seed some dummy fundi profile data (assuming fundi_profiles exists from earlier schema)
-- If not, we'll use a simple query to fetch users with role='fundi'
