<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\DatabaseException;
use PDO;
use PDOException;

final class Database
{
    private static ?self $instance = null;
    private array $schemaCache = [];
    private PDO $pdo;

    private function __construct()
    {
        $host = Config::get('db.host');
        $db = Config::get('db.database');
        $charset = Config::get('db.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        try {
            $this->pdo = new PDO(
                $dsn,
                Config::get('db.user'),
                Config::get('db.password'),
                [
                    // ERRMODE_EXCEPTION evita fallos silenciosos y permite errores controlados.
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Preparados reales de MySQL para reducir riesgo de SQL injection.
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new DatabaseException('No fue posible conectar con la base de datos.', 0, $exception);
        }
    }

    public static function instance(): self
    {
        // Singleton simple: una conexion PDO por solicitud.
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        try {
            // Todas las consultas pasan por prepare/execute con parametros nombrados.
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);
            $row = $statement->fetch();
        } catch (PDOException $exception) {
            throw new DatabaseException('No fue posible consultar la base de datos.', 0, $exception);
        }

        return $row ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            throw new DatabaseException('No fue posible consultar la base de datos.', 0, $exception);
        }
    }

    public function execute(string $sql, array $params = []): bool
    {
        try {
            $statement = $this->pdo->prepare($sql);
            return $statement->execute($params);
        } catch (PDOException $exception) {
            throw new DatabaseException('No fue posible actualizar la base de datos.', 0, $exception);
        }
    }

    public function insert(string $sql, array $params = []): int
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $exception) {
            throw new DatabaseException('No fue posible registrar datos en la base de datos.', 0, $exception);
        }
    }

    public function transaction(callable $callback): mixed
    {
        try {
            // Si una operacion compuesta falla, rollback mantiene la base consistente.
            $this->pdo->beginTransaction();
            $result = $callback($this);
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function tableExists(string $table): bool
    {
        $cacheKey = 'table:' . $table;
        if (!array_key_exists($cacheKey, $this->schemaCache)) {
            // Cachea inspeccion de esquema para soportar migraciones incrementales sin consultar en cada paso.
            $row = $this->fetch(
                "SELECT COUNT(*) AS total
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table",
                ['table' => $table]
            );
            $this->schemaCache[$cacheKey] = (int) ($row['total'] ?? 0) > 0;
        }

        return (bool) $this->schemaCache[$cacheKey];
    }

    public function columnExists(string $table, string $column): bool
    {
        $cacheKey = 'column:' . $table . ':' . $column;
        if (!array_key_exists($cacheKey, $this->schemaCache)) {
            $row = $this->fetch(
                "SELECT COUNT(*) AS total
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table
                   AND COLUMN_NAME = :column",
                ['table' => $table, 'column' => $column]
            );
            $this->schemaCache[$cacheKey] = (int) ($row['total'] ?? 0) > 0;
        }

        return (bool) $this->schemaCache[$cacheKey];
    }

    public function enumValueExists(string $table, string $column, string $value): bool
    {
        $cacheKey = 'enum:' . $table . ':' . $column . ':' . $value;
        if (!array_key_exists($cacheKey, $this->schemaCache)) {
            $row = $this->fetch(
                "SELECT COLUMN_TYPE AS column_type
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table
                   AND COLUMN_NAME = :column",
                ['table' => $table, 'column' => $column]
            );

            $columnType = (string) ($row['column_type'] ?? '');
            $this->schemaCache[$cacheKey] = str_contains($columnType, "'" . str_replace("'", "''", $value) . "'");
        }

        return (bool) $this->schemaCache[$cacheKey];
    }
}
