-- Actualizacion para permitir acceso propio a tecnicos.
-- Ejecutar en la base de datos ticketing desde phpMyAdmin o consola MySQL.

ALTER TABLE tecnicos
ADD COLUMN usuario VARCHAR(50) NULL AFTER nombre,
ADD COLUMN email VARCHAR(150) NULL AFTER usuario,
ADD COLUMN password VARCHAR(255) NULL AFTER email,
ADD COLUMN debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 1 AFTER password;

-- Usuarios iniciales de ejemplo para tecnicos.
-- Sustituir HASH_DEMO_CAMBIAR por hashes generados en tu entorno antes de ejecutar.
-- Despues, al iniciar sesion por primera vez, cada tecnico tendra que cambiarla obligatoriamente desde la app.

UPDATE tecnicos SET usuario = 'tecnico1', email = 'tecnico1@example.local', password = '$2y$10$HASH_DEMO_CAMBIAR' WHERE nombre = 'Tecnico Demo 1';
UPDATE tecnicos SET usuario = 'tecnico2', email = 'tecnico2@example.local', password = '$2y$10$HASH_DEMO_CAMBIAR' WHERE nombre = 'Tecnico Demo 2';
UPDATE tecnicos SET usuario = 'tecnico3', email = 'tecnico3@example.local', password = '$2y$10$HASH_DEMO_CAMBIAR' WHERE nombre = 'Tecnico Demo 3';

ALTER TABLE tecnicos
MODIFY usuario VARCHAR(50) NOT NULL,
MODIFY password VARCHAR(255) NOT NULL,
ADD UNIQUE KEY uq_tecnicos_usuario (usuario);
