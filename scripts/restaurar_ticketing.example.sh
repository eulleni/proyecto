#!/bin/bash

set -euo pipefail

# ================================
# Script de restauracion Ticketing
# ================================

# Cambia estos valores en tu entorno o exportalos como variables.
# 192.0.2.20 es una IP de documentacion; no subas IPs privadas reales.
USUARIO_BACKUP="${USUARIO_BACKUP:-usuario_backup}"
IP_BACKUP="${IP_BACKUP:-192.0.2.20}"
CARPETA_BACKUP_REMOTA="${CARPETA_BACKUP_REMOTA:-/home/usuario_backup/backups}"

# Si DB_PASSWORD queda vacio, mysql pedira la contrasena de forma interactiva.
DB_NAME="${DB_NAME:-ticketing}"
DB_USER="${DB_USER:-ticketuser}"
DB_PASSWORD="${DB_PASSWORD:-}"
WEB_DIR="${WEB_DIR:-/var/www/html/ticketing}"

CARPETA_LOCAL="${CARPETA_LOCAL:-/home/$USER/backups_restaurar}"

echo "Creando carpeta local de restauracion..."
mkdir -p "$CARPETA_LOCAL"

echo "Copiando backups desde la VM secundaria..."
scp "$USUARIO_BACKUP@$IP_BACKUP:$CARPETA_BACKUP_REMOTA/*" "$CARPETA_LOCAL/"

echo "Backups disponibles:"
ls -lh "$CARPETA_LOCAL"

echo ""
read -p "Escribe el nombre exacto del archivo SQL a restaurar: " SQL_FILE
read -p "Escribe el nombre exacto del archivo TAR.GZ a restaurar: " TAR_FILE

echo ""
echo "Restaurando base de datos..."
if [ -n "$DB_PASSWORD" ]; then
    MYSQL_PWD="$DB_PASSWORD" mysql -u "$DB_USER" "$DB_NAME" < "$CARPETA_LOCAL/$SQL_FILE"
else
    mysql -u "$DB_USER" -p "$DB_NAME" < "$CARPETA_LOCAL/$SQL_FILE"
fi

echo ""
echo "Eliminando carpeta web actual..."
sudo rm -rf "$WEB_DIR"

echo ""
echo "Restaurando carpeta web..."
sudo tar -xzvf "$CARPETA_LOCAL/$TAR_FILE" -C /

echo ""
echo "Corrigiendo permisos..."
sudo chown -R www-data:www-data "$WEB_DIR"
sudo chmod -R 755 "$WEB_DIR"

echo ""
echo "Reiniciando Apache..."
sudo systemctl restart apache2

echo ""
echo "Restauracion terminada."
echo "Comprueba la web en: http://IP_DE_TU_SERVIDOR/ticketing"
