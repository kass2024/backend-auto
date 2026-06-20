#!/bin/bash
# Fresh install: clear folder contents, clone backend-auto, restore .env, run fix.
# Keeps ~/api.neamee-autotechsolutions.com itself (cPanel subdomain path unchanged).
#
#   cd ~
#   bash fresh-clone.sh
#
# WARNING: Deletes all files inside ~/api.neamee-autotechsolutions.com (not the folder).

set -e

REPO="${NEAMEE_REPO:-https://github.com/kass2024/backend-auto.git}"
LIVE_DIR="${NEAMEE_LIVE_DIR:-$HOME/api.neamee-autotechsolutions.com}"
ENV_BACKUP="$HOME/.env.neamee.backup"

echo "==> NEAMEE API fresh clone"
echo "    Repo:   $REPO"
echo "    Target: $LIVE_DIR (folder kept; contents replaced)"
echo ""

mkdir -p "$LIVE_DIR"

# Backup .env before clearing
if [ -f "$LIVE_DIR/.env" ]; then
  cp "$LIVE_DIR/.env" "$ENV_BACKUP"
  echo "Backed up .env → $ENV_BACKUP"
fi

# Delete contents only — keep the folder for cPanel document root
if [ -d "$LIVE_DIR" ]; then
  echo "==> Clearing contents of $LIVE_DIR (folder preserved)"
  find "$LIVE_DIR" -mindepth 1 -maxdepth 1 -exec rm -rf {} +
fi

# Clone into the empty existing directory (Laravel files at root — no backend/ subfolder)
echo "==> git clone"
git clone "$REPO" "$LIVE_DIR"
cd "$LIVE_DIR"

# Restore .env
if [ -f "$ENV_BACKUP" ]; then
  cp "$ENV_BACKUP" .env
  echo "Restored .env"
else
  cp deploy/env.cpanel.example .env
  echo "Created .env from deploy/env.cpanel.example — edit DB password!"
fi

# Fix + migrate
bash cpanel-fix-api.sh

echo ""
echo "==> Done"
echo "Test: curl -s https://api.neamee-autotechsolutions.com/api/public/health"
echo "Login: admin@neamee-autotechsolutions.com / password"
