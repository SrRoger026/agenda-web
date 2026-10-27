USE agenda;

-- Paso 1: Respaldo de seguridad de eventos existentes
CREATE TABLE eventos_respaldo_p8 AS SELECT * FROM eventos;

-- Paso 2: Creación de la tabla de usuarios
CREATE TABLE usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(80)  NOT NULL,
  correo     VARCHAR(120) NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  creado_en  DATETIME     DEFAULT CURRENT_TIMESTAMP
);

-- Paso 3: Asignar eventos a un usuario y configurar la llave foránea
-- NOTA: Primero te registrarás para obtener tu id de usuario (usualmente 1)
ALTER TABLE eventos ADD COLUMN usuario_id INT NULL AFTER id;

UPDATE eventos SET usuario_id = 1 WHERE id > 0;

ALTER TABLE eventos
  MODIFY usuario_id INT NOT NULL,
  ADD CONSTRAINT fk_eventos_usuario
      FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
      ON DELETE CASCADE;