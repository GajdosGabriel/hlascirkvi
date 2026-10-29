#!/usr/bin/env bash
# Nasadenie na produkciu. Spúšťa sa ručne na serveri (Websupport mení SSH port,
# automatické nasadenie z GitHub Actions preto nefunguje).
#
# public/build je commitnutý, takže sa tu nebuilduje — build treba spraviť pred pushom.
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> git pull"
git pull --ff-only origin master
git log --oneline -1

echo "==> composer install"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

echo "==> migrácie"
php artisan migrate --force

echo "==> cache"
php artisan cache:clear
php artisan view:clear

# config:cache aj route:cache najprv zmažú starý súbor a až potom zapíšu nový —
# požiadavka v tej medzere spadne na „routes-v7.php: Failed to open stream".
# Preto sa generujú bokom a na miesto sa presunú atomickým mv.
APP_CONFIG_CACHE=bootstrap/cache/config.new.php php artisan config:cache
mv -f bootstrap/cache/config.new.php bootstrap/cache/config.php
APP_ROUTES_CACHE=bootstrap/cache/routes-v7.new.php php artisan route:cache
mv -f bootstrap/cache/routes-v7.new.php bootstrap/cache/routes-v7.php

echo "==> reštart queue workera"
php artisan queue:restart

echo "==> hotovo"
