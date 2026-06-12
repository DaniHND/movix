-- Movix — Migración Fase 6
-- Ejecutar: mysql -u root -p movix_db < sql/fase6_migration.sql

USE movix_db;

-- Agregar viaje_id a mensajes_chat para filtrar por viaje
ALTER TABLE mensajes_chat
    ADD COLUMN viaje_id INT UNSIGNED NULL AFTER id,
    ADD INDEX  idx_chat_viaje (viaje_id),
    ADD CONSTRAINT fk_chat_viaje
        FOREIGN KEY (viaje_id) REFERENCES viajes (id) ON DELETE CASCADE;

-- Cupón de bienvenida de ejemplo
INSERT IGNORE INTO cupones (codigo, descuento_pct, usos_max, activo)
VALUES
    ('BIENVENIDO', 20, 100, 1),
    ('MOVIX10',    10, 500, 1);
