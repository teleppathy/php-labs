<?php

declare(strict_types=1);

/**
 * Підключення до бази даних через PDO.
 * Повертає об'єкт PDO; підключати через require_once.
 */

$dsn = 'mysql:host=localhost;dbname=practicum4;charset=utf8mb4';
$user = 'root';
$password = '';

try {
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Не вдалося підключитися до бази даних. Перевірте, що MySQL запущено, '
        . 'база practicum4 створена (schema.sql) і дані в db.php правильні.<br>'
        . 'Повідомлення: ' . htmlspecialchars($e->getMessage()));
}
