<?php

declare(strict_types=1);

/**
 * Перетворює заголовок на slug для URL (з транслітерацією української).
 */
function makeSlug(string $title): string
{
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd',
        'е' => 'e', 'є' => 'ie', 'ж' => 'zh', 'з' => 'z', 'и' => 'y', 'і' => 'i',
        'ї' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ь' => '', 'ю' => 'iu', 'я' => 'ia', '’' => '', "'" => '',
    ];

    $slug = strtr(mb_strtolower($title), $map);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    return trim($slug, '-');
}

/**
 * Обрізає текст до заданої довжини й додає «…».
 */
function truncateText(string $text, int $length = 60): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $length)) . '…';
}

/**
 * Форматує дату у вигляді дд.мм.рррр.
 */
function formatDate(string $date): string
{
    return date('d.m.Y', strtotime($date));
}
