# cPanel update — flat layout at api.neamee-autotechsolutions.com root

Repo: https://github.com/kass2024/backend-auto

## Update (every deploy)

```bash
cd ~/api.neamee-autotechsolutions.com
bash git-pull-fix.sh
```

## First-time git on server

```bash
cd ~/api.neamee-autotechsolutions.com
cp .env ~/.env.neamee.backup 2>/dev/null || true
git init
git remote add origin https://github.com/kass2024/backend-auto.git
git fetch origin
git checkout -b main origin/main
cp ~/.env.neamee.backup .env 2>/dev/null || cp deploy/env.cpanel.example .env
bash cpanel-fix-api.sh
```

## Production `.env`

See `deploy/env.cpanel.example`
