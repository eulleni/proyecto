#!/bin/bash

set -euo pipefail

FECHA=$(date +%Y-%m-%d_%H-%M)

# Cambia estos valores en tu entorno o exportalos como variables.
# No escribas contrasenas reales en este archivo si lo vas a subir a GitHub.
DB_NAME="${DB_NAME:-ticketing}"
DB_USER="${DB_USER:-backup_user}"
DB_PASSWORD="${DB_PASSWORD:-CAMBIAR_PASSWORD_BACKUP}"
WEB_DIR="${WEB_DIR:-/var/www/html/ticketing}"

# 192.0.2.20 es una IP de documentacion. Sustituyela localmente por la IP real
# de tu servidor de backups, pero no subas esa version con datos reales.
BACKUP_USER="${BACKUP_USER:-usuario_backup}"
BACKUP_HOST="${BACKUP_HOST:-192.0.2.20}"
BACKUP_DIR="${BACKUP_DIR:-/home/usuario_backup/backups}"
DESTINO="${BACKUP_USER}@${BACKUP_HOST}:${BACKUP_DIR}"

SQL_FILE="ticketing_$FECHA.sql"
WEB_FILE="web_$FECHA.tar.gz"

MYSQL_PWD="$DB_PASSWORD" mysqldump -u "$DB_USER" "$DB_NAME" > "$SQL_FILE"
tar -czf "$WEB_FILE" "$WEB_DIR"

scp "$SQL_FILE" "$DESTINO"
scp "$WEB_FILE" "$DESTINO"

rm "$SQL_FILE"
rm "$WEB_FILE"
