-- Create admin user
INSERT INTO users (username, email, password, role, status, api_token, created, modified)
VALUES ('admin', 'admin@example.com', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOP', 'admin', 1, 'admin_token_123', NOW(), NOW());
