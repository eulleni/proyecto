# 🖥️ Intranet Empresarial - Proyecto Intermodular ASIX

![Ubuntu](https://img.shields.io/badge/Ubuntu-22.04-orange)
![Apache](https://img.shields.io/badge/Apache-2.4-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-purple)
![MySQL](https://img.shields.io/badge/MySQL-Database-blue)
![Status](https://img.shields.io/badge/Status-Completed-success)

---

##  Descripción

Este proyecto consiste en la implementación de una **intranet empresarial completa** sobre un servidor Linux, integrando servicios de red, administración remota, aplicación web y sistema de correo interno.

El objetivo es simular un entorno real de empresa donde se centralizan servicios y gestión de incidencias.

---

##  Objetivos

- Implementar un servidor Linux funcional
- Gestionar usuarios y permisos
- Desplegar servicios web
- Desarrollar una aplicación interna (tickets)
- Configurar un sistema de correo interno
- Permitir administración remota completa

---

##  Stack Tecnológico

| Categoría | Tecnología |
|----------|-----------|
| Sistema Operativo | Ubuntu Server 22.04 |
| Entorno gráfico | Xubuntu Desktop |
| Web Server | Apache2 |
| Backend | PHP |
| Base de datos | MySQL / MariaDB |
| Gestión DB | phpMyAdmin |
| Administración | Webmin |
| Correo | Postfix + Dovecot |
| Webmail | Roundcube |
| Acceso remoto | SSH, WinSCP, AnyDesk |

---

##  Índice

- [Instalación del sistema](#-instalación-del-sistema)
- [Configuración de red](#-configuración-de-red)
- [Gestión de usuarios](#-gestión-de-usuarios)
- [Permisos y grupos](#-permisos-y-grupos)
- [Acceso remoto](#-acceso-remoto)
- [Servidor web](#-servidor-web)
- [Aplicación de tickets](#-aplicación-de-tickets)
- [Base de datos](#-base-de-datos)
- [Sistema de correo](#-sistema-de-correo)
- [Problemas y soluciones](#-problemas-y-soluciones)
- [Estado final](#-estado-final)
- [Mejoras futuras](#-mejoras-futuras)

---

##  Instalación del sistema

Se utilizó **Ubuntu Server 22.04 LTS**, elegido por su estabilidad y uso en entornos profesionales.

Actualización del sistema:
sudo apt update && sudo apt upgrade

Instalación de entorno gráfico ligero:
sudo apt install xubuntu-desktop

Esto permitió facilitar la administración visual y la documentación del proyecto.

---

##  Configuración de red

Se configuró una IP estática mediante Netplan para evitar cambios en la dirección IP del servidor.

Archivo de configuración:
/etc/netplan/00-installer-config.yaml

Aplicación:
sudo netplan apply

Esto garantiza acceso constante al servidor dentro de la red.

---

##  Gestión de usuarios

Creación inicial:
sudo useradd usuario

Posteriormente se utilizó **Webmin** para completar la configuración:

- Creación de directorios /home
- Asignación de shell (/bin/bash)
- Configuración de contraseñas

Usuarios creados:
- cliente1
- soporte

---

##  Permisos y grupos

Se crearon grupos para estructurar el acceso:

- secretaria
- ventas

Directorios:
/srv/

Configuración:
chmod 770
chown root:grupo

Esto garantiza acceso restringido por departamentos.

---

##  Acceso remoto

### SSH
Permite administración por terminal:
sudo systemctl status ssh

---

### WinSCP
Utilizado para transferencia de archivos vía SFTP:

- Subida de archivos
- Edición remota
- Gestión de permisos

---

### AnyDesk

Se utilizó AnyDesk para acceso remoto gráfico completo.

Permite:
- Control total del servidor
- Acceso desde móvil o PC
- Gestión del entorno gráfico

Especialmente útil para pruebas en clase y administración rápida.

---

##  Administración con Webmin

Acceso:
https://IP:10000

Permite:
- Gestión de usuarios
- Configuración de servicios
- Administración del sistema

Se resolvió un problema inicial de repositorio GPG durante la instalación.

---

##  Servidor web

Instalación:
sudo apt install apache2

Directorio principal:
/var/www/html/

Servicios desplegados:
- phpMyAdmin
- Aplicación de tickets
- Roundcube

---

##  Aplicación de tickets

Aplicación desarrollada en **PHP + MySQL**.

Funcionalidades:
- Creación de incidencias
- Seguimiento de tickets
- Estados (abierto, en progreso, cerrado)
- Asignación de técnicos
- Panel de administración

Ubicación:
/var/www/html/ticketing

Simula un sistema real de soporte técnico.

---

##  Base de datos

Uso de MySQL para:

- Tickets
- Usuarios
- Roundcube

Gestión mediante phpMyAdmin.

Acceso root solucionado con:
sudo mysql

---

##  Sistema de correo

Implementación completa:

### Postfix (SMTP)
Servidor de envío de correos.

### Dovecot (IMAP)
Servidor de recepción.

### Roundcube
Interfaz web:
http://IP/roundcube

---

##  Buzones de correo

Creación:
maildirmake.dovecot ~/Maildir

Permite almacenamiento estructurado de correos.

---

##  Problemas y soluciones

### Roundcube Internal Error
Problema:
Fallo de conexión a base de datos.

Solución:
Configuración manual en config.inc.php

---

### Error 404 Roundcube
Problema:
Ruta inexistente en Apache.

Solución:
sudo ln -s /var/lib/roundcube /var/www/html/roundcube

---

### Error SMTP Connection failed
Problema:
Servidor no accesible.

Solución:
sudo systemctl restart postfix

---

### Error SMTP Authentication failed
Problema:
Fallo de autenticación.

Solución:
$config['smtp_user'] = '%u';
$config['smtp_pass'] = '%p';

---

### IP dinámica
Solución:
Configuración con Netplan.

---

### Error MySQL root
Solución:
sudo mysql

---

##  Estado final

Sistema completamente funcional:

- Gestión de usuarios ✔
- Acceso remoto ✔
- Webmin ✔
- Aplicación de tickets ✔
- Correo interno ✔

---

##  Mejoras futuras

- Notificaciones automáticas por correo
- Mejora de interfaz (Bootstrap)
- Roles avanzados
- Despliegue en AWS
- Seguridad avanzada

---

##  Conclusión

Se ha desarrollado una intranet completa integrando múltiples servicios en un único servidor Linux.

El proyecto ha permitido adquirir experiencia práctica en:

- Administración de sistemas
- Redes
- Servicios web
- Resolución de errores reales

El resultado es un sistema funcional, escalable y aplicable a entornos empresariales reales.
