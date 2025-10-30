<?php
// Simple helper: reads tables from a SQLite DB and inserts into MySQL using PDO.
// Usage: php migrate_sqlite_to_mysql.php /path/to/users.db

if ($argc < 2) {
    echo "Usage: php migrate_sqlite_to_mysql.php /path/to/sqlite.db [mysql_host] [mysql_port] [mysql_user] [mysql_pass]\n";
    echo "Or set env vars MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASS\n";
    exit(1);
}

$sqliteFile = $argv[1];
if (!file_exists($sqliteFile)) {
    echo "SQLite file not found: $sqliteFile\n";
    exit(1);
}

// Defaults: assume host mapping used by docker-compose (host -> 127.0.0.1:3307)
$mysqlHost = getenv('MYSQL_HOST') !== false ? getenv('MYSQL_HOST') : ($argv[2] ?? '127.0.0.1');
$mysqlPort = getenv('MYSQL_PORT') !== false ? getenv('MYSQL_PORT') : ($argv[3] ?? '3307');
$mysqlUser = getenv('MYSQL_USER') !== false ? getenv('MYSQL_USER') : ($argv[4] ?? 'liga');
$mysqlPass = getenv('MYSQL_PASS') !== false ? getenv('MYSQL_PASS') : ($argv[5] ?? 'secret');

$mysqlDsn = "mysql:host={$mysqlHost};port={$mysqlPort};dbname=liga;charset=utf8mb4";

try {
    $sqlite = new PDO('sqlite:' . $sqliteFile);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connecting to MySQL: $mysqlDsn (user=$mysqlUser)\n";
    $mysql = new PDO($mysqlDsn, $mysqlUser, $mysqlPass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // List tables (adjust as needed)
    $tables = ['users','athletes','clubs','tournaments'];
    foreach ($tables as $table) {
        echo "Migrating $table...\n";
        $res = $sqlite->query("SELECT * FROM $table");
        $cols = [];
        $first = $res->fetch(PDO::FETCH_ASSOC);
        if (!$first) {
            echo "  no rows in $table\n";
            continue;
        }
        $cols = array_keys($first);
        // prepare insert
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $colList = implode(',', array_map(function($c){return "`$c`";}, $cols));
        $stmt = $mysql->prepare("INSERT INTO `$table` ($colList) VALUES ($placeholders)");
        // insert first
        $stmt->execute(array_values($first));
        // rest
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $stmt->execute(array_values($row));
        }
        echo "  done.\n";
    }

    echo "Migration completed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
