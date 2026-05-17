USE spendtrackfinance;

ALTER TABLE transactions
    ADD COLUMN due_date DATE NULL,
    ADD COLUMN payment_status ENUM('unpaid', 'paid', 'scheduled') NOT NULL DEFAULT 'unpaid',
    ADD COLUMN reminder_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN reminder_days_before TINYINT UNSIGNED NOT NULL DEFAULT 3,
    ADD COLUMN reminder_last_sent_at DATETIME NULL;

