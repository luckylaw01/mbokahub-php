ALTER TABLE jobs ADD COLUMN title VARCHAR(255) NOT NULL AFTER category_id;
ALTER TABLE jobs ADD COLUMN urgency ENUM('standard', 'emergency') DEFAULT 'standard' AFTER budget_expectation;
ALTER TABLE jobs CHANGE COLUMN budget_expectation budget_range VARCHAR(100);
ALTER TABLE jobs CHANGE COLUMN hirer_id user_id INT(11) NOT NULL;
