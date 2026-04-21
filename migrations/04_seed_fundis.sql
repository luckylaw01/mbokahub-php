SET @fundi1 = (SELECT id FROM users WHERE role='fundi' LIMIT 1);
SET @fundi2 = (SELECT id FROM users WHERE role='fundi' LIMIT 1 OFFSET 1);

INSERT INTO fundi_profiles (user_id, category_id, specialization, bio, location, rating) 
VALUES (@fundi1, 1, 'Master Plumber', 'Experienced in modern home plumbing and drainage systems.', 'Eldoret', 4.8);

INSERT INTO fundi_profiles (user_id, category_id, specialization, bio, location, rating) 
VALUES (@fundi2, 2, 'Senior Electrician', 'Expert in industrial and residential electrical wiring.', 'Voi', 4.9);
