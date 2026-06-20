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
