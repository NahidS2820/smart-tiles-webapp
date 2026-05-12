# Smart Tiles Application

Smart Tiles Application is a PHP and MySQL web system for a tile business. It supports secure login, dashboard metrics, inventory management, sales and invoices, tile estimation, and basic reports.

## Setup

1. Start Apache and MySQL in XAMPP.
2. Open `http://localhost/phpmyadmin`.
3. Import `database/schema.sql`.
4. Open `http://localhost/SMART_TILES_APPLICATION`.

Default logins:

- Admin: `admin` / `admin123`
- Staff: `staff` / `admin123`

## Security Features

- Passwords are stored with PHP `password_hash`.
- Login and module actions use sessions.
- Database writes use prepared statements.
- Forms include CSRF tokens.
- Inputs are validated and escaped before output.
- Admin-only delete control is included for inventory records.

