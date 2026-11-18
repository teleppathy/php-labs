<?php

declare(strict_types=1);

/**
 * Підключення до бази даних через PDO (та сама БД practicum4, що й у практикумі №4).
 * Повертає об'єкт PDO. У разі помилки підключення віддає JSON із кодом 500,
 * бо цей файл використовують лише API-ендпоінти.
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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        ['error' => 'Не вдалося підключитися до бази даних. Перевірте, що MySQL запущено і база practicum4 створена (schema.sql).'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}
