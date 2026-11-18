<?php

declare(strict_types=1);

/** Екранування тексту для безпечного виводу в HTML. */
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/** Форматує дату у вигляді дд.мм.рррр. */
function formatDate(string $date): string
{
    return date('d.m.Y', strtotime($date));
}

/** Обрізає текст до заданої довжини й додає «…». */
function truncateText(string $text, int $length = 60): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $length)) . '…';
}

/** Перевіряє, що рядок — коректна дата у форматі рррр-мм-дд. */
function isValidDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);

    return $d !== false && $d->format('Y-m-d') === $date;
}
