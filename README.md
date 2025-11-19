# ABRM Management

ABRM Management is a plain PHP 8+ intranet application that fulfils the functional brief for handling lost & found, taxi log, inventory, doctor log, notes/collaboration, user/sector management, CMS settings and the supporting utilities (audit, notifications, saved filters, exports, MinIO uploads, etc.).

## Highlights
- Modular folder layout (`src/Modules/*`) with small controllers, models, and templates.
- PDO + prepared statements everywhere and centralised CSRF, auth, permissions, notifications, logging, and MinIO helpers.
- Light/Dark/high-contrast themeing per user including a toggle stored in DB + localStorage.
- REST-style JSON API under `/public/api/index.php` mirroring the UI actions.
- Built-in PDF/Excel stubs (ready to be swapped with TCPDF/PhpSpreadsheet) and quick export buttons on the key grids.
- Global search (Ctrl+K) indexing the major modules + module level saved filters/views.
- Soft delete & recycle bins, version history, audit log diff view, and notification hooks.

## Installation
1. Copy `config/config.php`, set database + MinIO credentials, and adjust the lockout policy if required.
2. Import `database.sql` into MySQL/MariaDB.
3. Install PHP dependencies if you plan to replace the bundled export/minio stubs.
4. Serve `/public` (Apache/Nginx/PHP-FPM or `php -S localhost:8000 -t public`).
5. Ensure `storage/` is writable so the pseudo-MinIO adapter and temp exports can persist files.

Default login (from seed): `owner@example.com / password`.

## Configuration snippets
`config/config.php` exposes database, MinIO, and app toggles.  Environment variables (`APP_BASE_URL`, `DB_*`, `MINIO_*`) override the defaults automatically.

## Development pointers
- `public/index.php` is the lightweight router. Add new routes by extending it.
- Auth/session helpers live in `src/Core`.  Modules keep their own controller+model pairs inside `src/Modules/<Module>`.
- Views live in `templates/<module>`. They extend the shared layout at `templates/layout/main_layout.php` which renders the sidebar, top bar, notifications, and search overlay.
- REST endpoints live in `public/api/index.php`.  They enforce auth, permissions, and CSRF (for non-GET).
- The custom assets (`public/assets/css/app.css` + `public/assets/js/app.js`) contain theme CSS variables, the sidebar/overlay interactions, signature pad logic, and AJAX helpers for saving filters or polling notifications.

## Testing / tooling
There is no automated suite bundled.  Run the PHP built-in server for manual tests:
```
php -S localhost:8000 -t public
```
Use browser dev tools to verify AJAX flows (saved filters, notifications, API submissions, etc.).
