<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/ArticleRepository.php';
require_once __DIR__ . '/lib/functions.php';

$repo = new ArticleRepository($pdo);

// Обробка форми (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim((string) ($_POST['title'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $author = trim((string) ($_POST['author'] ?? ''));
    $publishedAt = trim((string) ($_POST['published_at'] ?? ''));

    if ($repo->find($id) === null) {
        header('Location: index.php?msg=notfound');
        exit;
    }

    if ($title === '' || $content === '' || $author === '' || !isValidDate($publishedAt)) {
        header('Location: edit.php?id=' . $id . '&error=1');
        exit;
    }

    $repo->updateArticle($id, $title, $content, $author, $publishedAt);
    header('Location: index.php?msg=updated');
    exit;
}

// Показ форми з поточними значеннями (SELECT ... WHERE id = :id)
$id = (int) ($_GET['id'] ?? 0);
$article = $repo->find($id);

if ($article === null) {
    header('Location: index.php?msg=notfound');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редагування статті</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Редагування статті №<?= (int) $article['id'] ?></h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">Заповніть усі поля та вкажіть коректну дату.</div>
        <?php endif; ?>

        <form method="post" action="edit.php" class="form-box">
            <input type="hidden" name="id" value="<?= (int) $article['id'] ?>">

            <label for="title">Заголовок</label>
            <input type="text" id="title" name="title" maxlength="255"
                   value="<?= e($article['title']) ?>" required>

            <label for="content">Текст</label>
            <textarea id="content" name="content" rows="5" required><?= e($article['content']) ?></textarea>

            <label for="author">Автор</label>
            <input type="text" id="author" name="author" maxlength="100"
                   value="<?= e($article['author']) ?>" required>

            <label for="published_at">Дата публікації</label>
            <input type="date" id="published_at" name="published_at"
                   value="<?= e($article['published_at']) ?>" required>

            <button type="submit" class="btn">Зберегти</button>
            <a href="index.php" class="btn btn-light">Скасувати</a>
        </form>
    </main>
</body>
</html>
