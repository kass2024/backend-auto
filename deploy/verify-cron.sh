#!/usr/bin/env bash
# Run on cPanel SSH after setting up the cron job.
set -euo pipefail

cd "$(dirname "$0")/.."

echo "== PHP =="
php -v | head -1
echo "Path: $(which php)"

echo ""
echo "== Scheduled tasks =="
php artisan schedule:list

echo ""
echo "== Manual test: invoice service reminders =="
php artisan invoices:send-service-reminders

echo ""
echo "== Manual test: appointment reminders =="
php artisan appointments:send-reminders

echo ""
echo "Done. If cron is set (* * * * *), reminders will run automatically."
