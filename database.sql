CREATE DATABASE IF NOT EXISTS spendtrackfinance
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE spendtrackfinance;

CREATE TABLE IF NOT EXISTS user (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    description VARCHAR(120) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    category VARCHAR(40) NOT NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_transactions_user_date (user_id, transaction_date),
    CONSTRAINT fk_transactions_user
        FOREIGN KEY (user_id) REFERENCES user (user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

