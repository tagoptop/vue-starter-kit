# Deployment Checklist

## Before Publish

1. Copy `.env.production.example` to your server as `.env`.
2. Set a real `APP_URL` and correct MySQL database credentials.
3. Generate an application key:
   ```bash
   php artisan key:generate
   ```
4. Confirm `APP_ENV=production` and `APP_DEBUG=false`.
5. If you use HTTPS, keep `SESSION_SECURE_COOKIE=true`.

## Server Requirements

1. PHP 8.2 or newer.
2. Required PHP extensions for Laravel and your selected database.
3. A working MySQL-compatible database if `DB_CONNECTION=mysql` is used.
4. Node.js and npm available during build time.

## First-Time Deploy

1. Install backend dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. Install frontend dependencies and build assets:
   ```bash
   npm ci
   npm run build
   ```
3. Run database migrations:
   ```bash
   php artisan migrate --force
   ```
4. Create the public storage symlink:
   ```bash
   php artisan storage:link
   ```
5. Cache the framework for production:
   ```bash
   php artisan optimize
   ```

## Project-Specific Notes

1. This project stores sessions and cache in the database by default. Make sure the migrations for `sessions` and cache tables are applied before going live.
2. Messenger attachments and proof-of-delivery uploads use the public storage disk. Confirm that `public/storage` is linked and writable.
3. Delivery tracking fields were added through Laravel migrations and also have a MySQL fallback script at `database/migrations/2026_07_12_delivery_tracking_fields.mysql.sql` if direct SQL application is required.
4. Do not publish your real `.env` file or database credentials.
5. The current local `.env` shown in development is not production-safe because it mixes MySQL connection settings with a SQLite database path.

## Post-Deploy Smoke Checks

1. Log in as admin, staff, and customer.
2. Confirm order placement works.
3. Confirm delivery monitoring updates save correctly.
4. Confirm customer messenger access and one-click support chat work.
5. Confirm uploaded files open from the browser.

## Go-Live Verification

Run this on the target server after deploy:

```bash
bash scripts/verify_release.sh
```

The script checks:

1. Laravel can boot and read Artisan.
2. Database migrations are reachable and have no pending entries.
3. `public/storage` exists as a working symlink.
4. A queue worker or Horizon process is running when the queue connection is asynchronous.