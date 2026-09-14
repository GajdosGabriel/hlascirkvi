#!/usr/bin/env bash
# Nasadenie na produkciu. Spúšťa ho GitHub Actions (.github/workflows/deploy.yml)
# po každom pushi na master. Dá sa spustiť aj ručne na serveri.
#
# Frontend sa na serveri nebuilduje — na to tu nie je dosť pamäte. Build robí
# GitHub Actions a pred spustením skriptu ho nahrá do public/build.new.
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> git pull"
git pull --ff-only origin master
git log --oneline -1

# Až po pulle: public/build bol kedysi verzovaný a pull, ktorý ho z gitu
# vyradil, by zmazal aj práve nahraté súbory s rovnakým názvom.
if [ -d public/build.new ]; then
    echo "==> nový build frontendu"
    rm -rf public/build.old
    if [ -d public/build ]; then
        mv public/build public/build.old
    fi
    mv public/build.new public/build
    rm -rf public/build.old
fi

echo "==> composer install"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

echo "==> migrácie"
php artisan migrate --force

echo "==> cache"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache

echo "==> reštart queue workera"
php artisan queue:restart

echo "==> hotovo"
