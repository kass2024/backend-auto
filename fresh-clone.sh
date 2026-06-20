#!/bin/bash
# Fresh install: delete old files, clone backend-auto, restore .env, run fix.
#
#   cd ~
#   bash fresh-clone.sh
#
# WARNING: Deletes everything in ~/api.neamee-autotechsolutions.com except .env backup.

set -e

REPO="${NEAMEE_REPO:-https://github.com/kass2024/backend-auto.git}"
LIVE_DIR="${NEAMEE_LIVE_DIR:-$HOME/api.neamee-autotechsolutions.com}"
ENV_BACKUP="$HOME/.env.neamee.backup"

echo "==> NEAMEE API fresh clone"
echo "    Repo:  $REPO"
echo "    Target: $LIVE_DIR"
echo ""

# Backup .env
if [ -f "$LIVE_DIR/.env" ]; then
  cp "$LIVE_DIR/.env" "$ENV_BACKUP"
  echo "Backed up .env → $ENV_BACKUP"
fi

# Delete old folder contents
if [ -d "$LIVE_DIR" ]; then
  echo "==> Removing old files in $LIVE_DIR"
  rm -rf "$LIVE_DIR"
fi

# Clone fresh (files directly at root — no backend/ subfolder)
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
