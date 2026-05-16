
-- =====================================================
-- ARCHIVO: schema.sql
-- =====================================================

-- =====================================================
-- SIGFA - Sistema de Gestión Farmacéutica
-- Hospital Dr. Juan Daza Pereira
-- =====================================================
-- Archivo: database/schema.sql
-- Descripción: Esquema unificado y optimizado (Versión Final Corregida)
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS sigfa_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sigfa_db;

-- 1. TABLA: usuarios
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula          VARCHAR(12)  NOT NULL UNIQUE,
    nombre          VARCHAR(80)  NOT NULL,
    apellido        VARCHAR(80)  NOT NULL,
    correo          VARCHAR(150) NOT NULL UNIQUE,
    telefono        VARCHAR(20)  DEFAULT NULL,
    clave           VARCHAR(255) NOT NULL,
    rol             ENUM('Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Almacenista', 'Farmaceutico', 'Kardista') NOT NULL DEFAULT 'Auxiliar_General',
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    ultimo_acceso   DATETIME     DEFAULT NULL,
    creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: asegurados
DROP TABLE IF EXISTS asegurados;
CREATE TABLE asegurados (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula                  VARCHAR(12)  UNIQUE,
    nombre                  VARCHAR(80)  NOT NULL,
    apellido                VARCHAR(80)  NOT NULL,
    fecha_nacimiento        DATE         NOT NULL,
    sexo                    ENUM('M', 'F') NOT NULL,
    grupo_sanguineo         ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') DEFAULT NULL,
    historia_medica         VARCHAR(30)  UNIQUE,
    partida_nacimiento      VARCHAR(50)  UNIQUE,
    estado                  VARCHAR(50)  DEFAULT NULL,
    municipio               VARCHAR(100) DEFAULT NULL,
    parroquia               VARCHAR(100) DEFAULT NULL,
    direccion               TEXT         DEFAULT NULL,
    telefono                VARCHAR(20)  DEFAULT NULL,
    telefono_familiar       VARCHAR(20)  DEFAULT NULL,
    correo                  VARCHAR(150) DEFAULT NULL,
    tipo_asegurado          ENUM('Titular', 'Beneficiario', 'Paciente') NOT NULL DEFAULT 'Titular',
    estatus                 ENUM('Activo', 'Inactivo', 'Anulado', 'Fallecido') NOT NULL DEFAULT 'Activo',
    ciclo_tratamiento       TINYINT(1)   NOT NULL DEFAULT 21,
    datos_actualizados      TINYINT(1)   NOT NULL DEFAULT 0,
    fecha_ultima_actualizacion DATETIME   DEFAULT NULL,
    creado_en               DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_asegurado_cedula (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: medicos
DROP TABLE IF EXISTS medicos;
CREATE TABLE medicos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula          VARCHAR(12)  NOT NULL UNIQUE,
    nombre          VARCHAR(80)  NOT NULL,
    apellido        VARCHAR(80)  NOT NULL,
    codigo_mpps     VARCHAR(30)  NOT NULL UNIQUE,
    especialidad    VARCHAR(100) DEFAULT NULL,
    telefono        VARCHAR(20)  DEFAULT NULL, -- Columna Faltante Corregida
    correo          VARCHAR(150) DEFAULT NULL, -- Columna Faltante Corregida
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: grupos_medicamentos
DROP TABLE IF EXISTS grupos_medicamentos;
CREATE TABLE grupos_medicamentos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(10)  NOT NULL UNIQUE,
    nombre          VARCHAR(150) NOT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA: medicamentos
DROP TABLE IF EXISTS medicamentos;
CREATE TABLE medicamentos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(30)  NOT NULL UNIQUE,
    nombre_generico VARCHAR(150) NOT NULL,
    nombre_comercial VARCHAR(150) DEFAULT NULL,
    concentracion   VARCHAR(50)  NOT NULL,
    tipo            ENUM('Tableta', 'Cápsula', 'Jarabe', 'Inyectable', 'Crema', 'Ungüento', 'Gotas', 'Supositorio', 'Solución', 'Suspensión', 'Otro') NOT NULL,
    presentacion    VARCHAR(100) NOT NULL,
    grupo_id        INT UNSIGNED DEFAULT NULL,
    tipo_medicamento ENUM('General', 'Alto_Costo') NOT NULL DEFAULT 'General',
    stock_minimo    INT UNSIGNED NOT NULL DEFAULT 10,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_med_grupo FOREIGN KEY (grupo_id) REFERENCES grupos_medicamentos(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABLA: proveedores
DROP TABLE IF EXISTS proveedores;
CREATE TABLE proveedores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rif             VARCHAR(20)  NOT NULL UNIQUE,
    razon_social    VARCHAR(150) NOT NULL,
    direccion       TEXT         DEFAULT NULL,
    telefono        VARCHAR(20)  DEFAULT NULL,
    correo          VARCHAR(150) DEFAULT NULL,
    contacto_nombre VARCHAR(100) DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABLA: almacenes
DROP TABLE IF EXISTS almacenes;
CREATE TABLE almacenes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(20)  NOT NULL UNIQUE,
    nombre          VARCHAR(100) NOT NULL,
    tipo            VARCHAR(50)  NOT NULL DEFAULT 'General',
    ubicacion       VARCHAR(200) DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABLA: servicios_medicos
DROP TABLE IF EXISTS servicios_medicos;
CREATE TABLE servicios_medicos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(20)  UNIQUE,
    nombre          VARCHAR(150) NOT NULL,
    descripcion     TEXT         DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABLA: patologias
DROP TABLE IF EXISTS patologias;
CREATE TABLE patologias (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(200) NOT NULL,
    clasificacion   VARCHAR(100) DEFAULT NULL, -- Columna Sincronizada con Patologia.php
    grupo_etario    VARCHAR(50)  DEFAULT NULL, -- Columna Sincronizada con Patologia.php
    descripcion     TEXT         DEFAULT NULL,
    codigo_cie10    VARCHAR(10)  DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABLA: lotes_inventario
DROP TABLE IF EXISTS lotes_inventario;
CREATE TABLE lotes_inventario (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medicamento_id      INT UNSIGNED NOT NULL,
    almacen_id          INT UNSIGNED NOT NULL,
    proveedor_id        INT UNSIGNED DEFAULT NULL,
    numero_lote         VARCHAR(50)  NOT NULL,
    fecha_fabricacion   DATE         DEFAULT NULL,
    fecha_vencimiento   DATE         NOT NULL,
    cantidad_recibida   INT UNSIGNED NOT NULL,
    cantidad_disponible INT UNSIGNED NOT NULL,
    precio_unitario     DECIMAL(10,2) DEFAULT 0.00,
    numero_guia         VARCHAR(50)  DEFAULT NULL,
    chofer_nombre       VARCHAR(100) DEFAULT NULL,
    chofer_cedula       VARCHAR(20)  DEFAULT NULL,
    chofer_telefono     VARCHAR(20)  DEFAULT NULL,
    chofer_correo       VARCHAR(150) DEFAULT NULL,
    placa_vehiculo      VARCHAR(20)  DEFAULT NULL,
    observaciones       TEXT         DEFAULT NULL,
    estatus             ENUM('Disponible', 'Agotado', 'Vencido', 'Anulado') DEFAULT 'Disponible',
    registrado_por      INT UNSIGNED DEFAULT NULL,
    fecha_recepcion     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    anulado_por         INT UNSIGNED DEFAULT NULL,
    fecha_anulacion     DATETIME     DEFAULT NULL,
    motivo_anulacion    TEXT         DEFAULT NULL,

    CONSTRAINT fk_lote_med FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_lote_alm FOREIGN KEY (almacen_id) REFERENCES almacenes(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_lote_prov FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_lote_regpor FOREIGN KEY (registrado_por) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE SET NULL,
    UNIQUE KEY uk_lote_medicamento (medicamento_id, numero_lote)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. TABLA: despachos
DROP TABLE IF EXISTS despachos;
CREATE TABLE despachos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                VARCHAR(36)  NOT NULL UNIQUE,
    ticket              VARCHAR(20)  NOT NULL UNIQUE,
    asegurado_id        INT UNSIGNED NOT NULL,
    medico_id           INT UNSIGNED DEFAULT NULL,
    servicio_id         INT UNSIGNED DEFAULT NULL,
    patologia_id        INT UNSIGNED DEFAULT NULL,
    diagnostico         TEXT         DEFAULT NULL,
    edad_paciente       INT UNSIGNED DEFAULT NULL,
    total_articulos     INT UNSIGNED NOT NULL DEFAULT 0,
    monto_total         DECIMAL(12,2) DEFAULT 0.00,
    estatus             ENUM('Pendiente', 'Despachado', 'Anulado') NOT NULL DEFAULT 'Despachado',
    observaciones       TEXT         DEFAULT NULL,
    despachado_por      INT UNSIGNED DEFAULT NULL,
    fecha_despacho      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    anulado_por         INT UNSIGNED DEFAULT NULL,
    fecha_anulacion     DATETIME     DEFAULT NULL,
    motivo_anulacion    TEXT         DEFAULT NULL,

    CONSTRAINT fk_desp_aseg FOREIGN KEY (asegurado_id) REFERENCES asegurados(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_desp_medico FOREIGN KEY (medico_id) REFERENCES medicos(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desp_serv FOREIGN KEY (servicio_id) REFERENCES servicios_medicos(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desp_pat FOREIGN KEY (patologia_id) REFERENCES patologias(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_desp_usu FOREIGN KEY (despachado_por) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. TABLA: despacho_detalle
DROP TABLE IF EXISTS despacho_detalle;
CREATE TABLE despacho_detalle (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    despacho_id         INT UNSIGNED NOT NULL,
    medicamento_id      INT UNSIGNED NOT NULL,
    lote_id             INT UNSIGNED DEFAULT NULL,
    cantidad            INT UNSIGNED NOT NULL,
    precio_unitario     DECIMAL(10,2) DEFAULT 0.00,

    CONSTRAINT fk_det_desp FOREIGN KEY (despacho_id) REFERENCES despachos(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_det_med FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_det_lote FOREIGN KEY (lote_id) REFERENCES lotes_inventario(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. TABLA: transferencias
DROP TABLE IF EXISTS transferencias;
CREATE TABLE transferencias (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_transaccion      VARCHAR(20)  NOT NULL UNIQUE,
    almacen_origen_id       INT UNSIGNED NOT NULL,
    almacen_destino_id      INT UNSIGNED DEFAULT NULL,
    servicio_destino_id    INT UNSIGNED DEFAULT NULL,
    motivo                  TEXT         NOT NULL,
    estatus                 ENUM('Pendiente', 'En_Transito', 'Completada', 'Anulada') NOT NULL DEFAULT 'Pendiente',
    observaciones           TEXT         DEFAULT NULL,
    registrado_por          INT UNSIGNED DEFAULT NULL,
    fecha_registro          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    recibido_por            VARCHAR(100) DEFAULT NULL,
    fecha_recepcion         DATETIME     DEFAULT NULL,
    anulado_por             INT UNSIGNED DEFAULT NULL,
    fecha_anulacion         DATETIME     DEFAULT NULL,
    motivo_anulacion        TEXT         DEFAULT NULL,

    CONSTRAINT fk_transf_origen FOREIGN KEY (almacen_origen_id) REFERENCES almacenes(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_transf_dest FOREIGN KEY (almacen_destino_id) REFERENCES almacenes(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_transf_serv FOREIGN KEY (servicio_destino_id) REFERENCES servicios_medicos(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_transf_regpor FOREIGN KEY (registrado_por) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_transf_anupor FOREIGN KEY (anulado_por) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. TABLA: transferencia_detalle
DROP TABLE IF EXISTS transferencia_detalle;
CREATE TABLE transferencia_detalle (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transferencia_id    INT UNSIGNED NOT NULL,
    medicamento_id      INT UNSIGNED NOT NULL,
    lote_id             INT UNSIGNED DEFAULT NULL,
    cantidad            INT UNSIGNED NOT NULL,

    CONSTRAINT fk_transfdet_transf FOREIGN KEY (transferencia_id) REFERENCES transferencias(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_transfdet_med FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_transfdet_lote FOREIGN KEY (lote_id) REFERENCES lotes_inventario(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. TABLA: devoluciones_proveedores
DROP TABLE IF EXISTS devoluciones_proveedores;
CREATE TABLE devoluciones_proveedores (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_comprobante     VARCHAR(20)  NOT NULL UNIQUE,
    proveedor_id           INT UNSIGNED NOT NULL,
    medicamento_id         INT UNSIGNED NOT NULL,
    lote_id                INT UNSIGNED DEFAULT NULL,
    cantidad               INT UNSIGNED NOT NULL,
    motivo                 TEXT         NOT NULL,
    observaciones          TEXT         DEFAULT NULL,
    estatus                ENUM('Pendiente', 'Aprobada', 'Rechazada', 'Anulada') DEFAULT 'Pendiente',
    registrado_por         INT UNSIGNED DEFAULT NULL,
    fecha_registro         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion       DATETIME     DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_dev_prov FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_dev_med FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_dev_lote FOREIGN KEY (lote_id) REFERENCES lotes_inventario(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. TABLA: kardex (Unificado)
DROP TABLE IF EXISTS kardex;
CREATE TABLE kardex (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medicamento_id      INT UNSIGNED NOT NULL,
    lote_id             INT UNSIGNED DEFAULT NULL,
    tipo_movimiento     ENUM('Entrada', 'Salida', 'Ajuste_Positivo', 'Ajuste_Negativo', 'Anulacion', 'Devolucion') NOT NULL,
    cantidad            INT            NOT NULL,
    stock_anterior      INT            NOT NULL,
    stock_posterior     INT            NOT NULL,
    referencia_tipo     VARCHAR(50)  DEFAULT NULL,
    referencia_id       INT UNSIGNED DEFAULT NULL,
    operacion           VARCHAR(50)  NOT NULL,
    motivo              TEXT         DEFAULT NULL,
    observacion         TEXT         DEFAULT NULL,
    usuario_id          INT UNSIGNED NOT NULL,
    ip_address          VARCHAR(45)  DEFAULT NULL,
    session_id          VARCHAR(100) DEFAULT NULL,
    user_agent          VARCHAR(255) DEFAULT NULL,
    fecha_movimiento    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_kardex_med FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_kardex_lote FOREIGN KEY (lote_id) REFERENCES lotes_inventario(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_kardex_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. TABLA: alertas (Globales)
DROP TABLE IF EXISTS alertas;
CREATE TABLE alertas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo            VARCHAR(50)  NOT NULL,
    nivel           ENUM('Info', 'Advertencia', 'Critico') NOT NULL,
    titulo          VARCHAR(255) NOT NULL,
    mensaje         TEXT         NOT NULL,
    referencia_tipo VARCHAR(50)  DEFAULT NULL,
    referencia_id   INT UNSIGNED DEFAULT NULL,
    resuelta        TINYINT(1)   NOT NULL DEFAULT 0,
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion DATETIME     DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. TABLA: override_ciclos (Alertas Controladas / Excepciones)
DROP TABLE IF EXISTS override_ciclos;
CREATE TABLE override_ciclos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asegurado_id        INT UNSIGNED NOT NULL,
    medicamento_id      INT UNSIGNED NOT NULL,
    clave_autorizacion  VARCHAR(255) NOT NULL,
    motivo              TEXT         NOT NULL,
    autorizado_por      INT UNSIGNED NOT NULL,
    fecha_override      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_override_aseg FOREIGN KEY (asegurado_id) REFERENCES asegurados(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_override_med FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_override_usu FOREIGN KEY (autorizado_por) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. TABLA: auditoria_sistema
DROP TABLE IF EXISTS auditoria_sistema;
CREATE TABLE auditoria_sistema (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT UNSIGNED DEFAULT NULL,
    accion          VARCHAR(50)  NOT NULL,
    modulo          VARCHAR(100) NOT NULL,
    detalle         TEXT         DEFAULT NULL,
    ip_address      VARCHAR(45)  DEFAULT NULL,
    fecha_accion    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. TABLA: logs_descargas
DROP TABLE IF EXISTS logs_descargas;
CREATE TABLE logs_descargas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporte         VARCHAR(50)  NOT NULL,
    formato         ENUM('PDF', 'Excel') NOT NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    fecha_descarga  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address      VARCHAR(45)  DEFAULT NULL,
    CONSTRAINT fk_log_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DATOS INICIALES: Usuario administrador (Admin2026!)
INSERT INTO usuarios (cedula, nombre, apellido, correo, clave, rol)
VALUES ('V-00000001', 'Administrador', 'SIGFA', 'admin@sigfa.local', '$2y$10$rJM9MEiyO5S84FV.0X49NOI6AxhQ4VbH.hFjofIVn7GrXcrygX/12', 'Administrador');

-- Grupos iniciales
INSERT INTO grupos_medicamentos (codigo, nombre) VALUES
('001', 'Inyectables'), ('002', 'Patentados'), ('003', 'Estupefacientes'), ('004', 'Psicotrópicos'), ('005', 'Enfermedades crónicas'), ('006', 'Sin código'), ('007', 'Misceláneos'), ('008', 'Otros');

SET FOREIGN_KEY_CHECKS = 1;


-- =====================================================
-- ARCHIVO: migration_correcciones_27.sql
-- =====================================================

-- =====================================================
-- SIGFA - Migración: Correcciones 27/04/2026
-- =====================================================
-- Ejecutar sobre `sigfa_db` existente.
-- =====================================================

USE sigfa_db;

-- 1. Nuevas columnas en despacho_detalle para entregas parciales y ciclos
ALTER TABLE despacho_detalle
  ADD COLUMN cantidad_recetada INT UNSIGNED DEFAULT NULL AFTER cantidad,
  ADD COLUMN ciclo_asignado    INT UNSIGNED DEFAULT NULL COMMENT 'Días del ciclo asignado al despacho',
  ADD COLUMN fecha_proxima     DATE         DEFAULT NULL COMMENT 'Fecha de próxima entrega disponible';

-- 2. Tabla de logs para overrides de advertencias en despachos
CREATE TABLE IF NOT EXISTS logs_override_despacho (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    despacho_id     INT UNSIGNED NOT NULL,
    medicamento_id  INT UNSIGNED NOT NULL,
    asegurado_id    INT UNSIGNED NOT NULL,
    tipo_override   ENUM('ADVERTENCIA_24H', 'CICLO_PARCIAL', 'SALTO_BLOQUEO') NOT NULL,
    motivo          TEXT         DEFAULT NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    fecha_override  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address      VARCHAR(45)  DEFAULT NULL,

    CONSTRAINT fk_logov_desp FOREIGN KEY (despacho_id) REFERENCES despachos(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_logov_med  FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_logov_aseg FOREIGN KEY (asegurado_id) REFERENCES asegurados(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_logov_usu  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Agregar columna partida_nacimiento al índice de búsqueda si no existe
-- (partida_nacimiento ya tiene UNIQUE constraint, solo agregar a búsqueda)
-- No se requiere ALTER adicional ya que ya es UNIQUE.

-- Fin de migración


-- =====================================================
-- ARCHIVO: migration_principios_activos.sql
-- =====================================================

-- =====================================================
-- SIGFA - Migración: Principios Activos y Refactorización
-- =====================================================

USE sigfa_db;

-- 1. Crear tabla de principios activos
CREATE TABLE IF NOT EXISTS principios_activos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150) NOT NULL UNIQUE,
    creado_en   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agregar columna id_principio_activo a medicamentos
ALTER TABLE medicamentos 
ADD COLUMN id_principio_activo INT UNSIGNED DEFAULT NULL AFTER nombre_comercial,
ADD CONSTRAINT fk_med_principio FOREIGN KEY (id_principio_activo) REFERENCES principios_activos(id) ON UPDATE CASCADE ON DELETE SET NULL;

-- 3. Insertar Algunos Principios Activos Base (Opcional, pero útil para pruebas)
-- INSERT INTO principios_activos (nombre) SELECT DISTINCT nombre_generico FROM medicamentos;
-- UPDATE medicamentos m SET m.id_principio_activo = (SELECT id FROM principios_activos WHERE nombre = m.nombre_generico);

-- 4. Asegurarse de que despacho_detalle tenga los campos necesarios 
-- (Si ya se ejecutó migration_correcciones_27.sql, esto ya existe, pero aseguro compatibilidad)
-- ALTER TABLE despacho_detalle ADD COLUMN IF NOT EXISTS cantidad_recetada INT UNSIGNED DEFAULT NULL AFTER cantidad;
-- ALTER TABLE despacho_detalle ADD COLUMN IF NOT EXISTS ciclo_asignado INT UNSIGNED DEFAULT NULL;
-- ALTER TABLE despacho_detalle ADD COLUMN IF NOT EXISTS fecha_proxima DATE DEFAULT NULL;


-- =====================================================
-- ARCHIVO: migration_final_despachos.sql
-- =====================================================

-- SIGFA V2 - Migración de Correcciones Finales
-- Refactorización de Despachos y Control de Ciclos

-- 1. Asegurar que la tabla despacho_detalle tenga los nuevos campos
ALTER TABLE despacho_detalle 
ADD COLUMN IF NOT EXISTS cantidad_recetada INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS ciclo_asignado INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS fecha_proxima DATE DEFAULT NULL;

-- 2. Eliminar el campo ciclo_tratamiento redundante en asegurados
-- (Opcional, pero recomendado según requerimientos)
ALTER TABLE asegurados DROP COLUMN IF EXISTS ciclo_tratamiento;

-- 3. Crear tabla de logs para excepciones autorizadas (Overrides)
CREATE TABLE IF NOT EXISTS logs_override_despacho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    despacho_id INT NOT NULL,
    usuario_id INT NOT NULL,
    motivo TEXT NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (despacho_id) REFERENCES despachos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Actualizar tabla medicamentos para soportar Principios Activos
CREATE TABLE IF NOT EXISTS principios_activos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    estatus ENUM('Activo', 'Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE medicamentos 
ADD COLUMN IF NOT EXISTS id_principio_activo INT DEFAULT NULL;

ALTER TABLE medicamentos 
ADD CONSTRAINT fk_medicamento_principio
FOREIGN KEY (id_principio_activo) REFERENCES principios_activos(id);

