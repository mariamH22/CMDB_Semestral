-- ============================================================================
-- CMDB Integral - Recepcion fisica independiente de devoluciones
-- Fecha: 2026-07-14
--
-- Permite que la solicitud de devolucion no capture condicion fisica.
-- La condicion real se registra al momento de recepcion por administrador u
-- operador, junto con accesorios y observacion de recepcion.
-- ============================================================================

USE cmdb_integral;

SET @column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones'
      AND COLUMN_NAME = 'estado_fisico'
);

SET @sql := IF(
    @column_exists = 1,
    "ALTER TABLE devoluciones MODIFY COLUMN estado_fisico ENUM('BUENO','REGULAR','DANADO','INCOMPLETO') NULL",
    "SELECT 1"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
