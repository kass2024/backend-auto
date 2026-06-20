#!/bin/bash
# Update live API from GitHub — git pull ONLY (no clone).
#
# Use when your project is already a git repo on cPanel.
#
# ── Layout A: monorepo (backend/ + front-end/) ──
#   cd ~/auto-tech
#   bash backend/deploy/cpanel-git-pull.sh
#
# ── Layout B: flat Laravel at API subdomain (your setup) ──
#   cd ~/api.neamee-autotechsolutions.com
#   git pull origin main
#   bash deploy/cpanel-fix-api.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BACKEND_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

# Monorepo root = parent of backend/
MONOREPO_ROOT="$(cd "$BACKEND_DIR/.." && pwd)"

LIVE_DIR="${NEAMEE_LIVE_DIR:-$HOME/api.neamee-autotechsolutions.com}"

if [ -d "$MONOREPO_ROOT/.git" ]; then
  GIT_ROOT="$MONOREPO_ROOT"
elif [ -d "$BACKEND_DIR/.git" ]; then
  GIT_ROOT="$BACKEND_DIR"
elif [ -d "$LIVE_DIR/.git" ]; then
  GIT_ROOT="$LIVE_DIR"
else
  echo "ERROR: No .git found."
  echo "  cd to your git repo (monorepo root or api.neamee-autotechsolutions.com) and run:"
  echo "    git pull origin main"
  echo "    bash deploy/cpanel-fix-api.sh"
  exit 1
fi

cd "$GIT_ROOT"
BRANCH="${NEAMEE_GIT_BRANCH:-main}"

echo "==> git pull ($GIT_ROOT, branch $BRANCH)"
git pull origin "$BRANCH"

# Monorepo → sync backend/ to flat live API folder
if [ -f "$GIT_ROOT/backend/artisan" ] && [ "$GIT_ROOT/backend" != "$LIVE_DIR" ]; then
  echo "==> Sync backend/ → $LIVE_DIR"
  ENV_BACKUP=""
  if [ -f "$LIVE_DIR/.env" ]; then
    ENV_BACKUP="$(mktemp)"
    cp "$LIVE_DIR/.env" "$ENV_BACKUP"
  fi
  mkdir -p "$LIVE_DIR"
  rsync -av \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='vendor/' \
    --exclude='node_modules/' \
    "$GIT_ROOT/backend/" "$LIVE_DIR/"
  if [ -n "$ENV_BACKUP" ] && [ -f "$ENV_BACKUP" ]; then
    cp "$ENV_BACKUP" "$LIVE_DIR/.env"
    echo "Restored .env"
  fi
  cd "$LIVE_DIR"
elif [ -f "$GIT_ROOT/artisan" ]; then
  # Flat Laravel repo root (api subdomain IS the git checkout)
  cd "$GIT_ROOT"
else
  echo "ERROR: Could not find Laravel (artisan) after pull."
  exit 1
fi

bash deploy/cpanel-fix-api.sh

echo ""
echo "Done. Test:"
echo "  curl -s https://api.neamee-autotechsolutions.com/api/public/health"
