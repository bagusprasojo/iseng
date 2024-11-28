<?php

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("File .env tidak ditemukan di $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Hapus tanda kutip jika ada
        $value = trim($value, '"');

        $_ENV[$key] = $value;
    }
}

loadEnv(__DIR__ . '/.env');

function getDatabaseConfig() {
    return [
        'host' => $_ENV['HOST'],
        'dbname' => $_ENV['DB_NAME'],
        'username' => $_ENV['USER_NAME'],
        'password' => $_ENV['PASSWORD'],
        'dsn' => 'mysql:host=' . $_ENV['HOST'] . ';dbname=' . $_ENV['DB_NAME'],
    ];
}

function getPdoInstance() {
    $dbConfig = getDatabaseConfig();
    try {
        $pdo = new PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Connection failed: ' . $e->getMessage());
    }
}
