# Intranet Empresarial en Linux

Proyecto intermodular de 2º ASIX: una intranet empresarial basada en Linux con aplicación web de ticketing, base de datos, correo interno, administración remota y copias de seguridad automatizadas.

## Tecnologias utilizadas

- Ubuntu Server / Xubuntu en entorno de pruebas
- Apache2
- PHP
- MySQL/MariaDB
- phpMyAdmin
- Postfix, Dovecot y Roundcube
- Webmin
- SSH, SFTP/WinSCP
- Cron, mysqldump, tar y scp
- VirtualBox

## Arquitectura resumida

El proyecto simula una intranet local con dos maquinas virtuales:

- Servidor principal: aloja Apache, PHP, MariaDB, la aplicacion de ticketing, correo, Webmin y phpMyAdmin.
- Servidor de backups: recibe copias de seguridad de la base de datos y de la carpeta web.

La aplicacion esta pensada como intranet local de laboratorio. El relay SMTP permite enviar correos, pero no publica la web en Internet.

## Estructura del repositorio

```text
README.md
docs/
  proyecto-intermodular.md
app/
  archivos PHP/CSS/JS de la aplicacion
  db.example.php
database/
  schema.sql
  seed.example.sql
scripts/
  backup.example.sh
  restaurar_ticketing.example.sh
.gitignore
```

## Instalacion rapida

1. Instala Apache, PHP y MariaDB en el servidor.

```bash
sudo apt update
sudo apt install apache2 mariadb-server php libapache2-mod-php php-mysql
sudo systemctl enable --now apache2 mariadb
```

2. Crea la base de datos y el usuario de la aplicacion.

```bash
sudo mysql
```

Dentro de MariaDB/MySQL:

```sql
CREATE DATABASE ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'usuario_ticketing'@'localhost' IDENTIFIED BY 'CAMBIAR_PASSWORD_DB';
GRANT ALL PRIVILEGES ON ticketing.* TO 'usuario_ticketing'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

3. Importa la estructura de la base de datos:

```bash
mysql -u root -p ticketing < database/schema.sql
```

4. Opcionalmente, prepara e importa datos ficticios de prueba.

`seed.example.sql` no contiene hashes reales. Antes de usar esos usuarios para iniciar sesion, sustituye `HASH_DEMO_CAMBIAR` por hashes nuevos generados en tu propio entorno, por ejemplo:

```bash
php app/generar_hash.php "PasswordDemoCambiar123!"
```

Despues de sustituir los placeholders:

```bash
mysql -u root -p ticketing < database/seed.example.sql
```

5. Copia la aplicacion a la ruta del servidor web.

```bash
sudo mkdir -p /var/www/html/ticketing
sudo cp -r app/. /var/www/html/ticketing/
```

## Configuracion de la base de datos

El archivo real `app/db.php` no se sube al repositorio porque contiene datos locales. Para configurar la aplicacion desplegada:

```bash
sudo cp /var/www/html/ticketing/db.example.php /var/www/html/ticketing/db.php
```

Despues edita `/var/www/html/ticketing/db.php` con los datos reales de tu entorno o define variables de entorno:

```bash
export DB_HOST="localhost"
export DB_NAME="ticketing"
export DB_USER="usuario_ticketing"
export DB_PASSWORD="CAMBIAR_PASSWORD_DB"
```

Aplica permisos basicos para Apache:

```bash
sudo chown -R www-data:www-data /var/www/html/ticketing
sudo find /var/www/html/ticketing -type d -exec chmod 755 {} \;
sudo find /var/www/html/ticketing -type f -exec chmod 644 {} \;
sudo systemctl restart apache2
```

La aplicacion quedara disponible en:

```text
http://IP_DEL_SERVIDOR/ticketing
```

## Scripts de backup y restauracion

Los scripts incluidos son ejemplos seguros:

- `scripts/backup.example.sh`
- `scripts/restaurar_ticketing.example.sh`

Antes de usarlos, copia cada archivo a una version local sin `.example` y ajusta las variables:

```bash
cp scripts/backup.example.sh scripts/backup.sh
cp scripts/restaurar_ticketing.example.sh scripts/restaurar_ticketing.sh
```

Variables principales:

- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `WEB_DIR`
- `BACKUP_USER`
- `BACKUP_HOST`
- `BACKUP_DIR`

Para usar backups en un entorno real:

1. Crea un usuario de base de datos con permisos de lectura sobre `ticketing`.
2. Configura acceso SSH con clave publica hacia el servidor de backups.
3. Crea la carpeta remota indicada en `BACKUP_DIR`.
4. Da permisos de ejecucion a las copias locales:

```bash
chmod +x scripts/backup.sh scripts/restaurar_ticketing.sh
```

No subas los scripts locales con contraseñas, usuarios reales o IPs privadas.

## Correo

La aplicacion utiliza `mail()` desde PHP mediante `app/mail_helper.php`. Para que las notificaciones y recuperacion de contraseña funcionen, el servidor debe tener un servicio de correo configurado, por ejemplo Postfix con relay SMTP. Si no se configura correo, la aplicacion web puede funcionar, pero no enviara emails reales.

## Documentacion completa

La memoria completa del proyecto esta en:

[docs/proyecto-intermodular.md](docs/proyecto-intermodular.md)
