-- ============================================================
-- Sistema TOPSA — Esquema de Base de Datos
-- Oficina de Ingeniería Civil "TOPSA"
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── Crear base de datos ─────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `topsa_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `topsa_db`;

-- ─── Tabla: empleados ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `empleados` (
  `id_empleado` INT NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(60) NOT NULL,
  `dui` VARCHAR(10) NOT NULL,
  `cargo` VARCHAR(90) NOT NULL,
  `telefono` VARCHAR(9) DEFAULT NULL,
  `estado` VARCHAR(20) NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `uk_empleados_dui` (`dui`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: usuarios ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `id_empleado` INT NOT NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `correo` VARCHAR(50) NOT NULL,
  `contrasena` VARCHAR(255) NOT NULL,
  `rol` VARCHAR(30) NOT NULL DEFAULT 'Empleado',
  `estado_de_cuenta` VARCHAR(20) NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuarios_correo` (`correo`),
  KEY `fk_usuarios_empleado` (`id_empleado`),
  CONSTRAINT `fk_usuarios_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: clientes ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(90) NOT NULL,
  `dui` VARCHAR(10) NOT NULL,
  `telefono` VARCHAR(9) DEFAULT NULL,
  `direccion` TEXT DEFAULT NULL,
  `correo_electronico` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `uk_clientes_dui` (`dui`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: inmuebles ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `inmuebles` (
  `id_inmueble` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT NOT NULL,
  `matricula` VARCHAR(30) NOT NULL,
  `direccion` TEXT NOT NULL,
  `area` DECIMAL(10,2) DEFAULT NULL,
  `tipo_inmueble` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`id_inmueble`),
  UNIQUE KEY `uk_inmuebles_matricula` (`matricula`),
  KEY `fk_inmuebles_cliente` (`id_cliente`),
  CONSTRAINT `fk_inmuebles_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: proyectos ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id_proyecto` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT NOT NULL,
  `id_inmueble` INT DEFAULT NULL,
  `nombre_del_proyecto` VARCHAR(60) NOT NULL,
  `fecha_de_inicio` DATE NOT NULL,
  `estado_del_proyecto` VARCHAR(30) NOT NULL DEFAULT 'En Proceso',
  `presupuesto_inicial` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_proyecto`),
  KEY `fk_proyectos_cliente` (`id_cliente`),
  KEY `fk_proyectos_inmueble` (`id_inmueble`),
  CONSTRAINT `fk_proyectos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_proyectos_inmueble` FOREIGN KEY (`id_inmueble`) REFERENCES `inmuebles` (`id_inmueble`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: proyecto_empleado (relación N:M) ─────────────────
CREATE TABLE IF NOT EXISTS `proyecto_empleado` (
  `id_empleado` INT NOT NULL,
  `id_proyecto` INT NOT NULL,
  `fecha_asignacion` DATE NOT NULL,
  PRIMARY KEY (`id_empleado`, `id_proyecto`),
  KEY `fk_pe_proyecto` (`id_proyecto`),
  CONSTRAINT `fk_pe_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pe_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: transacciones ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `transacciones` (
  `id_transaccion` INT NOT NULL AUTO_INCREMENT,
  `id_proyecto` INT NOT NULL,
  `fecha_de_pago` DATE NOT NULL,
  `monto_abonado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tipo_de_transaccion` VARCHAR(30) NOT NULL,
  `saldo_pendiente` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_transaccion`),
  KEY `fk_transacciones_proyecto` (`id_proyecto`),
  CONSTRAINT `fk_transacciones_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: valuos (avalúos) ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `valuos` (
  `id_valuo` INT NOT NULL AUTO_INCREMENT,
  `id_proyecto` INT NOT NULL,
  `fecha_del_valuo` DATE NOT NULL,
  `monto_estimado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `porcentaje_de_avance` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `observaciones` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_valuo`),
  KEY `fk_valuos_proyecto` (`id_proyecto`),
  CONSTRAINT `fk_valuos_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: documentos ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `documentos` (
  `id_documento` INT NOT NULL AUTO_INCREMENT,
  `id_proyecto` INT NOT NULL,
  `nombre_del_archivo` VARCHAR(60) NOT NULL,
  `tipo_de_documento` VARCHAR(30) NOT NULL,
  `fecha_de_subida` DATE NOT NULL,
  `ruta_del_archivo` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_documento`),
  KEY `fk_documentos_proyecto` (`id_proyecto`),
  CONSTRAINT `fk_documentos_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: asistencias ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `asistencias` (
  `id_asistencia` INT NOT NULL AUTO_INCREMENT,
  `id_empleado` INT NOT NULL,
  `fecha_de_marcaje` DATE NOT NULL,
  `hora_de_entrada` TIME DEFAULT NULL,
  `hora_de_salida` TIME DEFAULT NULL,
  `horas_trabajadas` DECIMAL(4,2) DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  KEY `fk_asistencias_empleado` (`id_empleado`),
  CONSTRAINT `fk_asistencias_empleado` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tabla: bitacora (actividad del sistema) ─────────────────
CREATE TABLE IF NOT EXISTS `bitacora` (
  `id_bitacora` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT DEFAULT NULL,
  `accion` VARCHAR(100) NOT NULL,
  `tabla_afectada` VARCHAR(50) DEFAULT NULL,
  `id_registro` INT DEFAULT NULL,
  `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (`id_bitacora`),
  KEY `fk_bitacora_usuario` (`id_usuario`),
  KEY `idx_bitacora_fecha` (`fecha_hora`),
  CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Datos iniciales ─────────────────────────────────────────

-- Empleado administrador
INSERT INTO `empleados` (`nombre_completo`, `dui`, `cargo`, `telefono`, `estado`)
VALUES ('Administrador del Sistema', '00000000-0', 'Administrador General', '0000-0000', 'Activo');

-- Usuario administrador (contraseña: Admin123!)
INSERT INTO `usuarios` (`id_empleado`, `nombre`, `correo`, `contrasena`, `rol`, `estado_de_cuenta`)
VALUES (
  1,
  'Administrador',
  'admin@topsa.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Administrador',
  'Activo'
);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Usuario de prueba:
--   Correo:     admin@topsa.com
--   Contraseña: Admin123!
-- ============================================================
