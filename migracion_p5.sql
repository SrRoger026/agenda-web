
CREATE TABLE IF NOT EXISTS eventos_respaldo AS SELECT * FROM eventos;

-- Paso 1: Crear la tabla de categorías e insertar las bases
CREATE TABLE IF NOT EXISTS categorias (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (id, nombre) VALUES
  (1, 'Trabajo'),
  (2, 'Personal'),
  (3, 'Estudio'),
  (4, 'Ocio / Deporte');

ALTER TABLE eventos ADD COLUMN IF NOT EXISTS categoria_id INT NULL AFTER categoria;

UPDATE eventos
   SET categoria_id = CASE LOWER(categoria)
         WHEN 'trabajo'  THEN 1
         WHEN 'personal' THEN 2
         WHEN 'proyecto' THEN 3
         WHEN 'estudio'  THEN 3
         WHEN 'academico' THEN 3
         WHEN 'ocio'     THEN 4
         ELSE 2 
       END
 WHERE id > 0;

ALTER TABLE eventos
  MODIFY categoria_id INT NOT NULL,
  ADD CONSTRAINT fk_eventos_categoria
      FOREIGN KEY (categoria_id) REFERENCES categorias(id)
      ON DELETE RESTRICT;

ALTER TABLE eventos DROP COLUMN categoria;