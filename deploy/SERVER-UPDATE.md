# Fresh clone — clear folder + clone backend-auto

Repo: https://github.com/kass2024/backend-auto

**Important:** Only delete **contents** inside the folder — keep `api.neamee-autotechsolutions.com` itself so cPanel subdomain/document-root paths stay valid.

## Run on cPanel SSH

```bash
cd ~

# 1. Backup .env
cp api.neamee-autotechsolutions.com/.env ~/.env.neamee.backup 2>/dev/null || true

# 2. Clear folder contents (folder itself is preserved)
mkdir -p api.neamee-autotechsolutions.com
find api.neamee-autotechsolutions.com -mindepth 1 -maxdepth 1 -exec rm -rf {} +

# 3. Clone fresh into the empty folder (Laravel files directly at root)
git clone https://github.com/kass2024/backend-auto.git api.neamee-autotechsolutions.com
cd api.neamee-autotechsolutions.com

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

(`fresh-clone.sh` is also in the repo after first clone)

## Verify

```bash
php artisan route:list | grep health
curl -s https://api.neamee-autotechsolutions.com/api/public/health
```

## Updates later

```bash
cd ~/api.neamee-autotechsolutions.com
git pull origin main
bash cpanel-fix-api.sh
```
