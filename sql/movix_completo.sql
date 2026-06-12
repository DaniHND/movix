-- ============================================================
-- Movix — Base de datos completa (schema + datos iniciales)
-- Versión consolidada — Junio 2026
-- Incluye: todas las tablas, tarifas, cupones de prueba
-- ============================================================
-- Uso:  mysql -u root -p < sql/movix_completo.sql
-- O en phpMyAdmin: importar este archivo directamente
-- ============================================================

CREATE DATABASE IF NOT EXISTS movix_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE movix_db;

-- ============================================================
-- TABLA: usuarios (clientes de la plataforma)
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id                        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre                    VARCHAR(100)    NOT NULL,
    fecha_nacimiento          DATE            NULL,
    sexo                      ENUM('M','F','otro') NULL,
    telefono                  VARCHAR(20)     NOT NULL,
    email                     VARCHAR(150)    NOT NULL,
    password                  VARCHAR(255)    NOT NULL,
    foto                      VARCHAR(255)    NULL,
    fb_id                     VARCHAR(100)    NULL,
    email_verificado          TINYINT(1)      NOT NULL DEFAULT 0,
    token_verificacion        VARCHAR(100)    NULL,
    token_recuperacion        VARCHAR(100)    NULL,
    token_recuperacion_expira DATETIME        NULL,
    descuento_primer_viaje    TINYINT(1)      NOT NULL DEFAULT 0,
    created_at                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE  KEY uk_usuarios_email    (email),
    UNIQUE  KEY uk_usuarios_telefono (telefono),
    INDEX   idx_usuarios_token_ver   (token_verificacion),
    INDEX   idx_usuarios_token_rec   (token_recuperacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: admins (usuarios del panel de administración)
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL,
    password    VARCHAR(255)    NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: conductores
-- ============================================================
CREATE TABLE IF NOT EXISTS conductores (
    id                      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre                  VARCHAR(100)    NOT NULL,
    fecha_nacimiento        DATE            NOT NULL,
    identidad               VARCHAR(20)     NOT NULL,
    telefono                VARCHAR(20)     NOT NULL,
    email                   VARCHAR(150)    NOT NULL,
    password                VARCHAR(255)    NOT NULL,
    foto_perfil             VARCHAR(255)    NULL,
    foto_identidad_frente   VARCHAR(255)    NULL,
    foto_identidad_reverso  VARCHAR(255)    NULL,
    foto_licencia_frente    VARCHAR(255)    NULL,
    foto_licencia_reverso   VARCHAR(255)    NULL,
    foto_revision_frente    VARCHAR(255)    NULL,
    foto_revision_reverso   VARCHAR(255)    NULL,
    tipo                    ENUM('convencional','vip') NOT NULL,
    estado                  ENUM('pendiente','aprobado','rechazado','suspendido') NOT NULL DEFAULT 'pendiente',
    activo                  TINYINT(1)      NOT NULL DEFAULT 0,
    fb_id                   VARCHAR(100)    NULL,
    fcm_token               VARCHAR(255)    NULL,
    lat_actual              DECIMAL(10,7)   NULL,
    lng_actual              DECIMAL(10,7)   NULL,
    ultima_posicion         DATETIME        NULL,
    penalizado_hasta        DATETIME        NULL,
    email_verificado        TINYINT(1)      NOT NULL DEFAULT 0,
    token_verificacion      VARCHAR(100)    NULL,
    created_at              DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE  KEY uk_conductores_email     (email),
    UNIQUE  KEY uk_conductores_identidad (identidad),
    INDEX   idx_conductores_estado       (estado),
    INDEX   idx_conductores_activo       (activo),
    INDEX   idx_conductores_tipo         (tipo),
    INDEX   idx_conductores_posicion     (lat_actual, lng_actual)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: vehiculos
-- ============================================================
CREATE TABLE IF NOT EXISTS vehiculos (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    conductor_id    INT UNSIGNED    NOT NULL,
    tipo            ENUM('convencional','vip') NOT NULL,
    marca           VARCHAR(80)     NOT NULL,
    modelo          VARCHAR(80)     NOT NULL,
    anio            YEAR            NOT NULL,
    placa           VARCHAR(20)     NOT NULL,
    color           VARCHAR(40)     NOT NULL,
    polarizado      TINYINT(1)      NOT NULL DEFAULT 0,
    numero_taxi     VARCHAR(20)     NULL,
    foto_frente     VARCHAR(255)    NULL,
    foto_atras      VARCHAR(255)    NULL,
    foto_lado_izq   VARCHAR(255)    NULL,
    foto_lado_der   VARCHAR(255)    NULL,
    foto_tablero    VARCHAR(255)    NULL,
    foto_asiento1   VARCHAR(255)    NULL,
    foto_asiento2   VARCHAR(255)    NULL,
    PRIMARY KEY (id),
    UNIQUE  KEY uk_vehiculos_placa      (placa),
    INDEX   idx_vehiculos_conductor_id  (conductor_id),
    CONSTRAINT fk_vehiculos_conductor FOREIGN KEY (conductor_id)
        REFERENCES conductores (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: tarifas
-- ============================================================
CREATE TABLE IF NOT EXISTS tarifas (
    id                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    tipo_servicio         ENUM('convencional','vip') NOT NULL,
    horario               ENUM('dia','noche') NOT NULL,
    km_desde              DECIMAL(5,1)    NOT NULL,
    km_hasta              DECIMAL(5,1)    NOT NULL,
    precio_base           DECIMAL(10,2)   NOT NULL,
    es_por_km             TINYINT(1)      NOT NULL DEFAULT 0,
    precio_pasajero_extra DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    comision_fija         DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    INDEX idx_tarifas_tipo_horario (tipo_servicio, horario),
    INDEX idx_tarifas_rango        (km_desde, km_hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: viajes  (tabla central del negocio)
-- ============================================================
CREATE TABLE IF NOT EXISTS viajes (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    cliente_id          INT UNSIGNED    NOT NULL,
    conductor_id        INT UNSIGNED    NULL,
    tipo_servicio       ENUM('convencional','vip') NOT NULL,
    num_pasajeros       TINYINT         NOT NULL DEFAULT 1,
    ida_y_regreso       TINYINT(1)      NOT NULL DEFAULT 0,
    lat_origen          DECIMAL(10,7)   NOT NULL,
    lng_origen          DECIMAL(10,7)   NOT NULL,
    direccion_origen    VARCHAR(255)    NULL,
    lat_destino         DECIMAL(10,7)   NOT NULL,
    lng_destino         DECIMAL(10,7)   NOT NULL,
    direccion_destino   VARCHAR(255)    NULL,
    distancia_km        DECIMAL(8,2)    NULL,
    duracion_min        INT             NULL,
    horario             ENUM('dia','noche') NOT NULL DEFAULT 'dia',
    precio_base         DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    comision_app        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    precio_total        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    estado              ENUM('pendiente','asignado','en_curso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
    motivo_cancelacion  TEXT            NULL,
    fecha_solicitud     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_asignacion    DATETIME        NULL,
    fecha_inicio        DATETIME        NULL,
    fecha_fin           DATETIME        NULL,
    PRIMARY KEY (id),
    INDEX idx_viajes_cliente_id   (cliente_id),
    INDEX idx_viajes_conductor_id (conductor_id),
    INDEX idx_viajes_estado       (estado),
    INDEX idx_viajes_fecha        (fecha_solicitud),
    CONSTRAINT fk_viajes_cliente   FOREIGN KEY (cliente_id)   REFERENCES usuarios    (id) ON UPDATE CASCADE,
    CONSTRAINT fk_viajes_conductor FOREIGN KEY (conductor_id) REFERENCES conductores (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: favoritos
-- ============================================================
CREATE TABLE IF NOT EXISTS favoritos (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    cliente_id      INT UNSIGNED    NOT NULL,
    conductor_id    INT UNSIGNED    NOT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE  KEY uk_favoritos_relacion  (cliente_id, conductor_id),
    INDEX   idx_favoritos_cliente      (cliente_id),
    INDEX   idx_favoritos_conductor    (conductor_id),
    CONSTRAINT fk_favoritos_cliente   FOREIGN KEY (cliente_id)   REFERENCES usuarios    (id) ON DELETE CASCADE,
    CONSTRAINT fk_favoritos_conductor FOREIGN KEY (conductor_id) REFERENCES conductores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: notificaciones_viaje
-- ============================================================
CREATE TABLE IF NOT EXISTS notificaciones_viaje (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    viaje_id        INT UNSIGNED    NOT NULL,
    conductor_id    INT UNSIGNED    NOT NULL,
    estado          ENUM('enviado','aceptado','rechazado','expirado') NOT NULL DEFAULT 'enviado',
    motivo_rechazo  TEXT            NULL,
    enviado_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    respondido_at   DATETIME        NULL,
    PRIMARY KEY (id),
    INDEX idx_notif_viaje     (viaje_id),
    INDEX idx_notif_conductor (conductor_id),
    INDEX idx_notif_estado    (estado),
    CONSTRAINT fk_notif_viaje     FOREIGN KEY (viaje_id)     REFERENCES viajes      (id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_conductor FOREIGN KEY (conductor_id) REFERENCES conductores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: cupones
-- ============================================================
CREATE TABLE IF NOT EXISTS cupones (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    codigo          VARCHAR(30)      NOT NULL,
    descuento_pct   TINYINT UNSIGNED NOT NULL,
    usos_max        INT UNSIGNED     NOT NULL DEFAULT 1,
    usos_actuales   INT UNSIGNED     NOT NULL DEFAULT 0,
    vence_at        DATETIME         NULL,
    activo          TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_cupones_codigo (codigo),
    INDEX  idx_cupones_activo   (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: cupones_uso
-- ============================================================
CREATE TABLE IF NOT EXISTS cupones_uso (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    cupon_id    INT UNSIGNED    NOT NULL,
    cliente_id  INT UNSIGNED    NOT NULL,
    viaje_id    INT UNSIGNED    NULL,
    usado_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_cupones_uso_cupon   (cupon_id),
    INDEX idx_cupones_uso_cliente (cliente_id),
    CONSTRAINT fk_cupon_uso_cupon   FOREIGN KEY (cupon_id)   REFERENCES cupones  (id) ON DELETE CASCADE,
    CONSTRAINT fk_cupon_uso_cliente FOREIGN KEY (cliente_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_cupon_uso_viaje   FOREIGN KEY (viaje_id)   REFERENCES viajes   (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: mensajes_chat
-- Nota: viaje_id incluido desde el diseño inicial (no requiere migración)
-- ============================================================
CREATE TABLE IF NOT EXISTS mensajes_chat (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    viaje_id    INT UNSIGNED    NULL,
    de_tipo     ENUM('cliente','conductor','admin') NOT NULL,
    de_id       INT UNSIGNED    NOT NULL,
    para_tipo   ENUM('cliente','conductor','admin') NOT NULL,
    para_id     INT UNSIGNED    NOT NULL,
    mensaje     TEXT            NOT NULL,
    leido       TINYINT(1)      NOT NULL DEFAULT 0,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_chat_viaje (viaje_id),
    INDEX idx_chat_de    (de_tipo,   de_id),
    INDEX idx_chat_para  (para_tipo, para_id),
    INDEX idx_chat_leido (leido),
    CONSTRAINT fk_chat_viaje FOREIGN KEY (viaje_id) REFERENCES viajes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: estadisticas_diarias
-- ============================================================
CREATE TABLE IF NOT EXISTS estadisticas_diarias (
    id                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    conductor_id      INT UNSIGNED    NOT NULL,
    fecha             DATE            NOT NULL,
    total_viajes      INT UNSIGNED    NOT NULL DEFAULT 0,
    km_totales        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    ganancia_neta     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    comisiones_total  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    viajes_perdidos   INT UNSIGNED    NOT NULL DEFAULT 0,
    horas_trabajadas  DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    UNIQUE  KEY uk_stats_conductor_fecha (conductor_id, fecha),
    INDEX   idx_stats_conductor          (conductor_id),
    CONSTRAINT fk_stats_conductor FOREIGN KEY (conductor_id) REFERENCES conductores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: comisiones_pagos
-- ============================================================
CREATE TABLE IF NOT EXISTS comisiones_pagos (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    conductor_id    INT UNSIGNED    NOT NULL,
    fecha           DATE            NOT NULL,
    monto           DECIMAL(10,2)   NOT NULL,
    marcado_por     INT UNSIGNED    NOT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_comisiones_conductor (conductor_id),
    INDEX idx_comisiones_fecha     (fecha),
    CONSTRAINT fk_comisiones_conductor FOREIGN KEY (conductor_id) REFERENCES conductores (id) ON DELETE CASCADE,
    CONSTRAINT fk_comisiones_admin     FOREIGN KEY (marcado_por)  REFERENCES admins      (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS INICIALES: Tarifas (12 registros, editables desde admin)
-- Horario: dia = 04:00–21:00 | noche = 21:00–04:00
-- es_por_km: 0 = precio fijo tramo | 1 = precio por km
-- ============================================================
INSERT INTO tarifas
    (tipo_servicio, horario, km_desde, km_hasta, precio_base, es_por_km, precio_pasajero_extra, comision_fija)
VALUES
-- Tramo 0–5 km (precio fijo)
('convencional', 'dia',    0.0,  5.0,   25.00, 0, 25.00,  5.00),
('convencional', 'noche',  0.0,  5.0,   50.00, 0, 50.00, 10.00),
('vip',          'dia',    0.0,  5.0,   40.00, 0, 40.00, 10.00),
('vip',          'noche',  0.0,  5.0,   80.00, 0, 80.00, 20.00),
-- Tramo 6–8 km (precio por km)
('convencional', 'dia',    6.0,  8.0,    7.00, 1,  7.00,  5.00),
('convencional', 'noche',  6.0,  8.0,    8.50, 1,  8.50, 10.00),
('vip',          'dia',    6.0,  8.0,   10.00, 1, 10.00, 10.00),
('vip',          'noche',  6.0,  8.0,   12.50, 1, 12.50, 20.00),
-- Tramo 9+ km (precio por km)
('convencional', 'dia',    9.0, 9999.9,  9.00, 1,  9.00, 10.00),
('convencional', 'noche',  9.0, 9999.9, 12.00, 1, 12.00, 10.00),
('vip',          'dia',    9.0, 9999.9, 12.00, 1, 12.00, 20.00),
('vip',          'noche',  9.0, 9999.9, 15.00, 1, 15.00, 25.00);

-- ============================================================
-- DATOS INICIALES: Cupones de descuento de ejemplo
-- ============================================================
INSERT IGNORE INTO cupones (codigo, descuento_pct, usos_max, activo)
VALUES
    ('BIENVENIDO', 20, 100, 1),
    ('MOVIX10',    10, 500, 1);

-- ============================================================
-- Para crear los usuarios de prueba (cliente, conductor, admin):
--   php sql/crear_usuarios_prueba.php
-- Credenciales:
--   Cliente:   test@movix.com       / Movix1234
--   Conductor: conductor@movix.com  / Conductor1234
--   Admin:     admin@movix.com      / Admin1234
-- ============================================================
