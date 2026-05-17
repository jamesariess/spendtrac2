USE spendtrackfinance;

ALTER TABLE user
    ADD COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    ADD COLUMN display_name VARCHAR(120) NULL,
    ADD COLUMN currency CHAR(3) NOT NULL DEFAULT 'USD',
    ADD COLUMN locale VARCHAR(12) NOT NULL DEFAULT 'en-US',
    ADD COLUMN timezone VARCHAR(64) NOT NULL DEFAULT 'UTC',
    ADD COLUMN email_verified_at TIMESTAMP NULL,
    ADD COLUMN remember_token VARCHAR(100) NULL;

ALTER TABLE transactions
    ADD COLUMN currency CHAR(3) NOT NULL DEFAULT 'USD',
    ADD COLUMN payment_method VARCHAR(40) NULL,
    ADD COLUMN notes TEXT NULL,
    ADD COLUMN receipt_path VARCHAR(255) NULL,
    ADD COLUMN is_recurring TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN recurring_interval ENUM('weekly', 'monthly', 'yearly') NULL,
    ADD COLUMN due_date DATE NULL,
    ADD COLUMN payment_status ENUM('unpaid', 'paid', 'scheduled') NOT NULL DEFAULT 'unpaid',
    ADD COLUMN reminder_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN reminder_days_before TINYINT UNSIGNED NOT NULL DEFAULT 3,
    ADD COLUMN reminder_last_sent_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS budgets (
    budget_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category VARCHAR(40) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    period ENUM('monthly', 'quarterly', 'yearly') NOT NULL DEFAULT 'monthly',
    starts_on DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_budgets_user (user_id, category),
    CONSTRAINT fk_budgets_user FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS goals (
    goal_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    target_amount DECIMAL(12, 2) NOT NULL,
    saved_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    target_date DATE NULL,
    status ENUM('active', 'completed', 'paused') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT NOT NULL,
    type VARCHAR(40) NOT NULL DEFAULT 'info',
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user_read (user_id, read_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
    subscription_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    provider ENUM('stripe', 'paypal') NOT NULL,
    provider_customer_id VARCHAR(191) NULL,
    provider_subscription_id VARCHAR(191) NULL,
    plan_name VARCHAR(80) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'inactive',
    current_period_end DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_events (
    payment_event_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('stripe', 'paypal') NOT NULL,
    event_id VARCHAR(191) NOT NULL,
    event_type VARCHAR(120) NOT NULL,
    payload JSON NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payment_events_provider_event (provider, event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(191) PRIMARY KEY,
    token_hash VARCHAR(64) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    user_id INT UNSIGNED PRIMARY KEY,
    token_hash VARCHAR(64) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_email_verification_user FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
