/* Datos demo para el proyecto Ticketing. No contiene datos reales ni hashes de la VM. */

LOCK TABLES `admins` WRITE;
INSERT INTO admins (id, usuario, password) VALUES
(1, 'admin_demo', '$2y$10$HASH_DEMO_CAMBIAR');
UNLOCK TABLES;

LOCK TABLES `clientes` WRITE;
INSERT INTO clientes (id, nombre, apellidos, email, password, empresa, reset_token, reset_expira) VALUES
(1, 'Cliente', 'Demo Uno', 'cliente1@example.local', '$2y$10$HASH_DEMO_CAMBIAR', 'Empresa Demo Norte', NULL, NULL),
(2, 'Cliente', 'Demo Dos', 'cliente2@example.local', '$2y$10$HASH_DEMO_CAMBIAR', 'Empresa Demo Sur', NULL, NULL),
(3, 'Cliente', 'Demo Tres', 'cliente3@example.local', '$2y$10$HASH_DEMO_CAMBIAR', 'Empresa Demo Este', NULL, NULL);
UNLOCK TABLES;

LOCK TABLES `tecnicos` WRITE;
INSERT INTO tecnicos (id, nombre, usuario, email, password, debe_cambiar_password, activo) VALUES
(1, 'Tecnico Demo 1', 'tecnico1', 'tecnico1@example.local', '$2y$10$HASH_DEMO_CAMBIAR', 1, 1),
(2, 'Tecnico Demo 2', 'tecnico2', 'tecnico2@example.local', '$2y$10$HASH_DEMO_CAMBIAR', 1, 1),
(3, 'Tecnico Demo 3', 'tecnico3', 'tecnico3@example.local', '$2y$10$HASH_DEMO_CAMBIAR', 1, 1);
UNLOCK TABLES;

LOCK TABLES `tickets` WRITE;
INSERT INTO `tickets` VALUES
(1,'Error de acceso al portal','Un usuario demo no puede iniciar sesion en la aplicacion','abierto','2026-04-03 08:37:04','media','Aplicacion web','Un usuario','Si, pero con limitaciones','Si',NULL,'2026-04-03 08:37:04',NULL,NULL,NULL,NULL,'Sin asignar',1,'Empresa Demo Norte','Persona Demo A','600000001'),
(2,'Servidor lento','La intranet responde lentamente durante las pruebas','en_progreso','2026-04-16 16:31:24','alta','Servidor completo','A toda la empresa','Si, pero con limitaciones','No',NULL,'2026-04-16 16:31:24','admin_demo','Revision inicial realizada','Solicitamos actualizacion del estado','2026-04-19 12:38:17','Tecnico Demo 1',2,'Empresa Demo Sur','Persona Demo B','600000002'),
(3,'Consulta sobre copia de seguridad','Se solicita comprobar la restauracion de un backup de prueba','cerrado','2026-04-30 18:47:35','baja','Backups','A mi departamento','Si, sin problema','Si','2026-04-30 19:10:00','2026-04-30 18:49:34','admin_demo','Ticket resuelto en entorno demo',NULL,NULL,'Tecnico Demo 2',3,'Empresa Demo Este','Persona Demo C','600000003');
UNLOCK TABLES;
