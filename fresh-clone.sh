#!/bin/bash
# Folder ~/api.neamee-autotechsolutions.com MUST already exist (cPanel subdomain).
# This script deletes ONLY its contents, then clones backend-auto into it.
#
#   cd ~
#   bash fresh-clone.sh

set -e

REPO="${NEAMEE_REPO:-https://github.com/kass2024/backend-auto.git}"
LIVE_DIR="${NEAMEE_LIVE_DIR:-$HOME/api.neamee-autotechsolutions.com}"
ENV_BACKUP="$HOME/.env.neamee.backup"

echo "==> NEAMEE API fresh clone (into existing folder)"
echo "    Repo:   $REPO"
echo "    Folder: $LIVE_DIR"
echo ""

if [ ! -d "$LIVE_DIR" ]; then
  echo "ERROR: $LIVE_DIR does not exist. Create the subdomain folder in cPanel first."
  exit 1
fi

# Backup .env before clearing
if [ -f "$LIVE_DIR/.env" ]; then
  cp "$LIVE_DIR/.env" "$ENV_BACKUP"
  echo "Backed up .env → $ENV_BACKUP"
fi

# Delete contents only — folder stays (cPanel path unchanged)
echo "==> Deleting contents of $LIVE_DIR (folder kept)"
find "$LIVE_DIR" -mindepth 1 -maxdepth 1 -exec rm -rf {} +

# Clone into the existing empty folder
echo "==> git clone into $LIVE_DIR"
cd "$LIVE_DIR"
git clone "$REPO" .

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
