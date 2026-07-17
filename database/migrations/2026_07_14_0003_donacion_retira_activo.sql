-- ============================================================================
-- CMDB Integral - Donaciones fuera del inventario operativo
-- Fecha: 2026-07-14
--
-- Objetivo:
--   Corregir datos existentes donde un activo DONADO siga marcado como activo.
--   El reporte de donaciones conserva el historico desde la consulta de la app.
-- ============================================================================

USE cmdb_integral;

START TRANSACTION;

SET @cmdb_has_activo := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario'
      AND COLUMN_NAME = 'activo'
);

SET @cmdb_sql := IF(
    @cmdb_has_activo = 1,
    "UPDATE inventario SET activo = 0 WHERE estado = 'DONADO' AND activo <> 0",
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

COMMIT;
