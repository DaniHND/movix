-- Movix — Datos iniciales
-- Ejecutar DESPUÉS de movix_schema.sql
-- Contiene: 12 tarifas del sistema
-- Para usuarios de prueba, ejecuta: php sql/crear_usuarios_prueba.php

USE movix_db;

-- ============================================================
-- TARIFAS (12 registros — editables desde panel admin)
-- Horario: dia = 04:00–21:00 | noche = 21:00–04:00
-- es_por_km: 0 = precio fijo | 1 = precio por km
-- ============================================================
INSERT INTO tarifas
    (tipo_servicio,    horario,  km_desde, km_hasta, precio_base, es_por_km, precio_pasajero_extra, comision_fija)
VALUES
-- 0–5 km
('convencional', 'dia',   0.0, 5.0,    25.00, 0, 25.00,  5.00),
('convencional', 'noche', 0.0, 5.0,    50.00, 0, 50.00, 10.00),
('vip',          'dia',   0.0, 5.0,    40.00, 0, 40.00, 10.00),
('vip',          'noche', 0.0, 5.0,    80.00, 0, 80.00, 20.00),
-- 6–8 km
('convencional', 'dia',   6.0, 8.0,     7.00, 1,  7.00,  5.00),
('convencional', 'noche', 6.0, 8.0,     8.50, 1,  8.50, 10.00),
('vip',          'dia',   6.0, 8.0,    10.00, 1, 10.00, 10.00),
('vip',          'noche', 6.0, 8.0,    12.50, 1, 12.50, 20.00),
-- 9+ km
('convencional', 'dia',   9.0, 9999.9,  9.00, 1,  9.00, 10.00),
('convencional', 'noche', 9.0, 9999.9, 12.00, 1, 12.00, 10.00),
('vip',          'dia',   9.0, 9999.9, 12.00, 1, 12.00, 20.00),
('vip',          'noche', 9.0, 9999.9, 15.00, 1, 15.00, 25.00);
