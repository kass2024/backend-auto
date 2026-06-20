# Fresh clone — clear folder + clone backend-auto

Repo: https://github.com/kass2024/backend-auto

**Important:** `api.neamee-autotechsolutions.com` **already exists** in cPanel. Only delete its **contents**, then clone into it. Never `rm -rf api.neamee-autotechsolutions.com`.

## Run on cPanel SSH

```bash
cd ~

# 1. Backup .env
cp api.neamee-autotechsolutions.com/.env ~/.env.neamee.backup 2>/dev/null || true

# 2. Delete contents only (folder stays)
find api.neamee-autotechsolutions.com -mindepth 1 -maxdepth 1 -exec rm -rf {} +

# 3. Clone into the existing empty folder
cd api.neamee-autotechsolutions.com
git clone https://github.com/kass2024/backend-auto.git .

# 4. Restore .env
cp ~/.env.neamee.backup .env 2>/dev/null || cp deploy/env.cpanel.example .env

# 5. Install + migrate + fix routing
bash cpanel-fix-api.sh
```

## Or one script

```bash
cd ~
curl -sL https://raw.githubusercontent.com/kass2024/backend-auto/main/fresh-clone.sh -o fresh-clone.sh
bash fresh-clone.sh
```

## Verify

```bash
php artisan route:list | grep health
curl -s https://api.neamee-autotechsolutions.com/api/public/health
```

## Updates later

```bash
cd ~/api.neamee-autotechsolutions.com
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
bash cpanel-fix-api.sh
php artisan neamee:verify-admin-menus
```

### Cron (appointment email reminders)

Add to cPanel cron (daily at 9 AM):

```
cd ~/api.neamee-autotechsolutions.com && php artisan schedule:run >> /dev/null 2>&1
```

Or run every minute for Laravel scheduler. Reminders send for confirmed appointments **tomorrow**.
