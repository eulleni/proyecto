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
|------------------|----------------------|
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

- Introducción  
- Instalación del sistema  
- Entorno gráfico (Xubuntu)  
- Configuración de red  
- Gestión de usuarios  
- Permisos y directorios  
- Acceso remoto  
- Servidor web (Apache)  
- Base de datos  
- Aplicación web (Sistema de tickets)  
- Webmin  
- Sistema de correo  
- AnyDesk  
- Problemas y soluciones  
- Mejoras futuras  
- Conclusión  

---

# 🚀 Introducción

Este proyecto consiste en el diseño e implementación de una **intranet empresarial completa basada en Linux**, integrando múltiples servicios reales utilizados en entornos profesionales.

El objetivo principal es simular el funcionamiento de una empresa donde todos los servicios (usuarios, web, correo, administración remota) están centralizados en un único servidor.

El sistema permite:

- Gestión de usuarios y grupos  
- Control de permisos por departamentos  
- Administración remota del sistema  
- Despliegue de aplicaciones web  
- Sistema de tickets para incidencias  
- Servicio de correo interno  

Se han utilizado herramientas reales del mundo laboral, con el objetivo de adquirir experiencia práctica en administración de sistemas.

---

# 💿 Instalación del sistema

Se instaló **Ubuntu Server 22.04 LTS**, seleccionado por su estabilidad, seguridad y uso extendido en entornos profesionales.

Tras la instalación, se actualizó el sistema completamente:

sudo apt update && sudo apt upgrade

Esto garantiza que el sistema esté actualizado y libre de vulnerabilidades.

---

# 🖥️ Entorno gráfico (Xubuntu)

Se instaló Xubuntu Desktop:

sudo apt install xubuntu-desktop

Se eligió este entorno porque:

- Es ligero  
- Consume pocos recursos  
- Permite administración visual  

Se utilizó principalmente para:

- Facilitar la gestión del servidor  
- Realizar capturas para el proyecto  
- Permitir acceso remoto gráfico mediante AnyDesk  

---

# 🌐 Configuración de red

Se configuró una IP estática mediante Netplan para evitar cambios de dirección IP.

Archivo de configuración:

/etc/netplan/00-installer-config.yaml

Aplicación de cambios:

sudo netplan apply

Esto permite que el servidor tenga siempre la misma IP, lo cual es fundamental para acceder a servicios como:

- Webmin  
- Apache  
- Roundcube  

---

# 👤 Gestión de usuarios

Inicialmente se utilizó:

sudo useradd usuario

Pero este método presenta problemas:

- No crea el directorio /home  
- No asigna un shell válido  

Por ello, se utilizó Webmin para completar la configuración:

- Creación automática de /home  
- Asignación del shell `/bin/bash`  
- Configuración de contraseñas  

Ejemplo de usuario:

sudo adduser cliente1

---

# 👥 Permisos y directorios

Se crearon directorios en `/srv` para simular departamentos:

sudo mkdir /srv/ventas  
sudo chown root:ventas /srv/ventas  
sudo chmod 770 /srv/ventas  

Explicación:

- `770` → solo el grupo tiene acceso  
- `root:ventas` → control por grupo  

Esto permite una gestión segura de los datos.

---

# 🔐 Acceso remoto

## SSH

Permite administrar el servidor remotamente:

systemctl status ssh  

Conexión:

ssh usuario@IP

---

## WinSCP

Configuración:

- Protocolo: SFTP  
- Puerto: 22  

Permite:

- Transferir archivos  
- Editar directamente en el servidor  
- Gestionar permisos  

---

# 🌍 Servidor web (Apache)

Instalación:

sudo apt install apache2  

Comprobación:

systemctl status apache2  

Ruta web:

/var/www/html/

Acceso:

http://IP_DEL_SERVIDOR  

Aquí se alojan:

- phpMyAdmin  
- Ticketing  
- Roundcube  

---

# 🗄️ Base de datos

Instalación:

sudo apt install mysql-server  

Acceso:

sudo mysql  

phpMyAdmin:

http://IP/phpmyadmin  

Se utilizó para gestionar bases de datos de forma visual.

---

# 🌍 Aplicación web (Sistema de tickets)

Ubicación:

/var/www/html/ticketing  

Funcionalidades:

- Crear incidencias  
- Ver estado  
- Asignar técnicos  
- Cambiar estado (abierto, en progreso, cerrado)  
- Prioridad automática  

Panel admin:

/admin_ver.php  

Protegido mediante login.

Simula un sistema real de soporte técnico empresarial.

---

# ⚙️ Webmin

Acceso:

https://IP:10000  

Permite:

- Gestión de usuarios  
- Configuración de servicios  
- Monitorización  

Problema encontrado:

Error GPG del repositorio  

Solución:

Actualizar repositorio y clave  

---

# 📧 Sistema de correo

Instalación:

sudo apt install postfix dovecot-imapd dovecot-pop3d roundcube  

Componentes:

- Postfix → envío de correos  
- Dovecot → recepción  
- Roundcube → interfaz web  

Acceso:

http://IP/roundcube  

---

## 📬 Buzones de correo

Creación:

adduser usuario  
maildirmake.dovecot ~/Maildir  

Esto permite almacenar correos en formato Maildir.

---

# 🖥️ Acceso remoto con AnyDesk

Instalación:

sudo apt install anydesk  

Uso:

- Obtener ID  
- Conectar desde otro dispositivo  

Permite:

- Control completo del sistema  
- Acceso desde móvil  
- Gestión gráfica  

Muy útil para pruebas y administración fuera de clase.

---

# ⚠️ Problemas y soluciones

### Error Roundcube (Internal Error)
Solución:
Configuración manual de base de datos en config.inc.php  

---

### Error 404 Roundcube
sudo ln -s /var/lib/roundcube /var/www/html/roundcube  

---

### Error SMTP
sudo systemctl restart postfix  

---

### Error SMTP Authentication failed
Configuración correcta de usuario SMTP  

---

### Problema IP dinámica
Configuración mediante Netplan  

---

### Error MySQL acceso root
sudo mysql  

---

# 🚧 Mejoras futuras

- Notificaciones automáticas por correo  
- Sistema de roles  
- Mejora visual con Bootstrap  
- Integración con servicios externos  
- Migración a AWS  

---

# ✅ Conclusión

Se ha implementado una intranet completa integrando múltiples servicios reales:

- Administración de usuarios  
- Servicios web  
- Sistema de tickets  
- Correo interno  
- Acceso remoto  

Además, se han resuelto problemas reales que han permitido adquirir experiencia práctica en administración de sistemas.

Resultado final:

- Sistema funcional  
- Escalable  
- Aplicable a entornos empresariales reales  
