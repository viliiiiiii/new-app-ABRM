# ABRM Management

Minimal PHP 8+ web application skeleton implementing the ABRM Management platform requirements. The project is organised without frameworks and uses MySQL via PDO plus MinIO-compatible storage abstractions.

## Installation
1. Copy `config/config.php` and adjust database / MinIO credentials.
2. Import `database.sql` into MySQL/MariaDB.
3. Serve the `/public` directory with PHP 8+ (e.g. Apache, Nginx + PHP-FPM, or `php -S localhost:8000 -t public`).
4. Ensure the `storage` directory is writable by the web server user.

Default login after seeding: `owner@example.com / change-me`.

## Development
- `public/index.php` is the front controller/mini router.
- Core classes live in `src/Core` and modules inside `src/Modules`.
- Templates under `templates/` use a minimal layout system.

## Tests
Manual testing recommended using `php -S localhost:8000 -t public`.
