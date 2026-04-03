# 🖥️ Proyecto Intermodular - Intranet Empresarial Linux

## 👤 Autor

**Eugenio Requena Castillo**
**2º ASIX - IES Serra Perenxisa**

---

![Ubuntu](https://img.shields.io/badge/Ubuntu-22.04-orange)
![Apache](https://img.shields.io/badge/Apache-2.4-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-purple)
![MySQL](https://img.shields.io/badge/MySQL-Database-blue)
![Status](https://img.shields.io/badge/Status-Completed-success)

---

# 🧱 Stack Tecnológico

| Categoría         | Tecnología           |
| ----------------- | -------------------- |
| Sistema Operativo | Ubuntu Server 22.04  |
| Entorno gráfico   | Xubuntu Desktop      |
| Web Server        | Apache2              |
| Backend           | PHP                  |
| Base de datos     | MySQL / MariaDB      |
| Gestión DB        | phpMyAdmin           |
| Administración    | Webmin               |
| Correo            | Postfix + Dovecot    |
| Webmail           | Roundcube            |
| Acceso remoto     | SSH, WinSCP, AnyDesk |

---

# 📌 Índice

* [Introducción](#-introducción)
* [Instalación del sistema](#-instalación-del-sistema)
* [Entorno gráfico](#-entorno-gráfico-xubuntu)
* [Servicio de correo](#-servicio-de-correo)
* [Gestión de usuarios](#-gestión-de-usuarios)
* [Permisos y directorios](#-permisos-y-directorios)
* [Acceso remoto](#-acceso-remoto-ssh--winscp)
* [Servidor web](#-servidor-web-apache)
* [Aplicación de tickets](#-aplicación-web-sistema-de-tickets)
* [Base de datos](#-base-de-datos)
* [Webmin](#-webmin)
* [AnyDesk](#-acceso-remoto-con-anydesk)
* [Problemas y soluciones](#-problemas-y-soluciones)
* [Mejoras futuras](#-mejoras-futuras)
* [Conclusión](#-conclusión)

---

# 🚀 Introducción

Este proyecto consiste en la creación de una **intranet empresarial completa en Linux**, integrando servicios reales de administración de sistemas.

Incluye:

* Gestión de usuarios
* Control de permisos
* Sistema de correo
* Aplicación web de tickets
* Acceso remoto completo

---

# 💿 Instalación del sistema

```bash
sudo apt update && sudo apt upgrade
```

✔️ Mantiene el sistema actualizado y seguro.

---

# 🖥️ Entorno gráfico (Xubuntu)

```bash
sudo apt install xubuntu-desktop
```

✔️ Permite:

* Administración visual
* Uso remoto gráfico (AnyDesk)

---

# 📧 Servicio de correo

## Instalación

```bash
sudo apt install postfix dovecot-imapd dovecot-pop3d roundcube
```

---

## 📥 Acceso a Roundcube

Desde navegador:

```
http://IP_DEL_SERVIDOR/roundcube
```

Ejemplo:

```
http://192.168.1.37/roundcube
```

---

## 📬 Crear buzones

```bash
adduser usuario
maildirmake.dovecot ~/Maildir
```

---

## 🔧 Reiniciar servicios

```bash
sudo systemctl restart postfix
sudo systemctl restart dovecot
```

---

# 👥 Gestión de usuarios

## Crear usuario

```bash
sudo adduser nombre_usuario
```

## Añadir a grupo

```bash
sudo usermod -aG grupo usuario
```

## Ver grupos

```bash
groups usuario
```

---

# 🔐 Permisos y directorios

```bash
sudo mkdir /srv/ventas
sudo chown root:ventas /srv/ventas
sudo chmod 770 /srv/ventas
```

✔️ Solo usuarios del grupo acceden.

---

# 🌐 Acceso remoto (SSH + WinSCP)

## 🔐 SSH

### Conexión desde otro equipo:

```bash
ssh usuario@IP_DEL_SERVIDOR
```

Ejemplo:

```bash
ssh erc01@192.168.1.37
```

---

### Comprobar servicio:

```bash
systemctl status ssh
```

---

## 📂 WinSCP

Configuración:

* Protocolo: SFTP
* IP: 192.168.1.37
* Puerto: 22
* Usuario + contraseña

✔️ Permite:

* Transferir archivos
* Editar directamente en servidor

---

# 🌍 Servidor web (Apache)

## Instalación

```bash
sudo apt install apache2
```

## Comprobar estado

```bash
systemctl status apache2
```

## Ruta web

```
/var/www/html/
```

## Acceso desde navegador

```
http://IP_DEL_SERVIDOR
```

---

# 🗄️ Base de datos

## Instalación

```bash
sudo apt install mysql-server
```

## Acceso

```bash
sudo mysql
```

## phpMyAdmin

```
http://IP_DEL_SERVIDOR/phpmyadmin
```

---

# 🌍 Aplicación web (Sistema de tickets)

Ubicación:

```
/var/www/html/ticketing
```

## Acceso

```
http://IP/ticketing
```

---

## Funcionalidades

* Crear tickets
* Ver estado
* Asignar técnicos
* Cambiar estado
* Prioridad automática

---

## Panel admin

```
http://IP/ticketing/admin_ver.php
```

✔️ Protegido por login

---

# ⚙️ Webmin

## Instalación

```bash
sudo apt install webmin
```

---

## Acceso

```
https://IP_DEL_SERVIDOR:10000
```

Ejemplo:

```
https://192.168.1.37:10000
```

---

## Funcionalidades

* Gestión de usuarios
* Monitorización
* Configuración de servicios

---

# 🖥️ Acceso remoto con AnyDesk

## Instalación

```bash
sudo apt install anydesk
```

---

## Uso

* Abrir aplicación
* Obtener ID
* Conectarse desde otro dispositivo

✔️ Permite:

* Control gráfico completo
* Acceso desde móvil

---

# ⚠️ Problemas y soluciones

## ❌ Error SMTP

✔️ Revisar Postfix y puertos

## ❌ Error autenticación

✔️ Configurar usuario correctamente

## ❌ Error GPG Webmin

✔️ Actualizar repositorio

## ❌ Error 404 Roundcube

✔️ Crear enlace simbólico

---

# 🚧 Mejoras futuras

* Notificaciones automáticas por correo
* Sistema de roles
* Interfaz Bootstrap
* Integración externa
* Migración a AWS

---

# ✅ Conclusión

Se ha implementado una intranet completa integrando múltiples servicios reales.

✔️ Resultado:

* Sistema funcional
* Escalable
* Aplicable en empresa real

---
