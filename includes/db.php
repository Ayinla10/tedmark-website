<?php
require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static bool $connectionFailed = false;

    public static function getInstance(): ?PDO {
        if (self::$connectionFailed) return null;
        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 2,
                ]);
            } catch (PDOException $e) {
                error_log('DB Connection failed: ' . $e->getMessage());
                self::$connectionFailed = true;
                self::$instance = null;
                return null;
            }
        }
        return self::$instance;
    }
}

function db(): ?PDO {
    return Database::getInstance();
}

function query(string $sql, array $params = []): ?PDOStatement {
    $db = db();
    if (!$db) return null;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('Query error: ' . $e->getMessage());
        return null;
    }
}

function fetchOne(string $sql, array $params = []): ?array {
    $stmt = query($sql, $params);
    if (!$stmt) return null;
    $result = $stmt->fetch();
    return $result ?: null;
}

function fetchAll(string $sql, array $params = []): array {
    $stmt = query($sql, $params);
    if (!$stmt) return [];
    return $stmt->fetchAll();
}

function insert(string $table, array $data): int {
    $db = db();
    if (!$db) return 0;
    $cols = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    query("INSERT INTO `$table` ($cols) VALUES ($placeholders)", array_values($data));
    return (int) $db->lastInsertId();
}

function update(string $table, array $data, string $where, array $whereParams = []): int {
    $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
    $stmt = query("UPDATE `$table` SET $set WHERE $where", [...array_values($data), ...$whereParams]);
    return $stmt ? $stmt->rowCount() : 0;
}
