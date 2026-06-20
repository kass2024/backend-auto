# cPanel API — flat Laravel layout (no backend/ folder)

## CRITICAL — API subdomain must serve Laravel, NOT React

If `curl https://api.neamee-autotechsolutions.com/index.php` returns **HTML with Vite/React**,
the API subdomain has the **frontend** uploaded instead of (or mixed with) Laravel `public/`.

| Subdomain | What belongs there |
|-----------|-------------------|
| `neamee-autotechsolutions.com` | React build (`front-end/dist/`) |
| `api.neamee-autotechsolutions.com` | Laravel only (`backend/` files) |

**Never** put `index.html`, `src/`, or the frontend SPA `.htaccess` on the API subdomain.

---

Laravel files live **directly** in `api.neamee-autotechsolutions.com/`:

```
api.neamee-autotechsolutions.com/
├── .htaccess          ← REQUIRED (often missing after upload!)
├── index.php          ← REQUIRED
├── app/
├── public/
├── vendor/            ← from composer install (NOT in git)
└── .env
```

---

## Why you get 404 / login fails

| Symptom | Cause |
|---------|--------|
| `/api/public/health` → **404** | **No root `.htaccess`** — LiteSpeed never reaches Laravel |
| `/public/index.php` → **500** | Missing `vendor/` or bad `.env` / database |
| **Invalid credentials** | API may work but **seeder not run** — no admin user yet |

Your File Manager screenshot shows **no `.htaccess` and no `index.php` at root** — that is the 404.

---

## Step 1 — Upload missing files (File Manager)

Enable **Settings → Show Hidden Files**.

Upload to the Laravel root (same level as `app/`):

1. **`.htaccess`** — copy from `deploy/htaccess-root.txt` and rename to `.htaccess`
2. **`index.php`** — from repo root (not only inside `public/`)

In `public/`:

```bash
cp public/index.cpanel.php public/index.php
```

---

## Step 2 — Fix `.env` on server

**Critical fixes** (compare with `deploy/env.cpanel.example`):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.neamee-autotechsolutions.com
ASSET_URL=
FRONTEND_URL=https://neamee-autotechsolutions.com
CORS_ALLOWED_ORIGINS=https://neamee-autotechsolutions.com,https://www.neamee-autotechsolutions.com
SANCTUM_STATEFUL_DOMAINS=neamee-autotechsolutions.com,www.neamee-autotechsolutions.com,api.neamee-autotechsolutions.com
SESSION_DOMAIN=.neamee-autotechsolutions.com
SESSION_SECURE_COOKIE=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=visawgnz_autotech
DB_USERNAME=visawgnz_auto_user
DB_PASSWORD=Autotech@2026
```

**Do not wrap DB password in quotes** — wrong:

```env
DB_PASSWORD='Autotech@2026'
```

Correct:

```env
DB_PASSWORD=Autotech@2026
```

Remove `ASSET_URL=https://api.../public` — that breaks URLs when document root is correct.

---

## Step 3 — Install Composer dependencies (cPanel has no global `composer`)

Shared hosting SSH usually does **not** have `composer` in PATH. Use a local `composer.phar`:

```bash
cd ~/api.neamee-autotechsolutions.com

# Download Composer (once)
curl -sS https://getcomposer.org/installer | php

# Install Laravel dependencies (creates vendor/)
php composer.phar install --no-dev --optimize-autoloader
```

If `php` is wrong version, use cPanel’s binary (check version in cPanel → Select PHP Version):

```bash
ea-php82 composer.phar install --no-dev --optimize-autoloader
```

**Or run the all-in-one script** (downloads composer.phar automatically):

```bash
cd ~/api.neamee-autotechsolutions.com
bash deploy/cpanel-install.sh
```

Then verify `vendor/` exists:

```bash
ls -la vendor/autoload.php
```

Without `vendor/`, `php artisan` and the website will fail.

---

## Step 4 — Migrations & cache

```bash
php artisan migrate --force
php artisan db:seed --class=GarageSeeder --force
php artisan config:cache
php artisan route:cache
chmod -R 775 storage bootstrap/cache
```

---

## Step 4 — Test

```
https://api.neamee-autotechsolutions.com/api/public/health
```

Expected JSON: `"ok": true`

**Admin login** (after seeder):

- Email: `admin@neamee-autotechsolutions.com`
- Password: `password`

---

## Option A — Best: document root = `public` folder

cPanel → **Domains** → `api.neamee-autotechsolutions.com` → **Document Root**:

```
/home/visawgnz/api.neamee-autotechsolutions.com/public
```

Inside `public/` you must have **only Laravel** web files:

```
public/
├── .htaccess      ← Laravel (rewrites to index.php)
├── index.php      ← from index.cpanel.php (PHP, not HTML)
├── build/         ← Filament assets (optional)
└── (NO index.html, NO src/, NO React files)
```

Fix mixed/wrong `public/` on SSH:

```bash
cd ~/api.neamee-autotechsolutions.com/public

# Remove React SPA files if present
rm -f index.html
rm -rf src node_modules assets 2>/dev/null

# Restore Laravel entry
cp index.cpanel.php index.php

# Confirm it's PHP Laravel, not HTML
head -1 index.php
# Must show: <?php
```

Run diagnose script:

```bash
cd ~/api.neamee-autotechsolutions.com
bash deploy/cpanel-diagnose.sh
```

`.env`:

Rebuild with:

```env
VITE_API_URL=https://api.neamee-autotechsolutions.com
```

Upload `front-end/dist/` to the main site.

---

## CSRF token mismatch on login

Frontend (`neamee-autotechsolutions.com`) and API (`api.neamee-autotechsolutions.com`) are **different subdomains**. The CSRF cookie must be shared via `SESSION_DOMAIN`.

Add to server `.env`:

```env
SANCTUM_STATEFUL_DOMAINS=neamee-autotechsolutions.com,www.neamee-autotechsolutions.com
SESSION_DOMAIN=.neamee-autotechsolutions.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CORS_ALLOWED_ORIGINS=https://neamee-autotechsolutions.com,https://www.neamee-autotechsolutions.com
```

Then:

```bash
php artisan config:clear
php artisan config:cache
```

Rebuild frontend after pulling latest code:

```bash
cd front-end && npm run build
```

**Wrong:** `SESSION_DOMAIN=null` — main site JS cannot read the CSRF cookie → login fails.
