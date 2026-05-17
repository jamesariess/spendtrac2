# SpendTrackFinance

PHP/XAMPP expense tracking system with MySQL-backed authentication and transactions.

## XAMPP setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin and import `database.sql`.
3. Optional: import `demo-user.sql` for the demo account.
4. Configure mail OTP credentials as environment variables before using login OTP:
   - `SPENDTRACK_SMTP_USER`
   - `SPENDTRACK_SMTP_PASS`
5. Open `http://localhost/spendtrac2/auth/login.html`.

## Main backend pieces

- `backend/conn.php` creates the PDO MySQL connection.
- `backend/security.php` contains session, JSON response, auth, CSRF, and input helpers.
- `api/session.php` returns the active session user and CSRF token.
- `api/transactions.php` handles transaction list, add, update, and delete.
- `api/logout.php` destroys the authenticated session.
