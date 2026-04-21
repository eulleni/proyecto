# Proyecto Intermodular - Intranet Empresarial Linux

## Autor

**Eugenio Requena Castillo**  
2º ASIX - IES Serra Perenxisa  

---

![Ubuntu](https://img.shields.io/badge/Ubuntu-22.04-orange)
![Apache](https://img.shields.io/badge/Apache-2.4-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-purple)
![MySQL](https://img.shields.io/badge/MySQL-Database-blue)
![Status](https://img.shields.io/badge/Status-Completed-success)

---

# Introducción

Este proyecto consiste en el diseño e implementación de una intranet empresarial basada en Linux, integrando múltiples servicios utilizados en entornos profesionales.

El sistema final está compuesto por:

- Un **servidor principal**, encargado de los servicios web, base de datos, correo y administración.
- Un **servidor de backups**, dedicado exclusivamente al almacenamiento de copias de seguridad.

El objetivo principal es simular el funcionamiento de una empresa real, donde los servicios internos se encuentran organizados, securizados y preparados para facilitar la administración del sistema y la gestión de incidencias.

---

# Stack Tecnológico

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
| Acceso remoto | SSH, WinSCP |
| Backups | mysqldump, tar, SCP |

---

# Configuración de red

La arquitectura final del proyecto utiliza **dos adaptadores de red**:

## Adaptador 1: NAT
Permite acceso a Internet para:
- Instalación de paquetes
- Actualizaciones
- Uso de servicios externos (Gmail SMTP)

## Adaptador 2: Red interna
Permite la comunicación entre máquinas virtuales.

## Direcciones IP finales

### Servidor principal
- 192.168.100.10

### Servidor de backups
- 192.168.100.20

Esta arquitectura permite:
- Separar tráfico interno y externo
- Mejorar la estabilidad del sistema
- Simular entorno empresarial real

---

# Acceso a los servicios

## Webmin
https://IP_DEL_SERVIDOR:10000  
Usuario: admin  

## Aplicación Ticketing
http://IP_DEL_SERVIDOR/tickets  

## Roundcube (Correo)
http://IP_DEL_SERVIDOR/webmail  

## Servidor de backups
Acceso por SSH:

```bash
ssh erc01@192.168.100.20
```

---

# Aplicación Web (Sistema de Ticketing)

Aplicación desarrollada en PHP y MySQL.

## Funcionalidades

### Cliente
- Registro e inicio de sesión
- Crear tickets
- Ver estado de incidencias
- Recuperar contraseña
- Solicitar actualización

### Administrador
- Ver todos los tickets
- Editar tickets
- Cambiar estado
- Asignar técnico
- Registrar modificaciones

## Campos incluidos en tickets
- Empresa
- Persona de contacto
- Teléfono
- Sistema afectado
- Usuarios afectados
- Nivel de bloqueo
- Solución temporal

---

# Sistema de correo

Se ha implementado un sistema de correo interno con:

- Postfix (SMTP)
- Dovecot (IMAP)
- Roundcube (webmail)

## Correos automáticos
- Creación de tickets
- Recuperación de contraseña
- Solicitudes de actualización

## Configuración SMTP

Se utiliza Gmail como relay:

- Cuenta: eugeenproject@gmail.com
- Autenticación mediante contraseña de aplicación

---

# Servidor de copias de seguridad

Se ha implementado una segunda máquina virtual dedicada a backups.

## Objetivo
Separar los datos del servidor principal para mayor seguridad.

## Comunicación
Mediante red interna + SSH

---

## Acceso SSH sin contraseña

```bash
ssh-keygen
ssh-copy-id erc01@192.168.100.20
```

---

# Script de backup

Archivo: backup.sh

Funciones:
1. Backup de base de datos
2. Compresión de archivos web
3. Envío al servidor de backups
4. Limpieza de temporales

```bash
mysqldump -u backup -p'********' ticketing > ticketing.sql
tar -czf web.tar.gz /var/www/html/ticketing
scp ticketing.sql erc01@192.168.100.20:/home/erc01/backups
scp web.tar.gz erc01@192.168.100.20:/home/erc01/backups
```

---

# Automatización con cron

```bash
crontab -e
```

```bash
0 2 * * * /home/erc01/backup.sh
```

Backups diarios a las 2:00 AM.

---

# Seguridad

Medidas implementadas:

- Uso de usuarios sin root
- Contraseñas cifradas
- Control de accesos
- Backups automatizados

---

# Conclusión

El proyecto representa una simulación de una intranet empresarial, integrando múltiples servicios en un entorno Linux.

Se ha conseguido:

- Centralizar servicios
- Automatizar procesos
- Implementar seguridad básica
- Desarrollar una aplicación funcional

