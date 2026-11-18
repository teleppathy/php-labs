<?php

declare(strict_types=1);

/**
 * GET api_list.php[?q=текст] — JSON-масив статей (за потреби відфільтрований за заголовком).
 */

header('Content-Type: application/json; charset=utf-8');

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/ArticleRepository.php';

$query = trim((string) ($_GET['q'] ?? ''));

try {
    $rows = (new ArticleRepository($pdo))->searchByTitle($query);
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Помилка бази даних під час отримання списку.'], JSON_UNESCAPED_UNICODE);
}
