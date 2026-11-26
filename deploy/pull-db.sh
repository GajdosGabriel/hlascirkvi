#!/usr/bin/env bash
# Stiahne produkčnú databázu do lokálnej (XAMPP) a dobehne lokálne migrácie.
#
#   bash deploy/pull-db.sh <SSH_PORT>
#
# Port SSH konzoly Websupportu sa mení pri každej aktivácii (admin.websupport.sk
# → hlascirkvi.sk → Konzola → SSH pripojenie). Prihlásenie ide SSH kľúčom.
#
# Lokálna DB sa ZMAŽE a nahradí produkčnou. Dump obsahuje osobné údaje, preto
# sa ukladá len do dočasného priečinka a po importe sa zmaže. Lokálne maily idú
# do Mailtrapu (.env), reálnym používateľom nič neodíde.
set -euo pipefail

PORT="${1:?Použitie: bash deploy/pull-db.sh <SSH_PORT>}"
HOST="uid1079389@shell.r2.websupport.sk"

cd "$(dirname "$0")/.."

env_value() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | tr -d '"\r'; }
DB_NAME="$(env_value DB_DATABASE)"
DB_USER="$(env_value DB_USERNAME)"
BIN="$(env_value DB_DUMP_BINARY_PATH)"
MYSQL="${BIN:+${BIN%/}/}mysql"
MYSQL_ARGS=(-u "$DB_USER")
[ -n "$(env_value DB_PASSWORD)" ] && MYSQL_ARGS+=(-p"$(env_value DB_PASSWORD)")

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

# Záloha doterajšej lokálnej DB (mimo gitu, prepíše sa pri ďalšom behu).
BACKUP="storage/app/local-before-pull.sql.gz"
echo "==> zálohujem lokálnu DB do $BACKUP"
"${MYSQL%mysql}mysqldump" "${MYSQL_ARGS[@]}" "$DB_NAME" | gzip > "$BACKUP" || true

echo "==> dump z produkcie"
# Prístup k DB sa číta z config cache (heslo má znaky, ktoré shell z .env
# nenačíta) a odovzdá cez dočasný súbor s právami 600, nie v príkazovom riadku.
ssh -o BatchMode=yes -o StrictHostKeyChecking=accept-new -p "$PORT" "$HOST" '
  set -e; cd hlascirkvi.sk/web; umask 077; F=$(mktemp); trap "rm -f $F" EXIT
  php -r '\''$m=(require "bootstrap/cache/config.php")["database"]["connections"]["mysql"];
    $q=fn($v)=>"\"".addcslashes($v,"\\\"")."\"";
    echo "[client]\nhost=".$q($m["host"])."\nport=".$m["port"]."\nuser=".$q($m["username"])."\npassword=".$q($m["password"])."\n";'\'' > $F
  DB=$(php -r '\''echo (require "bootstrap/cache/config.php")["database"]["connections"]["mysql"]["database"];'\'')
  mariadb-dump --defaults-extra-file=$F --single-transaction --quick --no-tablespaces --routines "$DB" | gzip' > "$TMP/prod.sql.gz"

gunzip -c "$TMP/prod.sql.gz" | tail -1 | grep -q "Dump completed" || { echo "Dump nie je úplný, lokálna DB ostáva bez zmeny." >&2; exit 1; }
ls -la "$TMP/prod.sql.gz"

echo "==> import do lokálnej DB $DB_NAME"
"$MYSQL" "${MYSQL_ARGS[@]}" -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; CREATE DATABASE \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# Prvý riadok nového mariadb-dump (sandbox mode) staršie klienty nepoznajú.
gunzip -c "$TMP/prod.sql.gz" | sed '1{/enable the sandbox mode/d}' | "$MYSQL" "${MYSQL_ARGS[@]}" "$DB_NAME"

echo "==> lokálne migrácie a cache"
php artisan migrate --force
php artisan optimize:clear

echo "==> hotovo"
