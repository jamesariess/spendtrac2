# SpendTrackFinance

PHP/XAMPP expense tracking system with MySQL-backed authentication and transactions.

## Implemented application areas

- Unified reusable PHP layout and responsive sidebar in `pages/_layout.php`
- Modern shared fintech styling in `assets/css/app.css`
- Shared shell/data JavaScript in `assets/js/app.js`
- Secure session helpers, CSRF helpers, remember-me bootstrap, and JSON responses in `backend/security.php`
- Dashboard, expenses, income, analytics, reports, budget planner, transactions, notifications, settings, profile, goals, and admin pages
- MySQL-backed transactions, budgets, savings goals, notifications, subscriptions, and payment event storage
- Admin-only system overview for users, transaction volume, expenses, and subscription counts
- Stripe/PayPal billing entry points with environment-based configuration checks
- Password reset and email verification API endpoints
- CSV exports for transactions and reports
- Dark mode and collapsible/mobile sidebar

## XAMPP setup

1. Start Apache and MySQL in XAMPP.
2. Run `composer install` if `vendor/` is missing. Run `composer update stripe/stripe-php --with-dependencies` after pulling this upgrade if your lock file is older.
3. Open phpMyAdmin and import `database.sql` for a fresh install.
4. Existing installs should import `migrations/upgrade_2026_05_17.sql` once.
5. Optional: import `demo-user.sql` for the demo account.
6. Configure mail OTP credentials as environment variables before using production email OTP:
   - `SPENDTRACK_SMTP_USER`
   - `SPENDTRACK_SMTP_PASS`
7. Optional billing environment variables:
   - `STRIPE_SECRET_KEY`
   - `STRIPE_PREMIUM_PRICE_ID`
   - `PAYPAL_CLIENT_ID`
   - `PAYPAL_CLIENT_SECRET`
8. Open `http://localhost/spendtrac2/auth/login.html`.

On localhost, if SMTP is not configured, the OTP is displayed on the OTP screen for testing.

## Main backend pieces

- `backend/conn.php` creates the PDO MySQL connection.
- `backend/security.php` contains session, JSON response, auth, CSRF, and input helpers.
- `api/session.php` returns the active session user and CSRF token.
- `api/finance.php` handles dashboard overview, transactions, budgets, goals, profile settings, notifications, and admin data.
- `api/account.php` handles password reset and email verification tokens.
- `api/billing.php` starts provider-specific billing flows when configured.
- `api/webhooks.php` stores Stripe/PayPal webhook events.
- `api/transactions.php` remains for backward compatibility.
- `api/logout.php` destroys the authenticated session.

## Deployment notes

- Use a real domain with HTTPS.
- Configure SMTP and payment environment variables on the server, never in source code.
- Disable PHP display errors and route errors to server logs.
- Import the database schema before first login.
- Point the web root to this project or protect non-public folders with web server rules.
- Use strong database credentials instead of the local XAMPP root account.

## Future improvements

- Add automated PHPUnit/API tests.
- Add true PDF export through a server-side PDF package.
- Add receipt file upload storage policies and virus scanning.
- Add full localization files for translated UI copy.
- Add background jobs for monthly summaries and recurring transaction generation.
