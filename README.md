# NEAMEE Auto-Tech — Laravel API

GitHub: **https://github.com/kass2024/backend-auto**

Live: **https://api.neamee-autotechsolutions.com**

All Laravel files sit **directly** at the subdomain root on cPanel (no `backend/` subfolder).

---

## Local development

```bash
cp .env.example .env
php artisan key:generate
composer install
php artisan migrate --seed
php artisan serve
```

Frontend (separate repo): `VITE_API_URL=https://api.neamee-autotechsolutions.com`

---

## cPanel — first-time setup

SSH:

```bash
cd ~/api.neamee-autotechsolutions.com

# Keep existing .env if you have one
cp .env ~/.env.neamee.backup 2>/dev/null || true

# Connect to GitHub repo (flat — files at root)
git init
git remote add origin https://github.com/kass2024/backend-auto.git
git fetch origin
git checkout -b main origin/main

# Restore production .env
cp ~/.env.neamee.backup .env 2>/dev/null || cp deploy/env.cpanel.example .env

bash cpanel-fix-api.sh
```

Set cPanel document root to **`public`** folder (recommended):

```
/home/you/api.neamee-autotechsolutions.com/public
```

---

## cPanel — every update

```bash
cd ~/api.neamee-autotechsolutions.com
bash git-pull-fix.sh
```

Or:

```bash
git pull origin main
bash cpanel-fix-api.sh
```

---

## Verify

```bash
curl -s https://api.neamee-autotechsolutions.com/api/public/health
```

Login: `admin@neamee-autotechsolutions.com` / `password`
