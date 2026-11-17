<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Крок 6: підключення класів і бібліотеки функцій
require_once __DIR__ . '/classes/Article.php';
require_once __DIR__ . '/classes/FeaturedArticle.php';
require_once __DIR__ . '/classes/Blog.php';
require_once __DIR__ . '/lib/functions.php';

// Створення об'єктів: базового і похідного класів
$blog = new Blog();

$blog->addArticle(new Article(
    'Вступ до PHP для початківців',
    'У цій статті розглядаємо базовий синтаксис PHP, змінні, типи даних та перші скрипти.',
    'Олена Коваленко',
    '2026-09-05'
));
$blog->addArticle(new Article(
    'Асоціативні масиви на прикладах',
    'Асоціативні масиви дозволяють зберігати дані у форматі «ключ — значення».',
    'Андрій Мельник',
    '2026-09-18'
));
$blog->addArticle(new FeaturedArticle(
    'Як працює клієнт-серверна взаємодія',
    'Браузер надсилає HTTP-запит, сервер виконує PHP-код і повертає HTML-відповідь.',
    'Олена Коваленко',
    '2026-10-02',
    'client-server.jpg',
    1
));
$blog->addArticle(new Article(
    'Налаштування локального сервера',
    'Покроково налаштовуємо XAMPP та вбудований сервер PHP для локальної розробки.',
    'Ірина Бондар',
    '2026-10-21'
));
$blog->addArticle(new FeaturedArticle(
    'Основи ООП у PHP',
    'Класи, об\'єкти, конструктори та успадкування — фундамент сучасного PHP-коду.',
    'Андрій Мельник',
    '2026-11-03',
    'oop-basics.png',
    2
));
$blog->addArticle(new Article(
    'Шаблони: змішування PHP та HTML',
    'Як зручно поєднувати PHP-логіку з HTML-розміткою за допомогою альтернативного синтаксису.',
    'Ірина Бондар',
    '2026-11-12'
));

$selectedAuthor = 'Олена Коваленко';
$authorArticles = $blog->findByAuthor($selectedAuthor);
$recentArticles = $blog->listRecent(3);
$featuredCount = count(array_filter(
    $blog->getAll(),
    fn(Article $a): bool => $a instanceof FeaturedArticle
));
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Блог — ООП, список статей</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Блог / CMS: список статей (ООП)</h1>

        <!-- Усі статті -->
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Опис статті</th>
                    <th>Slug</th>
                    <th>Уривок</th>
                    <th>Тип</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blog->getAll() as $index => $article): ?>
                    <?php $isFeatured = $article instanceof FeaturedArticle; ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($article->getInfo()) ?></td>
                        <td class="slug"><?= htmlspecialchars(makeSlug($article->getTitle())) ?></td>
                        <td><?= htmlspecialchars(truncateText($article->getContent(), 40)) ?></td>
                        <td>
                            <span class="badge <?= $isFeatured ? 'badge-featured' : 'badge-normal' ?>">
                                <?= $isFeatured ? 'Рекомендована' : 'Звичайна' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Пошук за автором -->
        <h2 class="section-title">Статті автора: <?= htmlspecialchars($selectedAuthor) ?></h2>
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Опис статті</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authorArticles as $i => $article): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($article->getInfo()) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Найновіші статті -->
        <h2 class="section-title">Останні статті</h2>
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Опис статті</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentArticles as $i => $article): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($article->getInfo()) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <section class="summary">
            <h2>Підсумок</h2>
            <p>Кількість статей: <strong><?= $blog->count() ?></strong></p>
            <p>Із них рекомендованих: <strong><?= $featuredCount ?></strong></p>
            <p>Статей автора «<?= htmlspecialchars($selectedAuthor) ?>»: <strong><?= count($authorArticles) ?></strong></p>
        </section>
    </main>
</body>
</html>
