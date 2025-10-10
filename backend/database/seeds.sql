-- Admin user (password: admin123)
INSERT INTO users (name,email,student_id,password_hash,role,created_at)
VALUES
('Admin','admin@campus.local','A0001',
'$2y$10$wGHGMpXo0H9i9X6Uu4T/hOe2b7zCq0XnqG6J8n0y0xv7Zp8nT2B3a', -- bcrypt of admin123 (example)
'admin', NOW());

-- Sample rooms
INSERT INTO rooms (name,location,capacity,is_active,created_at) VALUES
('Library-101','Library 1st Floor',6,1,NOW()),
('Library-102','Library 1st Floor',4,1,NOW()),
('Science-204','Science Block 2nd',8,1,NOW());
