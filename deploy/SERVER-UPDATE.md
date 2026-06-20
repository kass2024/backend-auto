# Fresh clone — delete all + clone backend-auto

Repo: https://github.com/kass2024/backend-auto

## Run on cPanel SSH

```bash
cd ~

# 1. Backup .env
cp api.neamee-autotechsolutions.com/.env ~/.env.neamee.backup 2>/dev/null || true

# 2. Delete old folder completely
rm -rf api.neamee-autotechsolutions.com

# 3. Clone fresh (Laravel files directly at root)
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
bash fresh-clone.sh
```

(`fresh-clone.sh` is in the repo after first clone)

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
