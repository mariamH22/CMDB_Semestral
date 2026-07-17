-- ============================================================================
-- CMDB Integral - Presupuesto anual y quinquenal
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. Permite separar partidas con costo y solicitudes sin costo estimado.
-- ============================================================================

USE cmdb_integral;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuestos' AND COLUMN_NAME = 'presupuesto_base');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuestos ADD COLUMN presupuesto_base DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER total_estimado', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuestos' AND COLUMN_NAME = 'inflacion_anual');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuestos ADD COLUMN inflacion_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER presupuesto_base', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuestos' AND COLUMN_NAME = 'crecimiento_anual');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuestos ADD COLUMN crecimiento_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER inflacion_anual', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuestos' AND COLUMN_NAME = 'total_quinquenal');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuestos ADD COLUMN total_quinquenal DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER crecimiento_anual', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuestos' AND COLUMN_NAME = 'registros_sin_costo');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuestos ADD COLUMN registros_sin_costo INT UNSIGNED NOT NULL DEFAULT 0 AFTER total_quinquenal', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuestos' AND COLUMN_NAME = 'supuestos');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuestos ADD COLUMN supuestos TEXT NULL AFTER registros_sin_costo', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuestos' AND COLUMN_NAME = 'filtros_json');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuestos ADD COLUMN filtros_json TEXT NULL AFTER supuestos', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'costo_base');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN costo_base DECIMAL(12,2) NULL AFTER costo_unitario', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'year_index');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN year_index TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER anio', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'factor_proyeccion');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN factor_proyeccion DECIMAL(16,8) NOT NULL DEFAULT 1.00000000 AFTER year_index', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'inflacion_anual');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN inflacion_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER factor_proyeccion', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'crecimiento_anual');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN crecimiento_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00 AFTER inflacion_anual', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'tiene_costo');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN tiene_costo TINYINT(1) NOT NULL DEFAULT 1 AFTER crecimiento_anual', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'motivo_sin_costo');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN motivo_sin_costo VARCHAR(255) NULL AFTER tiene_costo', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'prioridad');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN prioridad ENUM(''BAJA'',''MEDIA'',''ALTA'') NULL AFTER motivo_sin_costo', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND COLUMN_NAME = 'estado_solicitud');
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE presupuesto_detalles ADD COLUMN estado_solicitud VARCHAR(40) NULL AFTER prioridad', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

UPDATE presupuestos
SET presupuesto_base = total_estimado
WHERE presupuesto_base = 0.00;

UPDATE presupuestos
SET total_quinquenal = total_estimado
WHERE total_quinquenal = 0.00;

UPDATE presupuesto_detalles
SET costo_base = costo_unitario
WHERE costo_base IS NULL;

SET @cmdb_index_exists := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND INDEX_NAME = 'idx_presupuesto_detalles_anio');
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_presupuesto_detalles_anio ON presupuesto_detalles(presupuesto_id, anio)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND INDEX_NAME = 'idx_presupuesto_detalles_costo');
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_presupuesto_detalles_costo ON presupuesto_detalles(presupuesto_id, tiene_costo)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'presupuesto_detalles' AND INDEX_NAME = 'idx_presupuesto_detalles_filtros');
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_presupuesto_detalles_filtros ON presupuesto_detalles(anio, categoria_id, tipo_necesidad, prioridad, estado_solicitud)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;
