CREATE TABLE IF NOT EXISTS transacciones (
 id_transaccion INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 id_proyecto INT NOT NULL,
 fecha_de_pago DATE NOT NULL,
 monto_abonado DECIMAL(10,2) NOT NULL,
 tipo_de_transaccion VARCHAR(30) NOT NULL,
 saldo_pendiente DECIMAL(10,2) NOT NULL,
 referencia VARCHAR(64) NOT NULL UNIQUE,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tareas (
 id_tarea INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 id_proyecto INT NOT NULL,
 id_empleado INT NOT NULL,
 titulo VARCHAR(150) NOT NULL,
 descripcion TEXT NULL,
 fecha_limite DATE NOT NULL,
 avance TINYINT UNSIGNED NOT NULL DEFAULT 0,
 observaciones TEXT NULL,
 actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (id_empleado, id_proyecto) REFERENCES proyecto_empleado(id_empleado, id_proyecto) ON UPDATE CASCADE,
 CHECK (avance <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recuperaciones (
 id_usuario INT NOT NULL PRIMARY KEY,
 solicitada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
