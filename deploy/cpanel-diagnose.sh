#!/bin/bash
# Quick diagnostic — run on cPanel SSH:
#   cd ~/api.neamee-autotechsolutions.com && bash deploy/cpanel-diagnose.sh

echo "=== Laravel root ==="
pwd
echo ""

echo "=== Required files ==="
for f in .htaccess index.php public/.htaccess public/index.php vendor/autoload.php; do
  if [ -f "$f" ]; then echo "OK  $f"; else echo "MISSING  $f"; fi
done
echo ""

echo "=== .htaccess first line ==="
head -1 .htaccess 2>/dev/null || echo "NO ROOT .htaccess"
head -1 public/.htaccess 2>/dev/null || echo "NO public/.htaccess"
echo ""

echo "=== Blockers (api folder?) ==="
if [ -d api ]; then echo "WARNING: ./api/ folder exists — may block /api/* URLs"; ls -la api/; else echo "OK: no ./api/ folder"; fi
if [ -f public/index.html ]; then echo "WARNING: public/index.html exists (React) — remove on API subdomain"; else echo "OK: no public/index.html"; fi
echo ""

echo "=== public/index.php type ==="
head -1 public/index.php 2>/dev/null || echo "missing"
echo ""

echo "=== Local PHP (bypasses web server) ==="
php artisan route:list --path=health 2>&1 | head -3
echo ""

echo "=== External curl ==="
curl -sI "https://api.neamee-autotechsolutions.com/api/public/health" 2>&1 | head -5
echo ""
curl -s "https://api.neamee-autotechsolutions.com/index.php" 2>&1 | head -c 150
echo ""
