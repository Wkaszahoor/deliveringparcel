#!/bin/bash
# DP Live — Complete Deploy + Permission Fix Script (2026-09-03)
# Usage: bash deploy.sh [/path/to/webroot]
set -e
WEBROOT="${1:-/var/www/html}"
APP="$WEBROOT"
echo "================================================"
echo " Delivering Parcel — Deploy Script"
echo " Target: $APP"
echo "================================================"

echo ""
echo "=== Step 1: Permissions ==="
find "$APP" -type d -exec chmod 755 {} \;
find "$APP" -type f -exec chmod 644 {} \;
chmod 755 "$APP/artisan"
chmod -R 775 "$APP/storage"
chmod -R 775 "$APP/bootstrap/cache"
echo "Permissions set."

echo ""
echo "=== Step 2: Composer ==="
cd "$APP"
composer install --no-dev --optimize-autoloader 2>&1

echo ""
echo "=== Step 3: Artisan ==="
php artisan migrate --force 2>&1
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan storage:link 2>&1 || true

echo ""
echo "=== Step 4: Create upload directories ==="
mkdir -p "$APP/public/uploads/cms/$(date +%Y)/$(date +%m)"
chmod -R 775 "$APP/public/uploads"

echo ""
echo "=== Step 5: Verify ==="
php artisan route:list 2>&1 | grep -E "blog|services|shop|page|cms" | head -30
echo ""
echo "================================================"
echo " DEPLOY COMPLETE"
echo "================================================"
