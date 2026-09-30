#!/bin/bash
# DP Live — Permissions-only fix script (2026-09-03)
# Usage: bash deploy-permissions-only.sh [/path/to/webroot]
WEBROOT="${1:-.}"
find "$WEBROOT" -type d -exec chmod 755 {} \;
find "$WEBROOT" -type f -exec chmod 644 {} \;
chmod 755 "$WEBROOT/artisan" 2>/dev/null || true
chmod -R 775 "$WEBROOT/storage" "$WEBROOT/bootstrap/cache" 2>/dev/null || true
chmod -R 775 "$WEBROOT/public/uploads" 2>/dev/null || true
echo "Permissions fixed in: $WEBROOT"
