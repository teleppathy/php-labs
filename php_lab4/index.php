<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/ArticleRepository.php';
require_once __DIR__ . '/lib/functions.php';

$repo = new ArticleRepository($pdo);

// Фільтр за автором (GET) — безпечно, через підготовлений запит
$selectedAuthor = trim((string) ($_GET['author'] ?? ''));
$articles = $selectedAuthor !== '' ? $repo->findByAuthor($selectedAuthor) : $repo->all();
$recent = $repo->listRecent(3);
$authors = $repo->authors();

// Повідомлення після операцій
$messages = [
    'added'   => ['success', 'Статтю додано.'],
    'updated' => ['success', 'Статтю оновлено.'],
    'deleted' => ['success', 'Статтю видалено.'],
    'invalid' => ['error', 'Заповніть заголовок, текст і автора та вкажіть коректну дату.'],
    'notfound' => ['error', 'Статтю не знайдено.'],
];
$flash = $messages[$_GET['msg'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Блог — статті з бази даних</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Блог / CMS: статті (PDO + MySQL)</h1>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash[0] ?>"><?= e($flash[1]) ?></div>
        <?php endif; ?>

        <!-- Фільтр за автором -->
        <form method="get" action="index.php" class="form-inline">
            <select name="author">
                <option value="">Усі автори</option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?= e($author) ?>" <?= $author === $selectedAuthor ? 'selected' : '' ?>>
                        <?= e($author) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Фільтрувати</button>
            <?php if ($selectedAuthor !== ''): ?>
                <a href="index.php" class="btn btn-light">Скинути</a>
            <?php endif; ?>
        </form>

        <!-- SELECT: усі записи -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Автор</th>
                    <th>Дата</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td><?= (int) $article['id'] ?></td>
                        <td>
                            <strong><?= e($article['title']) ?></strong><br>
                            <span class="muted"><?= e(truncateText($article['content'], 60)) ?></span>
                        </td>
                        <td><?= e($article['author']) ?></td>
                        <td><?= e(formatDate($article['published_at'])) ?></td>
                        <td class="actions">
                            <a href="edit.php?id=<?= (int) $article['id'] ?>" class="btn btn-light btn-sm">Редагувати</a>
                            <form method="post" action="delete.php"
                                  onsubmit="return confirm('Видалити статтю «<?= e(addslashes($article['title'])) ?>»?');">
                                <input type="hidden" name="id" value="<?= (int) $article['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Видалити</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$articles): ?>
                    <tr><td colspan="5">Статей не знайдено.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Останні статті -->
        <h2 class="section-title">Останні статті</h2>
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Заголовок</th>
                    <th>Автор</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $i => $article): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($article['title']) ?></td>
                        <td><?= e($article['author']) ?></td>
                        <td><?= e(formatDate($article['published_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- INSERT: форма додавання -->
        <h2 class="section-title">Додати статтю</h2>
        <form method="post" action="add.php" class="form-box">
            <label for="title">Заголовок</label>
            <input type="text" id="title" name="title" maxlength="255" required>

            <label for="content">Текст</label>
            <textarea id="content" name="content" rows="4" required></textarea>

            <label for="author">Автор</label>
            <input type="text" id="author" name="author" maxlength="100" required>

            <label for="published_at">Дата публікації</label>
            <input type="date" id="published_at" name="published_at" value="<?= date('Y-m-d') ?>" required>

            <button type="submit" class="btn">Додати</button>
        </form>

        <section class="summary">
            <h2>Підсумок</h2>
            <p>Статей у вибірці: <strong><?= count($articles) ?></strong></p>
            <p>Кількість авторів: <strong><?= count($authors) ?></strong></p>
        </section>
    </main>
</body>
</html>
