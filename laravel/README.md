# InternHub — Industrial Internship Matching Portal

A Laravel 12 web app connecting students with internship-offering employers.
MVC architecture, Eloquent ORM, and a REST API (Sanctum token auth) that the
Blade front-end consumes via `fetch()`.

## Requirements

- XAMPP (PHP 8.2+, MySQL/MariaDB) — or any equivalent Apache/PHP/MySQL stack
- [Composer](https://getcomposer.org/)

## 1. Get the project

Copy/clone this folder so that `laravel/` sits inside your XAMPP `htdocs`
directory (or anywhere PHP can reach it), e.g. `htdocs/IP_assg/laravel`.

## 2. Install PHP dependencies

```bash
cd laravel
composer install
```

## 3. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set at minimum:

- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — your local MySQL credentials
  (XAMPP's default is `root` with no password)
- `APP_URL` — wherever you'll actually serve the app (see step 6). This
  matters: it's baked into generated file URLs (avatars, company logos,
  resumes), so a mismatch here means uploaded images won't load.
- `ADMIN_PASSWORD` — change this from the placeholder before seeding

Everything else (`MAIL_*`, `GOOGLE_MAPS_API_KEY`) is optional — see step 7.

## 4. Create the database

In phpMyAdmin, create an **empty** database named `internship_portal` —
just the database itself, don't import any SQL there. Then let Laravel
build the tables (this is the authoritative way, not `schema.sql`):

```bash
php artisan migrate
```

`schema.sql` in the project root is a **reference snapshot** of the resulting
structure, useful if you ever want to import by hand instead — but
`php artisan migrate` is the real source of truth and the recommended path.

## 5. Seed the Admin account and link storage

```bash
php artisan db:seed --class=AdminSeeder
php artisan storage:link
```

The seeder creates one Admin account from `ADMIN_USERNAME` / `ADMIN_EMAIL` /
`ADMIN_PASSWORD` in your `.env`. Admins log in from the login page's
**Admin** tab using that username (not email) — this is a separate, dedicated
flow from the Student/Employer email login on purpose.

`storage:link` creates `public/storage`, which is required for uploaded
avatars/logos/resumes to actually be reachable by URL.

## 6. Run the app

**Option A — quickest, no Apache config needed:**

```bash
php artisan serve --port=8090
```

Visit `http://127.0.0.1:8090`. Make sure `APP_URL` in `.env` matches this
exactly (protocol + host + port).

**Option B — persistent XAMPP Apache VirtualHost:**

1. In `xampp/apache/conf/httpd.conf`, add a line (pick any free port):
   ```
   Listen 8090
   ```
2. In `xampp/apache/conf/extra/httpd-vhosts.conf`, add:
   ```apache
   <VirtualHost *:8090>
       ServerName internhub.local
       DocumentRoot "C:/path/to/htdocs/IP_assg/laravel/public"

       <Directory "C:/path/to/htdocs/IP_assg/laravel/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
3. Set `APP_URL=http://localhost:8090` in `.env`, then
   `php artisan config:clear`.
4. Restart Apache from the XAMPP control panel (config changes only take
   effect on restart).

If your chosen port is already in use by something else on your machine,
pick a different one and update both files plus `APP_URL` to match.

## 7. Optional configuration

**Real email** (verification links, password resets) — by default
`MAIL_MAILER=log` writes emails to `storage/logs/laravel.log` instead of
sending them, and the register/forgot-password API responses include a
`dev_verification_link` / `dev_reset_link` field (non-production only) so you
can test the full flow without any email setup. To send real email, switch
to `MAIL_MAILER=smtp` and fill in your own credentials (e.g. a Gmail address
+ an [App Password](https://myaccount.google.com/apppasswords) — never a
normal account password, and never commit it).

**Google Maps** on the register page's company-address picker — get a key at
the [Google Cloud Console](https://console.cloud.google.com/google/maps-apis)
(enable "Maps JavaScript API" + "Places API") and set
`GOOGLE_MAPS_API_KEY`. Leave it blank and the form just falls back to plain
text inputs for city/state/postcode — nothing breaks.

## Verifying it works

- `/` — homepage
- `/register` — create a Student or Employer account
- `/login` — Student/Employer tab (email) or Admin tab (username)
- `/profile` — manage full name, email, password, profile picture (requires login)
- `/api/companies` — public REST endpoint, should return JSON

## Project structure

```
app/Http/Controllers/Api/   REST API controllers (JSON responses)
app/Http/Requests/          Form Request validation classes
app/Factories/              Factory Method pattern (Student/Employer/Admin profile creation)
app/Models/                 Eloquent models
database/migrations/        Schema — source of truth
database/seeders/           AdminSeeder
resources/views/            Blade views (home, auth/login, auth/register, auth/profile)
routes/web.php              Page routes
routes/api.php              REST API routes (Sanctum-protected where relevant)
public/                     Web root — images, CSS, JS, front controller
```

## Notes for the team

- Never commit your own `.env` — it holds your local DB password and (if
  configured) real mail credentials. Each of you should have your own,
  built from `.env.example`.
- Admin accounts are never self-registered through `/register` — only
  created via `AdminSeeder`. If you need a second admin, adjust the seeder
  or create one via `php artisan tinker`.
- `.env`'s `APP_URL` must always match however you're actually accessing
  the site in your browser, or uploaded file links will silently 404.
