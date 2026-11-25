<?php
declare(strict_types=1);

/** Як пайвастшавӣ ба тамоми дархост; дар аввалин истифода сохта мешавад. */
final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    /** Як маротиба дар public/index.php даъват мешавад */
    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$pdo = null;
    }

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $c = self::$config;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // хатои SQL → истисно
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // сатр → массиви ассотсиативӣ
                PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statements-и ҳақиқӣ
            ];
            if (($c['driver'] ?? 'mysql') === 'sqlite') {
                // Танҳо барои санҷишҳои автоматӣ
                self::$pdo = new PDO('sqlite:' . $c['path'], null, null, $options);
                self::$pdo->exec('PRAGMA foreign_keys = ON');
            } else {
                // charset=utf8mb4 — вагарна кириллица ба «????» табдил мешавад
                $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'], $c['name']);
                self::$pdo = new PDO($dsn, $c['user'], $c['pass'], $options);
            }
        }
        return self::$pdo;
    }

    /** Хатои калиди хориҷӣ: MySQL/MariaDB — 1451 (ҳангоми нест кардан) ё 1452; SQLite (танҳо санҷишҳо) — 19 */
    public static function isForeignKeyError(PDOException $e): bool
    {
        $code = $e->errorInfo[1] ?? null;
        return in_array($code, [1451, 1452, 19], true);
    }
}
