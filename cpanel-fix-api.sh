#!/bin/bash
# Fix API after git pull — flat layout at api.neamee-autotechsolutions.com root.
#
#   git pull origin main
#   bash cpanel-fix-api.sh

set -e
cd "$(dirname "$0")"

if command -v ea-php82 >/dev/null 2>&1; then PHP=ea-php82
elif command -v ea-php81 >/dev/null 2>&1; then PHP=ea-php81
else PHP=php; fi

echo "==> NEAMEE API fix ($(pwd))"

# .env from template if missing
if [ ! -f .env ]; then
  cp deploy/env.cpanel.example .env
  echo "Created .env from deploy/env.cpanel.example"
fi

# APP_KEY is required for login sessions / CSRF cookies
if ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "==> Generating APP_KEY (required for login)"
  $PHP artisan key:generate --force
fi

# Writable storage (file sessions + logs)
mkdir -p storage/framework/sessions storage/framework/cache/data storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

# Cross-subdomain CSRF: frontend (neamee-autotechsolutions.com) + API (api.*)
set_env() {
  local key="$1"
  local value="$2"
  if grep -q "^${key}=" .env 2>/dev/null; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    echo "${key}=${value}" >> .env
  fi
}
echo "==> Session / CSRF domains (required for login from main site)"
set_env "SESSION_DOMAIN" ".neamee-autotechsolutions.com"
set_env "SESSION_SECURE_COOKIE" "true"
set_env "SANCTUM_STATEFUL_DOMAINS" "neamee-autotechsolutions.com,www.neamee-autotechsolutions.com"

# routes/health.php
mkdir -p routes
if [ ! -f routes/health.php ]; then
  cat > routes/health.php << 'PHP'
<?php

use App\Http\Controllers\Api\PublicController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/api/public/health', [PublicController::class, 'health']);
PHP
fi

rm -f bootstrap/cache/routes*.php 2>/dev/null || true

# .htaccess from deploy templates
[ -f deploy/htaccess-root.txt ] && cp deploy/htaccess-root.txt .htaccess
[ -f deploy/htaccess-public.txt ] && cp deploy/htaccess-public.txt public/.htaccess
chmod 644 .htaccess public/.htaccess 2>/dev/null || true
[ -f public/index.cpanel.php ] && cp public/index.cpanel.php public/index.php

# Composer
if [ ! -f vendor/autoload.php ]; then
  if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --optimize-autoloader
  elif [ -f composer.phar ]; then
    $PHP composer.phar install --no-dev --optimize-autoloader
  else
    curl -sS https://getcomposer.org/installer | $PHP
    $PHP composer.phar install --no-dev --optimize-autoloader
  fi
fi

$PHP artisan migrate --force

# Database session driver needs the sessions table
if grep -qE '^SESSION_DRIVER=database' .env 2>/dev/null; then
  $PHP artisan migrate --force --path=database/migrations/2024_01_01_000002_create_sessions_table.php 2>/dev/null || true
fi

$PHP artisan db:seed --class=GarageSeeder --force

if $PHP artisan list 2>/dev/null | grep -q neamee:ensure-admin; then
  $PHP artisan neamee:ensure-admin
else
  $PHP artisan tinker --execute="App\Models\User::updateOrCreate(['email'=>'admin@neamee-autotechsolutions.com'],['name'=>'Admin User','password'=>Illuminate\Support\Facades\Hash::make('password'),'role'=>'admin','phone'=>'+1 (567) 329-9231']);"
fi

$PHP artisan config:clear
$PHP artisan route:clear
$PHP artisan cache:clear
$PHP artisan config:cache

echo ""
$PHP artisan route:list | grep "api/public/health" || true
echo "Test: curl -s https://api.neamee-autotechsolutions.com/api/public/health"
