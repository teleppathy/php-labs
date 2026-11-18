<?php

declare(strict_types=1);

/**
 * API блогу (варіант 3) — єдина точка входу.
 *
 * Контракт (повний опис — у файлі API.md):
 *
 *   GET  api.php?resource=articles              -> 200, список статей
 *   GET  api.php?resource=articles&author=Ім'я  -> 200, список статей автора
 *   GET  api.php?resource=articles&id=N         -> 200, одна стаття | 404
 *   POST api.php?resource=articles              -> 201, створена стаття | 400
 *   POST api.php?resource=articles&action=publish&id=N
 *                                               -> 200, стаття з published_at = NOW() | 400 | 404
 *   інший метод для articles                    -> 405 (+ заголовок Allow)
 *   невідомий ресурс                            -> 404
 *
 * Формат відповіді: {"success": true, "data": ...} або {"success": false, "error": "..."}
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/lib/response.php';
require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/classes/ArticleRepository.php';

$pdo = require_once __DIR__ . '/db.php';
$repo = new ArticleRepository($pdo);

/* ---------- Обробники ---------- */

function listArticles(ArticleRepository $repo): void
{
    $author = isset($_GET['author']) ? trim((string) $_GET['author']) : null;
    sendSuccess(200, $repo->findAll($author));
}

function getArticle(ArticleRepository $repo, int $id): void
{
    $article = $repo->find($id);
    if ($article === null) {
        sendError(404, "Статтю з id={$id} не знайдено.");
    }
    sendSuccess(200, $article);
}

function createArticle(ArticleRepository $repo, array $input): void
{
    $title = trim((string) ($input['title'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $author = trim((string) ($input['author'] ?? ''));
    $publishedAt = trim((string) ($input['published_at'] ?? ''));

    // Обов'язкові поля
    $missing = [];
    foreach (['title' => $title, 'content' => $content, 'author' => $author] as $field => $value) {
        if ($value === '') {
            $missing[] = $field;
        }
    }
    if ($missing) {
        sendError(400, 'Відсутні обов\'язкові поля: ' . implode(', ', $missing) . '.');
    }

    // Додаткові перевірки значень
    if (mb_strlen($title) > 255) {
        sendError(400, 'Поле title не може перевищувати 255 символів.');
    }
    if (mb_strlen($author) > 100) {
        sendError(400, 'Поле author не може перевищувати 100 символів.');
    }
    if ($publishedAt === '') {
        $publishedAt = date('Y-m-d'); // необов'язкове поле: за замовчуванням — сьогодні
    } elseif (!isValidDate($publishedAt)) {
        sendError(400, 'Поле published_at має бути датою у форматі рррр-мм-дд.');
    }

    $id = $repo->create($title, $content, $author, $publishedAt);
    sendSuccess(201, $repo->find($id));
}

function publishArticle(ArticleRepository $repo, ?int $id): void
{
    if ($id === null) {
        sendError(400, 'Для дії publish потрібен параметр id.');
    }
    if ($repo->find($id) === null) {
        sendError(404, "Статтю з id={$id} не знайдено.");
    }

    $repo->publish($id);
    sendSuccess(200, $repo->find($id));
}

/* ---------- Маршрутизація ---------- */

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null;

if ($resource !== 'articles') {
    sendError(404, 'Невідомий ресурс. Доступний ресурс: articles.');
}

if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    sendError(405, "Метод {$method} не підтримується для ресурсу articles.");
}

try {
    if ($method === 'GET') {
        $id = parseId($_GET['id'] ?? null);

        if ($id === null) {
            listArticles($repo);
        } else {
            getArticle($repo, $id);
        }
    }

    // POST
    $input = readInput();
    $action = $_GET['action'] ?? ($input['action'] ?? null);

    if ($action === null || $action === '') {
        createArticle($repo, $input);
    } elseif ($action === 'publish') {
        publishArticle($repo, parseId($_GET['id'] ?? ($input['id'] ?? null)));
    } else {
        sendError(400, 'Невідома дія action. Підтримується: publish.');
    }
} catch (PDOException $e) {
    sendError(500, 'Помилка бази даних.');
}
