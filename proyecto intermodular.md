#  Proyecto Intermodular - Intranet Empresarial Linux

##  Autor

**Eugenio Requena Castillo**
**2º ASIX - IES Serra Perenxisa**

---

![Ubuntu](https://img.shields.io/badge/Ubuntu-22.04-orange)
![Apache](https://img.shields.io/badge/Apache-2.4-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-purple)
![MySQL](https://img.shields.io/badge/MySQL-Database-blue)
![Status](https://img.shields.io/badge/Status-Completed-success)

---

Stack Tecnológico

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


 Índice

- Instalación del sistema
- Configuración de red
- Gestión de usuarios
- Permisos y grupos
- Acceso remoto
- Servidor web
- Aplicación de tickets
- Base de datos
- Sistema de correo
- Problemas y soluciones
- Estado final
- Mejoras futuras

#  Introducción

Este proyecto intermodular consiste en el diseño e implementación de una **intranet empresarial basada en un servidor Linux**, integrando múltiples servicios reales utilizados en entornos profesionales.

El sistema desarrollado permite:

* Gestión de usuarios
* Control de permisos
* Administración remota
* Servicio de correo interno
* Aplicación web de gestión de incidencias (sistema de tickets)

Se han utilizado tecnologías como:

* Ubuntu Server 22.04 LTS
* Apache
* PHP
* MySQL / MariaDB
* Postfix
* Dovecot
* Roundcube
* Webmin
* SSH
* WinSCP
* AnyDesk

---

#  Instalación del sistema

Se instaló **Ubuntu Server 22.04 LTS**, seleccionando la instalación de servidor estándar desde el instalador oficial.

Tras la instalación, se realizó la actualización completa del sistema:

```
sudo apt update && sudo apt upgrade
```

---

#  Entorno gráfico (Xubuntu)

Para facilitar la administración del sistema y poder trabajar de forma más visual, se instaló Xubuntu Desktop:

```
sudo apt install xubuntu-desktop
```

Se trata de un entorno ligero que permite gestionar el sistema sin comprometer el rendimiento.

---

#  Servicio de correo

Se implementó un sistema de correo interno utilizando:

* **Postfix** → SMTP
* **Dovecot** → IMAP
* **Roundcube** → Webmail

### Problemas solucionados:

* Error de conexión a base de datos
* Error SMTP connection failed
* Error SMTP authentication failed
* Error 404 en Roundcube

---

#  Gestión de usuarios

Inicialmente se utilizó `useradd`, pero se completó la configuración con Webmin para:

* Crear directorios personales
* Asignar shell `/bin/bash`
* Configurar contraseñas

También se crearon grupos como:

* secretaria
* ventas

---

#  Permisos y directorios

Se crearon directorios en `/srv` para cada departamento y se configuraron permisos:

```
chown root:grupo carpeta
chmod 770 carpeta
```

Esto permite acceso solo a usuarios del grupo correspondiente.

---

#  Acceso remoto (SSH + WinSCP)

## SSH

Permite administrar el servidor remotamente:

```
systemctl status ssh
```

## WinSCP

Permite:

* Transferir archivos
* Editar ficheros
* Gestionar permisos

---

#  Webmin

Herramienta de administración web del sistema.

### Problema:

Error de clave GPG

### Solución:

Actualizar repositorio y clave

---

#  Gestión avanzada de usuarios

Desde Webmin:

* Creación automática de `/home`
* Expiración de contraseñas
* Asignación a grupos secundarios

---

#  Aplicación web (Sistema de tickets)

Aplicación desarrollada en PHP y MySQL.

### Funcionalidades:

* Crear incidencias
* Ver estado
* Asignar técnicos
* Cambiar estado
* Prioridad automática

### Panel admin:

* Protegido por sesión
* Gestión centralizada

---

#  Acceso remoto con AnyDesk

Permite:

* Control gráfico del servidor
* Acceso desde móvil o PC
* Administración remota completa

---

#  Mejoras futuras

* Notificaciones por correo
* Sistema de roles
* Interfaz con Bootstrap
* Integración externa
* Migración a AWS

---

#  Conclusión

Se ha desarrollado una intranet completa integrando múltiples servicios.

Se han resuelto problemas reales de:

* Correo
* Permisos
* Seguridad
* Configuración

 Resultado:

* Sistema funcional
* Escalable
* Preparado para entornos reales

---
