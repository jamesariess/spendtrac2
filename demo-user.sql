-- Demo user for spendtrackfinance DB (run in phpMyAdmin or MySQL CLI)
-- Ensure 'users' table exists with columns: id (auto), email, password

INSERT INTO users (email, password) VALUES 
('demo@spendtrack.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password_hash('demo123', PASSWORD_DEFAULT)

-- Verify:
-- SELECT id, email FROM users WHERE email = 'demo@spendtrack.com';

