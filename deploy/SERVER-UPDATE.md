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

### Cron (cPanel — one job for all reminders)

Your host allows cron **at most every 5 minutes**. Use **one** cron job; Laravel handles
all reminder types (5 min / 1 hr / 5 days before, plus weekly / monthly / yearly repeats).

#### cPanel → Cron Jobs → Add New Cron Job

| Field | Value |
|-------|-------|
| **Minute** | `*/5` (or Common Settings → Every 5 minutes) |
| **Hour** | `*` |
| **Day** | `*` |
| **Month** | `*` |
| **Weekday** | `*` |
| **Command** | see below |

**Command** (matches cPanel PHP example path):

```bash
/usr/local/bin/php /home/visawgnz/api.neamee-autotechsolutions.com/artisan schedule:run >> /dev/null 2>&1
```

**With logging** (optional):

```bash
/usr/local/bin/php /home/visawgnz/api.neamee-autotechsolutions.com/artisan schedule:run >> /home/visawgnz/api.neamee-autotechsolutions.com/storage/logs/cron.log 2>&1
```

Click **Add New Cron Job**.

#### How weekly / monthly repeats work

You only set repeat in the admin modal (Once, Weekly, Monthly, etc.). The cron does not
need a separate job per interval. Each run:

1. Sends any due early/due reminders for all invoices
2. After a service date passes, bumps `next_service_at` to the next week/month/year

#### Verify on SSH

```bash
cd ~/api.neamee-autotechsolutions.com
php artisan schedule:list
php artisan invoices:list-service-reminders
php artisan invoices:send-service-reminders
```

Scheduled tasks:

| Command | When |
|---------|------|
| `invoices:send-service-reminders` | Every 5 minutes |
| `appointments:send-reminders` | Daily 09:00 |
