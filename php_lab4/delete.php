<?php

declare(strict_types=1);

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/ArticleRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$deleted = (new ArticleRepository($pdo))->deleteArticle($id);

header('Location: index.php?msg=' . ($deleted > 0 ? 'deleted' : 'notfound'));
exit;
