-- Actualizacion para obligar a los tecnicos a cambiar la contrasena
-- en el primer inicio de sesion.
-- Ejecutar en la base de datos ticketing desde phpMyAdmin o consola MySQL.

ALTER TABLE tecnicos
ADD COLUMN debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 1 AFTER password;

-- Los tecnicos ya existentes tendran que cambiar la contrasena al iniciar sesion.
UPDATE tecnicos
SET debe_cambiar_password = 1;
