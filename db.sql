-- db.sql · Esquema completo de la base de datos AgendaWeb
CREATE DATABASE IF NOT EXISTS agendaweb_php_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agendaweb_php_db;

CREATE TABLE IF NOT EXISTS categorias (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (id, nombre) VALUES
  (1, 'Trabajo'),
  (2, 'Personal'),
  (3, 'Estudio'),
  (4, 'Ocio / Deporte');

CREATE TABLE IF NOT EXISTS eventos (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  titulo       VARCHAR(120) NOT NULL,
  fecha        DATE NOT NULL,
  hora         TIME NULL,
  categoria_id INT NOT NULL,
  descripcion  VARCHAR(500) NULL,
  creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_eventos_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;