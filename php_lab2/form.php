<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ---------- Дані та функції лаб №1  ----------
$articles = [
    ['title' => 'Вступ до PHP для початківців',      'author' => 'Олена Коваленко', 'publishedAt' => '2026-09-05', 'views' => 245],
    ['title' => 'Асоціативні масиви на прикладах',   'author' => 'Андрій Мельник',  'publishedAt' => '2026-09-18', 'views' => 87],
    ['title' => 'Як працює клієнт-серверна взаємодія', 'author' => 'Олена Коваленко', 'publishedAt' => '2026-10-02', 'views' => 130],
    ['title' => 'Налаштування локального сервера',   'author' => 'Ірина Бондар',    'publishedAt' => '2026-10-21', 'views' => 56],
    ['title' => 'Цикли та функції в PHP',            'author' => 'Андрій Мельник',  'publishedAt' => '2026-11-03', 'views' => 101],
    ['title' => 'Шаблони: змішування PHP та HTML',   'author' => 'Ірина Бондар',    'publishedAt' => '2026-11-12', 'views' => 12],
];

function formatArticle(array $article): string
{
    return "«{$article['title']}» — {$article['author']}, "
        . date('d.m.Y', strtotime($article['publishedAt']));
}

function getLabel(array $article): string
{
    if ($article['views'] > 100) {
        return 'Популярна';
    } else {
        return 'Звичайна';
    }
}

// ---------- Крок 3: серверна обробка й валідація ----------
$errors = [];
$title = '';
$content = '';
$tagsRaw = '';
$tags = [];
$submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
$success = false;

if ($submitted) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $tagsRaw = trim($_POST['tags'] ?? '');

    // title: обов'язковий, від 5 до 100 символів
    $titleLen = mb_strlen($title);
    if ($title === '') {
        $errors['title'] = 'Заголовок обов\'язковий';
    } elseif ($titleLen < 5 || $titleLen > 100) {
        $errors['title'] = "Заголовок має містити від 5 до 100 символів (зараз: $titleLen)";
    }

    // content: не менше 20 символів
    $contentLen = mb_strlen($content);
    if ($content === '') {
        $errors['content'] = 'Текст статті обов\'язковий';
    } elseif ($contentLen < 20) {
        $errors['content'] = "Текст має містити не менше 20 символів (зараз: $contentLen)";
    }

    // tags: необов'язкове поле, теги через кому
    if ($tagsRaw !== '') {
        $tags = array_values(array_filter(
            array_map('trim', explode(',', $tagsRaw)),
            fn(string $t): bool => $t !== ''
        ));
    }

    // ---------- Крок 4: результат ----------
    if (empty($errors)) {
        $success = true;
        // Додаємо нову статтю до списку 
        $articles[] = [
            'title'       => $title,
            'author'      => 'Ви',
            'publishedAt' => date('Y-m-d'),
            'views'       => 0,
        ];
    }
}

/** Скорочений запис екранування для виводу в HTML */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Блог — нова стаття</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-box {
            margin-bottom: 24px;
            padding: 16px 20px;
            background: #fff;
            border-left: 4px solid #2c3e50;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }
        .form-box h2 { margin-top: 0; }
        .field { margin-bottom: 16px; }
        .field label { display: block; margin-bottom: 6px; font-weight: bold; }
        .field input[type="text"], .field textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 8px 10px;
            border: 1px solid #c9ced4;
            border-radius: 4px;
            font: inherit;
        }
        .field textarea { min-height: 140px; resize: vertical; }
        .field .hint { font-size: 13px; color: #666; margin-top: 4px; }
        .field.invalid input, .field.invalid textarea { border-color: #b3261e; }
        .error { color: #b3261e; font-size: 14px; margin-top: 4px; }
        .alert-error {
            padding: 10px 14px; margin-bottom: 16px;
            background: #fde7e6; color: #b3261e; border-radius: 4px;
        }
        .alert-success {
            padding: 10px 14px; margin-bottom: 16px;
            background: #e3f4e6; color: #1e6b2f; border-radius: 4px;
        }
        .btn {
            padding: 9px 18px; border: 0; border-radius: 4px;
            background: #2c3e50; color: #fff; font: inherit; cursor: pointer;
        }
        .btn:hover { background: #3d566e; }
        .btn-secondary { background: #e3e6ea; color: #444; margin-left: 8px; }
        .btn-secondary:hover { background: #d3d7dc; }
        .draft-status { margin-left: 12px; font-size: 13px; color: #666; }
        .tag-list { margin: 0; padding: 0; list-style: none; }
        .tag-list li { display: inline-block; margin-right: 6px; }
    </style>
</head>
<body>
    <main class="container">
        <h1>Блог / CMS: нова стаття</h1>

        <?php if ($success): ?>
            <!-- Підтвердження з прийнятими даними -->
            <section class="form-box">
                <div class="alert-success">Статтю прийнято!</div>
                <p><strong>Заголовок:</strong> <?= e($title) ?></p>
                <p><strong>Текст:</strong><br><?= nl2br(e($content)) ?></p>
                <p><strong>Теги:</strong>
                    <?php if ($tags): ?>
                        <ul class="tag-list">
                            <?php foreach ($tags as $tag): ?>
                                <li><span class="badge badge-normal"><?= e($tag) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        не вказано
                    <?php endif; ?>
                </p>
                <p><a href="form.php">Додати ще одну статтю</a></p>
            </section>
            <script>
                // Стаття надіслана — чернетка більше не потрібна
                try { localStorage.removeItem('blog_article_draft'); } catch (e) {}
            </script>
        <?php else: ?>
            <!-- Крок 2: HTML-форма -->
            <section class="form-box">
                <h2>Нова стаття</h2>

                <?php if ($errors): ?>
                    <div class="alert-error">Виправте помилки у формі та спробуйте ще раз.</div>
                <?php endif; ?>
                <div id="clientError" class="alert-error" style="display:none;"></div>

                <form id="articleForm" method="post" action="form.php" novalidate>
                    <div class="field <?= isset($errors['title']) ? 'invalid' : '' ?>">
                        <label for="title">Заголовок *</label>
                        <input type="text" id="title" name="title"
                               required minlength="5" maxlength="100"
                               value="<?= e($title) ?>">
                        <div class="hint">Від 5 до 100 символів</div>
                        <?php if (isset($errors['title'])): ?>
                            <div class="error"><?= e($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="field <?= isset($errors['content']) ? 'invalid' : '' ?>">
                        <label for="content">Текст статті *</label>
                        <textarea id="content" name="content"
                                  required minlength="20"><?= e($content) ?></textarea>
                        <div class="hint">Не менше 20 символів</div>
                        <?php if (isset($errors['content'])): ?>
                            <div class="error"><?= e($errors['content']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="tags">Теги</label>
                        <input type="text" id="tags" name="tags"
                               value="<?= e($tagsRaw) ?>"
                               placeholder="php, форми, валідація">
                        <div class="hint">Необов'язково, через кому</div>
                    </div>

                    <button type="submit" class="btn">Опублікувати</button>
                    <button type="button" class="btn btn-secondary" id="clearDraft">Очистити чернетку</button>
                    <span class="draft-status" id="draftStatus"></span>
                </form>
            </section>
        <?php endif; ?>

        <!-- Список статей  -->
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
                        <td><?= e(formatArticle($article)) ?></td>
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
    </main>

    <?php if (!$success): ?>
    <script>
        // ---------- Крок 5: клієнтська валідація ----------
        const form = document.getElementById('articleForm');
        const titleInput = document.getElementById('title');
        const contentInput = document.getElementById('content');
        const tagsInput = document.getElementById('tags');
        const clientError = document.getElementById('clientError');

        form.addEventListener('submit', (event) => {
            const messages = [];
            const titleLen = titleInput.value.trim().length;
            const contentLen = contentInput.value.trim().length;

            if (titleLen < 5 || titleLen > 100) {
                messages.push('Заголовок має містити від 5 до 100 символів (зараз: ' + titleLen + ').');
            }
            if (contentLen < 20) {
                messages.push('Текст має містити не менше 20 символів (зараз: ' + contentLen + ').');
            }

            if (messages.length > 0) {
                event.preventDefault();
                clientError.textContent = messages.join(' ');
                clientError.style.display = 'block';
            } else {
                clientError.style.display = 'none';
            }
        });

        // ---------- Крок 6: автозбереження чернетки в localStorage ----------
        const DRAFT_KEY = 'blog_article_draft';
        const draftStatus = document.getElementById('draftStatus');

        function saveDraft() {
            const draft = {
                title: titleInput.value,
                content: contentInput.value,
                tags: tagsInput.value
            };
            try {
                localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
                draftStatus.textContent = 'Чернетку збережено ' + new Date().toLocaleTimeString('uk-UA');
            } catch (e) {
                draftStatus.textContent = 'Не вдалося зберегти чернетку';
            }
        }

        function restoreDraft() {
            let draft = null;
            try {
                draft = JSON.parse(localStorage.getItem(DRAFT_KEY));
            } catch (e) {
                draft = null;
            }
            if (!draft) return;

            // Підставляємо лише в порожні поля (після помилки сервера значення вже є)
            if (!titleInput.value)   titleInput.value = draft.title || '';
            if (!contentInput.value) contentInput.value = draft.content || '';
            if (!tagsInput.value)    tagsInput.value = draft.tags || '';

            if (draft.title || draft.content || draft.tags) {
                draftStatus.textContent = 'Відновлено збережену чернетку';
            }
        }

        // Автозбереження при кожному введенні
        [titleInput, contentInput, tagsInput].forEach((el) => {
            el.addEventListener('input', saveDraft);
        });

        document.getElementById('clearDraft').addEventListener('click', () => {
            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
            titleInput.value = '';
            contentInput.value = '';
            tagsInput.value = '';
            draftStatus.textContent = 'Чернетку очищено';
        });

        // Відновлення одразу після завантаження сторінки
        restoreDraft();
    </script>
    <?php endif; ?>
</body>
</html>