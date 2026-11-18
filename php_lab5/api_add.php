<?php

declare(strict_types=1);

/**
 * POST api_add.php — додає статтю (JSON-тіло або звичайна форма).
 * Відповідь: 201 + JSON доданого запису; 405/422/500 + {"error": "..."} при помилках.
 */

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): never
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Дозволено лише метод POST.']);
}

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/ArticleRepository.php';
require_once __DIR__ . '/lib/functions.php';

// Дані з JSON-тіла (fetch) або з $_POST (класична форма)
$data = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}

$title = trim((string) ($data['title'] ?? ''));
$content = trim((string) ($data['content'] ?? ''));
$author = trim((string) ($data['author'] ?? ''));
$publishedAt = trim((string) ($data['published_at'] ?? ''));

if ($title === '' || $content === '' || $author === '') {
    respond(422, ['error' => 'Заповніть заголовок, текст і автора.']);
}
if (mb_strlen($title) > 255 || mb_strlen($author) > 100) {
    respond(422, ['error' => 'Заголовок може містити до 255 символів, ім\'я автора — до 100.']);
}
if (!isValidDate($publishedAt)) {
    respond(422, ['error' => 'Вкажіть коректну дату публікації.']);
}

try {
    $repo = new ArticleRepository($pdo);
    $id = $repo->addArticle($title, $content, $author, $publishedAt);
    respond(201, $repo->find($id) ?? []);
} catch (PDOException $e) {
    respond(500, ['error' => 'Помилка бази даних під час додавання статті.']);
}
