<?php

// Крок 1: масив статей 
$articles = [
    ['title' => 'Вступ до PHP для початківців',      'author' => 'Олена Коваленко', 'publishedAt' => '2026-09-05', 'views' => 245],
    ['title' => 'Асоціативні масиви на прикладах',   'author' => 'Андрій Мельник',  'publishedAt' => '2026-09-18', 'views' => 87],
    ['title' => 'Як працює клієнт-серверна взаємодія', 'author' => 'Олена Коваленко', 'publishedAt' => '2026-10-02', 'views' => 130],
    ['title' => 'Налаштування локального сервера',   'author' => 'Ірина Бондар',    'publishedAt' => '2026-10-21', 'views' => 56],
    ['title' => 'Цикли та функції в PHP',            'author' => 'Андрій Мельник',  'publishedAt' => '2026-11-03', 'views' => 101],
    ['title' => 'Шаблони: змішування PHP та HTML',   'author' => 'Ірина Бондар',    'publishedAt' => '2026-11-12', 'views' => 12],
];

// Крок 2: типізована функція форматування одного запису
function formatArticle(array $article): string
{
    return "«{$article['title']}» — {$article['author']}, "
        . date('d.m.Y', strtotime($article['publishedAt']));
}

// Крок 3: умовна логіка — мітка за кількістю переглядів
function getLabel(array $article): string
{
    if ($article['views'] > 100) {
        return 'Популярна';
    } else {
        return 'Звичайна';
    }
}

// Крок 4: агрегат — сумарна кількість переглядів усіх статей
$totalViews = array_sum(array_column($articles, 'views'));
$totalCount = count($articles);
$popularCount = count(array_filter($articles, fn(array $a): bool => $a['views'] > 100));
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Блог — список статей</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Блог / CMS: список статей</h1>

        <!-- Крок 5: виведення даних у таблицю через foreach -->
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Опис статті</th>
                    <th>Перегляди</th>
                    <th>Мітка</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $index => $article): ?>
                    <?php $label = getLabel($article); ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars(formatArticle($article)) ?></td>
                        <td><?= $article['views'] ?></td>
                        <td>
                            <span class="badge <?= $label === 'Популярна' ? 'badge-popular' : 'badge-normal' ?>">
                                <?= $label ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Крок 6: окремий блок з агрегатним показником -->
        <section class="summary">
            <h2>Підсумок</h2>
            <p>Кількість статей: <strong><?= $totalCount ?></strong></p>
            <p>Із них популярних: <strong><?= $popularCount ?></strong></p>
            <p>Сумарна кількість переглядів усіх статей: <strong><?= $totalViews ?></strong></p>
        </section>
    </main>
</body>
</html>