# NEAMEE Auto-Tech — Laravel API

GitHub: **https://github.com/kass2024/backend-auto**

Live: **https://api.neamee-autotechsolutions.com**

Files sit **directly at the subdomain root** (no `backend/` subfolder).

---

## cPanel — fresh install (clear folder + clone)

SSH:

```bash
cd ~

# Backup .env first
cp api.neamee-autotechsolutions.com/.env ~/.env.neamee.backup 2>/dev/null || true

# Clear contents only (keep the folder — cPanel subdomain path stays the same)
mkdir -p api.neamee-autotechsolutions.com
find api.neamee-autotechsolutions.com -mindepth 1 -maxdepth 1 -exec rm -rf {} +
git clone https://github.com/kass2024/backend-auto.git api.neamee-autotechsolutions.com
cd api.neamee-autotechsolutions.com

# Restore .env
cp ~/.env.neamee.backup .env 2>/dev/null || cp deploy/env.cpanel.example .env

bash cpanel-fix-api.sh
```

Or use the script (after first clone):

```bash
cd ~
curl -sL https://raw.githubusercontent.com/kass2024/backend-auto/main/fresh-clone.sh -o fresh-clone.sh
bash fresh-clone.sh
```

---

## cPanel — every update (after fresh install)

```bash
cd ~/api.neamee-autotechsolutions.com
bash git-pull-fix.sh
```

---

## Document root (cPanel)

Point subdomain to **`public`** folder:

```
/home/you/api.neamee-autotechsolutions.com/public
```

---

## Local development

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---

## Verify

```bash
curl -s https://api.neamee-autotechsolutions.com/api/public/health
```

Login: `admin@neamee-autotechsolutions.com` / `password`
