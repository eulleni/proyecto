# Intranet Empresarial en Linux

Sistema de ticketing y gestión de incidencias desarrollado sobre una infraestructura Linux con servicios web, base de datos, correo, administración remota y backups automatizados.

> Proyecto Intermodular de 2º ASIX basado en Ubuntu Server, aplicación de ticketing, correo corporativo, administración remota y sistema de copias de seguridad.

---

## Tecnologías utilizadas

![Ubuntu](https://img.shields.io/badge/Ubuntu_Server-22.04-E95420?style=for-the-badge&logo=ubuntu&logoColor=white)
![Apache](https://img.shields.io/badge/Apache2-Web-D22128?style=for-the-badge&logo=apache&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-Backend-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%2FMariaDB-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Postfix](https://img.shields.io/badge/Postfix-SMTP-2E6DB4?style=for-the-badge)
![Dovecot](https://img.shields.io/badge/Dovecot-IMAP-0A66C2?style=for-the-badge)
![Roundcube](https://img.shields.io/badge/Roundcube-Webmail-37A5CC?style=for-the-badge)
![Webmin](https://img.shields.io/badge/Webmin-Admin-0080FF?style=for-the-badge)
![phpMyAdmin](https://img.shields.io/badge/phpMyAdmin-Database_Admin-F6C915?style=for-the-badge)
![SSH](https://img.shields.io/badge/SSH-Remote_Access-4D4D4D?style=for-the-badge&logo=gnubash&logoColor=white)
![Backups](https://img.shields.io/badge/Backups-Cron%20%2B%20SCP-00B894?style=for-the-badge)
![VirtualBox](https://img.shields.io/badge/VirtualBox-Virtualization-183A61?style=for-the-badge&logo=virtualbox&logoColor=white)

---

## Índice

- [Descripción del proyecto](#descripción-del-proyecto)
- [Objetivos del proyecto](#objetivos-del-proyecto)
- [Arquitectura general del sistema](#arquitectura-general-del-sistema)
- [Acceso local a la aplicación](#acceso-local-a-la-aplicación)
- [Servicios implementados](#servicios-implementados)
- [Aplicación de ticketing](#aplicación-de-ticketing)
- [Funcionamiento del sistema de tickets](#funcionamiento-del-sistema-de-tickets)
- [Estructura básica de la aplicación](#estructura-básica-de-la-aplicación)
- [Base de datos](#base-de-datos)
- [Acceso a phpMyAdmin](#acceso-a-phpmyadmin)
- [Webmin](#webmin)
- [Sistema de correo interno](#sistema-de-correo-interno)
- [Acceso a Roundcube Webmail](#acceso-a-roundcube-webmail)
- [Relay SMTP con Gmail](#relay-smtp-con-gmail)
- [Notificaciones automáticas](#notificaciones-automáticas)
- [Gestión de usuarios y permisos](#gestión-de-usuarios-y-permisos)
- [Acceso remoto con SSH y WinSCP](#acceso-remoto-con-ssh-y-winscp)
- [Acceso remoto con AnyDesk](#acceso-remoto-con-anydesk)
- [Servidor de backups](#servidor-de-backups)
- [Script de copias de seguridad](#script-de-copias-de-seguridad)
- [Automatización con cron](#automatización-con-cron)
- [Seguridad aplicada](#seguridad-aplicada)
- [Problemas encontrados y soluciones](#problemas-encontrados-y-soluciones)
- [Conclusión](#conclusión)

## Descripción del proyecto

Este proyecto consiste en el diseño e implementación de una **intranet empresarial basada en Linux**, orientada a simular una infraestructura interna de una empresa con servicios web, base de datos, correo, administración remota, sistema de tickets y copias de seguridad automatizadas.

La solución está formada por dos máquinas principales:

- **Servidor principal**, encargado de alojar la aplicación web, la base de datos, el sistema de correo, Webmin, phpMyAdmin y los servicios de administración.
- **Servidor de backups**, dedicado exclusivamente a almacenar copias de seguridad de la base de datos y de los archivos de la aplicación.

El objetivo principal del proyecto es centralizar distintos servicios empresariales en un entorno Linux y añadir una aplicación propia de **gestión de incidencias**, permitiendo a los usuarios crear tickets, consultar su estado y recibir notificaciones por correo electrónico.

La aplicación de ticketing permite registrar usuarios, crear incidencias, consultar tickets, calcular prioridades automáticamente, asignar técnicos, modificar estados y enviar correos automáticos relacionados con el seguimiento de incidencias y la recuperación de contraseña.

Además, el proyecto incluye un sistema de correo interno mediante **Postfix, Dovecot y Roundcube**, junto con una configuración de **relay SMTP con Gmail** para permitir el envío de correos reales al exterior mediante una cuenta autenticada con contraseña de aplicación.

Para mejorar la disponibilidad del sistema, se ha añadido una segunda máquina virtual dedicada a backups. Esta máquina recibe copias automáticas de la base de datos y de la aplicación web, generadas desde el servidor principal mediante un script programado con `cron`.

---

## Objetivos del proyecto

Los objetivos principales del proyecto son:

- Implementar una intranet empresarial funcional sobre Linux.
- Configurar un servidor web con Apache y PHP.
- Crear una aplicación web propia para la gestión de incidencias.
- Utilizar MySQL/MariaDB como sistema de base de datos.
- Administrar la base de datos mediante phpMyAdmin.
- Configurar un sistema de correo con Postfix, Dovecot y Roundcube.
- Permitir el envío de notificaciones reales mediante Gmail como relay SMTP.
- Administrar el sistema mediante Webmin.
- Permitir acceso remoto mediante SSH, WinSCP y AnyDesk.
- Organizar usuarios y permisos mediante grupos del sistema.
- Implementar un servidor independiente de copias de seguridad.
- Automatizar backups mediante `cron`.
- Simular una infraestructura empresarial real usando máquinas virtuales.

---

## Arquitectura general del sistema

La arquitectura final del proyecto está compuesta por dos servidores virtualizados en VirtualBox:

| Máquina | Función |
|---|---|
| Servidor principal | Aloja Apache, PHP, MySQL/MariaDB, phpMyAdmin, Postfix, Dovecot, Roundcube, Webmin y la aplicación de ticketing |
| Servidor de backups | Almacena copias automáticas de la base de datos y de la aplicación web |

Para la red se utilizaron dos adaptadores:

| Adaptador | Uso |
|---|---|
| NAT | Salida a Internet para actualizaciones, instalación de paquetes y relay SMTP externo |
| Red interna | Comunicación privada entre el servidor principal y el servidor de backups |

IPs utilizadas en la red interna:

| Máquina | IP |
|---|---|
| Servidor principal | `192.168.100.10` |
| Servidor de backups | `192.168.100.20` |

Esta separación permite que el servidor principal tenga acceso a Internet cuando sea necesario, pero que la comunicación con el servidor de backups se realice mediante una red interna aislada.

---

## Acceso local a la aplicación

La aplicación de ticketing funciona como una **intranet local** dentro del entorno virtualizado de VirtualBox. Su acceso se realiza desde la propia máquina virtual o desde equipos que tengan conectividad con la red interna configurada para el laboratorio.

El servidor principal puede acceder a Internet mediante el adaptador NAT, lo que permite instalar paquetes, actualizar el sistema y utilizar el relay SMTP de Gmail para enviar correos electrónicos reales al exterior.

Sin embargo, el uso del relay SMTP de Gmail solo permite la salida de correos autenticados desde el servidor. Esta configuración no publica la aplicación web en Internet ni hace que la página sea accesible desde redes externas. Si un correo contiene un enlace hacia una IP interna, ese enlace solo funcionará desde dispositivos que puedan acceder a la misma red local o virtual.

Por tanto, en el estado actual del proyecto, la aplicación se considera una intranet local de pruebas desplegada en VirtualBox. La publicación externa de la web, mediante una configuración de red adecuada, dominio, DNS, HTTPS o despliegue en un servidor/cloud, queda planteada como una posible mejora futura.

---

## Servicios implementados

El servidor principal integra varios servicios necesarios para el funcionamiento de la intranet:

| Servicio | Tecnología | Función |
|---|---|---|
| Servidor web | Apache2 | Alojar la aplicación de ticketing |
| Backend | PHP | Procesar formularios, sesiones y lógica de la aplicación |
| Base de datos | MySQL/MariaDB | Almacenar usuarios, tickets, técnicos y administradores |
| Gestión de BD | phpMyAdmin | Administrar la base de datos desde navegador |
| Correo SMTP | Postfix | Envío de correos internos y notificaciones |
| Correo IMAP | Dovecot | Acceso a buzones de correo |
| Webmail | Roundcube | Cliente web para consultar y enviar correos |
| Administración | Webmin | Gestión del servidor desde navegador |
| Acceso remoto | SSH / WinSCP / AnyDesk | Administración y transferencia de archivos |
| Backups | mysqldump, tar.gz, scp y cron | Copias automatizadas al servidor secundario |
---

## Aplicación de ticketing

La parte principal del proyecto es una aplicación web desarrollada en **PHP** y conectada a una base de datos **MySQL/MariaDB**.

La aplicación permite gestionar incidencias dentro de una empresa de forma centralizada.

### Funcionalidades principales

- Registro de clientes.
- Inicio de sesión mediante correo y contraseña.
- Contraseñas cifradas en la base de datos.
- Creación de tickets por parte de los usuarios.
- Consulta del estado de los tickets.
- Panel de administración protegido mediante sesión.
- Edición de tickets desde administración.
- Cambio de estado del ticket:
  - Abierto
  - En proceso
  - Cerrado
- Asignación de técnicos.
- Cálculo automático de prioridad.
- Solicitud de actualización por parte del cliente.
- Recuperación de contraseña mediante correo electrónico.
- Envío de notificaciones automáticas por email.

---

## Funcionamiento del sistema de tickets

El usuario puede registrarse en la aplicación introduciendo datos como:

- Nombre.
- Apellidos.
- Correo electrónico.
- Empresa.
- Contraseña.

Posteriormente puede iniciar sesión y crear un ticket indicando el problema detectado.

El sistema solicita información adicional para calcular la prioridad de la incidencia, como:

- Urgencia del problema.
- Sistema afectado.
- Número o tipo de usuarios afectados.
- Nivel de bloqueo provocado por la incidencia.
- Posible solución temporal.

Con estos datos, la aplicación clasifica automáticamente el ticket con prioridad **baja**, **media** o **alta**.

Desde el panel de administración, el administrador puede:

- Revisar tickets.
- Cambiar el estado de la incidencia.
- Asignar técnicos.
- Editar información del ticket.
- Consultar datos de contacto.
- Registrar notas de modificación.
- Gestionar el seguimiento de las incidencias.

---

## Estructura básica de la aplicación

La aplicación se encuentra ubicada en el servidor dentro de la ruta:

```bash
/var/www/html/ticketing
```

Algunos de los archivos principales de la aplicación son:

| Archivo | Función |
|---|---|
| `index.php` | Página principal e inicio de sesión |
| `crear.php` | Creación de nuevos tickets |
| `ver.php` | Visualización de tickets por parte del cliente |
| `admin_ver.php` | Panel de administración de tickets |
| `db.php` | Conexión con la base de datos |
| `tecnicos.php` | Gestión o listado de técnicos |
| `nav_admin.php` | Barra de navegación del administrador |
| `nav_public.php` | Barra de navegación pública o de cliente |
| `logout.php` | Cierre de sesión del administrador |
| `logout_cliente.php` | Cierre de sesión del cliente |
| `generar_hash.php` | Generación de contraseñas cifradas |
| `style.css` | Estilos visuales de la aplicación |

La aplicación se puede acceder desde el navegador mediante:

```text
http://IP_DEL_SERVIDOR/ticketing
```
En el entorno final del proyecto, usando la red interna, el acceso sería similar a:

```text
http://192.168.100.10/ticketing
```

---

## Base de datos

La base de datos utilizada para la aplicación se gestiona mediante **MySQL/MariaDB**.

La base de datos almacena la información de clientes, tickets, administradores y técnicos.

### Tablas principales

| Tabla | Función |
|---|---|
| `clientes` | Almacena los usuarios que pueden crear tickets |
| `tickets` | Almacena las incidencias creadas |
| `admins` | Almacena los usuarios administradores |
| `tecnicos` | Almacena los técnicos disponibles para asignar tickets |

### Tabla `clientes`

La tabla `clientes` almacena los datos de los usuarios registrados en la aplicación.

Campos importantes:

- `id`
- `nombre`
- `apellidos`
- `email`
- `empresa`
- `password`
- `reset_token`
- `reset_expira`

El campo `password` almacena la contraseña cifrada, mientras que `reset_token` y `reset_expira` se utilizan para el proceso de recuperación de contraseña.

### Tabla `tickets`

La tabla `tickets` es la tabla principal de la aplicación.

Campos importantes:

- `id`
- `titulo`
- `descripcion`
- `estado`
- `fecha`
- `prioridad`
- `sistema_afectado`
- `usuarios_afectados`
- `nivel_bloqueo`
- `solucion_temporal`
- `fecha_cierre`
- `actualizado_en`
- `ultimo_editor_admin`
- `nota_edicion_admin`
- `solicitud_actualizacion`
- `fecha_solicitud_actualizacion`
- `tecnico_asignado`
- `cliente_id`
- `empresa`
- `persona_contacto`
- `telefono_contacto`

Esta tabla permite controlar todo el ciclo de vida de una incidencia, desde su creación hasta su cierre.

### Tabla `admins`

La tabla `admins` almacena los usuarios administradores del sistema.

Campos principales:

- `id`
- `usuario`
- `password`

El campo `password` almacena la contraseña cifrada del administrador.

### Tabla `tecnicos`

La tabla `tecnicos` almacena los técnicos disponibles para asignar incidencias.

Campos principales:

- `id`
- `nombre`
- `activo`

Esto permite controlar qué técnicos pueden ser asignados a los tickets.

---

## Acceso a phpMyAdmin

Para administrar la base de datos de forma visual se utilizó **phpMyAdmin**.

phpMyAdmin permite:

- Crear bases de datos.
- Crear y modificar tablas.
- Insertar registros.
- Ejecutar consultas SQL.
- Revisar relaciones entre tablas.
- Comprobar usuarios y permisos.
- Hacer pruebas durante el desarrollo de la aplicación.

El acceso se realiza desde el navegador mediante:

```text
http://IP_DEL_SERVIDOR/phpmyadmin
```

Ejemplo en el entorno del proyecto:

```text
http://192.168.100.10/phpmyadmin
```

Durante el desarrollo se utilizó phpMyAdmin para revisar la estructura de la base de datos, comprobar los registros creados por la aplicación y aplicar cambios en las tablas conforme se añadían nuevas funcionalidades.

Como mejora de seguridad, se creó un usuario específico para la aplicación en lugar de utilizar directamente el usuario `root` de la base de datos.

---

## Webmin

Se instaló **Webmin** como herramienta de administración web del servidor.

Webmin permite administrar diferentes aspectos del sistema desde el navegador, como:

- Usuarios del sistema.
- Grupos.
- Servicios.
- Configuración del sistema.
- Información del servidor.
- Gestión básica de permisos.
- Revisión del estado de la máquina.

El acceso a Webmin se realiza mediante el puerto `10000`:

```text
https://IP_DEL_SERVIDOR:10000
```

Ejemplo:

```text
https://192.168.100.10:10000
```

En el proyecto, Webmin se utilizó especialmente para completar la gestión de usuarios, ya que inicialmente algunos usuarios creados por terminal no tenían correctamente generado su directorio personal o shell de inicio.

Desde Webmin se pudieron revisar y modificar datos como:

- Directorio personal del usuario.
- Shell utilizado.
- Contraseña.
- Grupos secundarios.
- Caducidad de contraseña.

---

## Sistema de correo interno

Para el sistema de correo se instalaron y configuraron los siguientes servicios:

| Servicio | Función |
|---|---|
| Postfix | Servidor SMTP para envío de correos |
| Dovecot | Servicio IMAP para acceso a buzones |
| Roundcube | Cliente webmail para consultar y enviar correos desde navegador |

Este sistema permite disponer de correo interno dentro del entorno de la intranet.

Postfix se encarga del envío de correos, Dovecot permite acceder a los buzones mediante IMAP y Roundcube actúa como interfaz webmail para que los usuarios puedan consultar y enviar mensajes desde el navegador.

---

## Acceso a Roundcube Webmail

Roundcube se utiliza como cliente webmail, permitiendo acceder al correo desde un navegador.

El acceso se realiza mediante:

```text
http://IP_DEL_SERVIDOR/roundcube
```

Ejemplo:

```text
http://192.168.100.10/roundcube
```

Desde Roundcube se pueden realizar acciones como:

- Iniciar sesión con un usuario del sistema.
- Consultar la bandeja de entrada.
- Enviar correos.
- Revisar correos enviados.
- Comprobar el funcionamiento de Postfix y Dovecot.

Durante las pruebas se crearon usuarios como `cliente1` y `soporte` para comprobar el envío y recepción de correos dentro de la intranet.

---

## Relay SMTP con Gmail

Durante el desarrollo del proyecto se comprobó que enviar correos directamente desde el servidor hacia cuentas externas podía generar problemas, ya que muchos proveedores rechazan correos enviados desde IPs no autorizadas o sin una configuración DNS adecuada.

Para solucionar este problema, se configuró **Postfix como relay SMTP utilizando Gmail**.

Esto permite que los correos generados por el servidor salgan autenticados a través de una cuenta de Gmail.

### Motivo del relay SMTP

El relay SMTP se utilizó para permitir que la aplicación de ticketing pudiera enviar correos reales al exterior, como:

- Notificaciones al crear tickets.
- Correos de recuperación de contraseña.
- Avisos relacionados con incidencias.

En lugar de enviar directamente desde la IP del servidor, Postfix reenvía los mensajes a través del servidor SMTP de Gmail.

### Configuración general del relay con Gmail

Para usar Gmail como relay SMTP se utilizó una cuenta de Gmail dedicada al proyecto y una **contraseña de aplicación**.

El relay se configuró en Postfix indicando el servidor SMTP de Gmail:

```text
smtp.gmail.com:587
```

La autenticación se configuró mediante el archivo:

```bash
/etc/postfix/sasl_passwd
```

Con una estructura similar a:

```bash
[smtp.gmail.com]:587 correo_gmail@gmail.com:CONTRASEÑA_DE_APLICACION
```

En este caso se utilizó:
```bash
[smtp.gmail.com]:587 eugeenproject@gmail.com:CONTRASEÑA_DE_APLICACION
```

Después se generó el archivo de base de datos utilizado por Postfix:

```bash
sudo postmap /etc/postfix/sasl_passwd
```

Para proteger las credenciales se ajustaron los permisos:

```bash
sudo chmod 600 /etc/postfix/sasl_passwd
sudo chmod 600 /etc/postfix/sasl_passwd.db
```

También se añadieron parámetros en la configuración de Postfix para habilitar autenticación SASL y cifrado TLS.

Finalmente, se reinició el servicio:

```bash
sudo systemctl restart postfix
```

Con esta configuración, los correos generados por la aplicación pueden salir al exterior usando Gmail como servidor intermediario.


---

## Notificaciones automáticas

La aplicación de ticketing está integrada con el sistema de correo para enviar notificaciones automáticas.

Se utilizan correos en situaciones como:

- Creación de un nuevo ticket.
- Confirmación al usuario.
- Aviso a administración.
- Recuperación de contraseña.

Esto permite que el sistema no sea únicamente una aplicación web local, sino una herramienta más completa de comunicación interna y soporte.

---

## Gestión de usuarios y permisos

Dentro del sistema Linux se crearon usuarios y grupos para simular una estructura empresarial.

Se utilizaron grupos como:

- `secretaria`
- `ventas`
- `programadores`

También se crearon directorios específicos dentro de `/srv`, asignando permisos para que solo los usuarios pertenecientes al grupo correspondiente pudieran acceder.

Ejemplo de estructura:

```bash
/srv/secretaria
/srv/ventas
```

Ejemplo de permisos aplicados:

```bash
sudo chown root:secretaria /srv/secretaria
sudo chmod 770 /srv/secretaria
```

De esta manera se aplican permisos por grupo, evitando que usuarios no autorizados puedan acceder a información de otros departamentos.

---

## Acceso remoto con SSH y WinSCP

Se configuró el servicio **SSH** para permitir la administración remota del servidor.

SSH permite conectarse al servidor desde otro equipo mediante terminal:

```bash
ssh usuario@IP_DEL_SERVIDOR
```

Ejemplo:

```bash
ssh erc01@192.168.100.10
```

Además, se utilizó **WinSCP** para transferir archivos desde Windows al servidor Linux mediante SFTP.

WinSCP resultó útil para:

- Copiar archivos PHP al servidor.
- Editar archivos de la aplicación.
- Revisar directorios.
- Gestionar archivos de forma visual.
- Subir cambios a `/var/www/html/ticketing`.

Para acceder con WinSCP se utilizaron datos similares a:

```text
Protocolo: SFTP
Servidor: 192.168.100.10
Puerto: 22
Usuario: erc01
Contraseña: ********
```

---

## Acceso remoto con AnyDesk

Además de SSH y WinSCP, se instaló **AnyDesk** para permitir acceso gráfico remoto al servidor.

AnyDesk permite controlar el escritorio del servidor como si se estuviera físicamente delante de la máquina.

Se utilizó principalmente porque el servidor tenía instalado Xubuntu Desktop con fines de comodidad durante el desarrollo y la realización de pruebas.

AnyDesk permite:

- Administrar el servidor de forma visual.
- Acceder desde otro equipo.
- Supervisar el estado del sistema.
- Realizar pruebas sin estar físicamente delante de la máquina.

En un entorno profesional real, lo más recomendable sería depender principalmente de SSH y herramientas de administración seguras, dejando el acceso gráfico solo para casos concretos.

---

## Servidor de backups

Para mejorar la disponibilidad del sistema, se creó una segunda máquina virtual Ubuntu Server dedicada exclusivamente a almacenar copias de seguridad.

El objetivo de esta máquina es separar las copias del servidor principal, evitando que una pérdida o fallo en el servidor principal afecte también a los backups.

### Comunicación entre servidores

El servidor principal y el servidor de backups se comunican mediante una red interna de VirtualBox.

IPs utilizadas:

```text
Servidor principal: 192.168.100.10
Servidor de backups: 192.168.100.20
```

Para permitir el envío automático de archivos, se configuró acceso SSH entre ambos servidores.

En la máquina principal se generó una clave SSH:

```bash
ssh-keygen
```

Después se copió la clave pública al servidor de backups:

```bash
ssh-copy-id erc01@192.168.100.20
```

De esta forma, el servidor principal puede enviar los backups al servidor secundario sin necesidad de introducir contraseña manualmente cada vez.

---

## Script de copias de seguridad

Se creó un script llamado:

```bash
backup.sh
```

Este script realiza varias acciones:

1. Genera una copia de la base de datos con `mysqldump`.
2. Comprime la carpeta de la aplicación web con `tar.gz`.
3. Envía los archivos al servidor de backups mediante `scp`.
4. Elimina los archivos temporales del servidor principal.

Ejemplo de comandos utilizados:

```bash
mysqldump -u backup -p'********' ticketing > ticketing_FECHA.sql

tar -czf web_FECHA.tar.gz /var/www/html/ticketing

scp ticketing_FECHA.sql erc01@192.168.100.20:/home/erc01/backups

scp web_FECHA.tar.gz erc01@192.168.100.20:/home/erc01/backups
```

Con este sistema se realiza una copia tanto de los datos almacenados en la base de datos como de los archivos necesarios para que la aplicación web pueda restaurarse en caso de fallo.

---

## Automatización con cron

Para automatizar el proceso de backups se utilizó `cron`.

Se editó el crontab del usuario correspondiente mediante:

```bash
crontab -e
```

Y se añadió una línea similar a:

```bash
0 2 * * * /home/erc01/backup.sh
```

Esta línea ejecuta el script `backup.sh` todos los días a las **02:00**.

Explicación:

| Campo | Valor | Significado |
|---|---|---|
| Minuto | `0` | Minuto 0 |
| Hora | `2` | 2 de la mañana |
| Día del mes | `*` | Todos los días |
| Mes | `*` | Todos los meses |
| Día de la semana | `*` | Todos los días de la semana |

Gracias a esta automatización, las copias de seguridad se generan y transfieren sin intervención manual.

---

## Seguridad aplicada

Durante el desarrollo del proyecto se aplicaron varias medidas básicas de seguridad:

- Uso de usuarios diferenciados en lugar de trabajar siempre con root.
- Contraseñas cifradas en la aplicación.
- Panel de administración protegido mediante sesiones.
- Permisos por grupos en directorios del sistema.
- Uso de SSH/SFTP para administración y transferencia de archivos.
- Separación del servidor principal y servidor de backups.
- Uso de contraseña de aplicación para el relay SMTP con Gmail.
- Automatización de backups para reducir riesgo de pérdida de datos.

---

## Problemas encontrados y soluciones

Durante el desarrollo del proyecto surgieron distintos problemas técnicos que fueron solucionándose conforme avanzaba la implementación.

### Problemas con Roundcube

Inicialmente, Roundcube presentó errores de acceso y configuración.

Algunos problemas detectados fueron:

- Error 404 al acceder al webmail.
- Problemas con el archivo de configuración.
- Fallos de conexión con la base de datos.
- Errores SMTP al enviar correos.

Estos problemas se solucionaron revisando la configuración de Roundcube, creando el enlace correspondiente en Apache y ajustando los parámetros necesarios para que Roundcube pudiera comunicarse correctamente con Postfix y Dovecot.

### Problemas con SMTP

Durante las pruebas de envío de correo aparecieron errores relacionados con SMTP, como fallos de conexión o autenticación.

Para solucionarlo se revisó la configuración de Postfix, los servicios activos y los parámetros de autenticación.

Finalmente, para el envío de correos reales al exterior, se configuró Gmail como relay SMTP mediante contraseña de aplicación.

### Problemas con Webmin

Durante la instalación de Webmin surgió un problema relacionado con el repositorio y la clave GPG.

El sistema bloqueaba la instalación por motivos de seguridad debido a que la clave o el repositorio no estaban correctamente configurados.

La solución fue eliminar la configuración antigua del repositorio, añadir el repositorio actualizado y configurar correctamente la clave GPG válida.

### Problemas de permisos con WinSCP

Al copiar archivos de la aplicación mediante WinSCP, surgieron problemas de permisos al intentar escribir directamente en `/var/www/html/ticketing`.

Este problema se debe a que esa ruta pertenece a usuarios del sistema como `root` o `www-data`.

Para solucionarlo, se ajustaron permisos o se movieron los archivos con privilegios adecuados desde terminal.

### Problemas de red entre máquinas virtuales

Al implementar el servidor de backups fue necesario ajustar la red de VirtualBox.

La solución final fue utilizar dos adaptadores:

- NAT para salida a Internet.
- Red interna para comunicación entre servidor principal y servidor de backups.

También se asignaron IPs estáticas para asegurar una comunicación estable entre ambas máquinas.



---

## Conclusión

El proyecto permite simular una infraestructura empresarial real basada en Linux, integrando servicios web, base de datos, correo, administración remota, sistema de tickets y copias de seguridad.

La aplicación de ticketing aporta una funcionalidad práctica al sistema, ya que permite gestionar incidencias de forma centralizada y enviar notificaciones automáticas.

Además, la implementación de un servidor de backups independiente mejora la disponibilidad y reduce el riesgo de pérdida de datos.
