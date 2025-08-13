<?php
// database.php
declare(strict_types=1);

function pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $config = require __DIR__ . '/../config.php';
    $db = $config['db'];

    $pdo = new PDO(
        $db['dsn'],
        $db['user'],
        $db['pass'],
        $db['options']
    );
    return $pdo;
}
