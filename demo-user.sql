-- Demo user for spendtrackfinance DB (run database.sql first in phpMyAdmin or MySQL CLI)
-- Password: demo123

INSERT INTO user (email, password) VALUES
('demo@spendtrack.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Verify:
-- SELECT user_id, email FROM user WHERE email = 'demo@spendtrack.com';
