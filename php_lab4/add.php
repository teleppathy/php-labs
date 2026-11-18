<?php

declare(strict_types=1);

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/ArticleRepository.php';
require_once __DIR__ . '/lib/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$title = trim((string) ($_POST['title'] ?? ''));
$content = trim((string) ($_POST['content'] ?? ''));
$author = trim((string) ($_POST['author'] ?? ''));
$publishedAt = trim((string) ($_POST['published_at'] ?? ''));

if ($title === '' || $content === '' || $author === '' || !isValidDate($publishedAt)) {
    header('Location: index.php?msg=invalid');
    exit;
}

(new ArticleRepository($pdo))->addArticle($title, $content, $author, $publishedAt);

header('Location: index.php?msg=added');
exit;
