#!/bin/bash
# Pull latest from GitHub + fix API (flat Laravel at subdomain root).
#
#   cd ~/api.neamee-autotechsolutions.com
#   bash git-pull-fix.sh

set -e
cd "$(dirname "$0")"

if [ ! -d .git ]; then
  echo "ERROR: No .git in $(pwd)"
  echo "  First time setup:"
  echo "    cd ~/api.neamee-autotechsolutions.com"
  echo "    git init"
  echo "    git remote add origin https://github.com/kass2024/backend-auto.git"
  echo "    git fetch origin"
  echo "    git checkout -b main origin/main"
  exit 1
fi

echo "==> git pull origin main"
git pull origin main

bash cpanel-fix-api.sh
