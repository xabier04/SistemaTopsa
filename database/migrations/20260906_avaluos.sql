-- Migración aditiva para la base local y nuevas instalaciones. No modifica datos existentes.
CREATE TABLE IF NOT EXISTS valuos (
  id_valuo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_proyecto INT NOT NULL,
  fecha_del_valuo DATE NOT NULL,
  monto_estimado DECIMAL(14,2) NOT NULL DEFAULT 0,
  porcentaje_de_avance DECIMAL(5,2) NOT NULL DEFAULT 0,
  observaciones TEXT NULL,
  KEY idx_valuo_proyecto (id_proyecto),
  CONSTRAINT fk_valuo_proyecto FOREIGN KEY (id_proyecto) REFERENCES proyectos (id_proyecto) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS valuo_expedientes (
  id_valuo INT NOT NULL PRIMARY KEY,
  referencia VARCHAR(100) NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'Borrador',
  version_calculo VARCHAR(40) NOT NULL,
  datos JSON NOT NULL,
  resultados JSON NOT NULL,
  revision INT NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_expediente_valuo FOREIGN KEY (id_valuo) REFERENCES valuos (id_valuo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS valuo_anexos (
  id_anexo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_valuo INT NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  archivo VARCHAR(80) NOT NULL,
  tipo VARCHAR(80) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_anexo_valuo FOREIGN KEY (id_valuo) REFERENCES valuos (id_valuo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
