#!/bin/bash
# Run from Laravel root on cPanel SSH:
#   cd ~/api.neamee-autotechsolutions.com && bash deploy/cpanel-install.sh
#
# cPanel often has no global "composer" — this script downloads composer.phar locally.

set -e
cd "$(dirname "$0")/.."
ROOT="$(pwd)"

echo "==> NEAMEE API cPanel install ($ROOT)"

# Prefer cPanel EasyApache PHP if available
if command -v ea-php82 >/dev/null 2>&1; then
  PHP=ea-php82
elif command -v ea-php81 >/dev/null 2>&1; then
  PHP=ea-php81
elif command -v php >/dev/null 2>&1; then
  PHP=php
else
  echo "ERROR: php not found. Use cPanel → Select PHP Version or contact host."
  exit 1
fi

echo "Using PHP: $($PHP -v | head -1)"

if [ -f deploy/htaccess-root.txt ]; then
  cp deploy/htaccess-root.txt .htaccess
  echo "Updated root .htaccess"
fi

if [ -f deploy/htaccess-public.txt ]; then
  cp deploy/htaccess-public.txt public/.htaccess
  echo "Updated public/.htaccess"
fi

if [ ! -f index.php ]; then
  echo "WARNING: index.php missing at project root — upload it from the repo."
fi

if [ ! -f public/index.php ] && [ -f public/index.cpanel.php ]; then
  cp public/index.cpanel.php public/index.php
  echo "Created public/index.php from index.cpanel.php"
fi

# --- Composer (local composer.phar when "composer" is not in PATH) ---
if command -v composer >/dev/null 2>&1; then
  COMPOSER_CMD="composer"
elif [ -f composer.phar ]; then
  COMPOSER_CMD="$PHP composer.phar"
else
  echo "==> Downloading composer.phar (global composer not installed on this server)"
  curl -sS https://getcomposer.org/installer | $PHP
  COMPOSER_CMD="$PHP composer.phar"
fi

echo "==> $COMPOSER_CMD install --no-dev --optimize-autoloader"
$COMPOSER_CMD install --no-dev --optimize-autoloader

if [ ! -f vendor/autoload.php ]; then
  echo "ERROR: vendor/autoload.php still missing after composer install."
  exit 1
fi

if [ ! -f vendor/stripe/stripe-php/init.php ]; then
  echo "ERROR: Stripe SDK missing after composer install. Upload composer.lock and run again."
  exit 1
fi

echo "==> Laravel setup"
$PHP artisan migrate --force
$PHP artisan db:seed --class=GarageSeeder --force
$PHP artisan neamee:ensure-admin

$PHP artisan config:clear
$PHP artisan cache:clear
$PHP artisan route:clear
$PHP artisan config:cache
$PHP artisan route:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "Done. Test:"
echo "  curl -s https://api.neamee-autotechsolutions.com/api/public/health"
echo ""
echo "Admin login: admin@neamee-autotechsolutions.com / password"
