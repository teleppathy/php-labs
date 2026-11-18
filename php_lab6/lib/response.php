<?php

declare(strict_types=1);

/**
 * Єдиний формат відповідей API:
 *   успіх:   {"success": true,  "data": ...}
 *   помилка: {"success": false, "error": "..."}
 */

/** Надсилає успішну відповідь і завершує скрипт. */
function sendSuccess(int $code, mixed $data): void
{
    http_response_code($code);
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Надсилає відповідь з помилкою і завершує скрипт. */
function sendError(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Зчитує вхідні дані запиту: JSON-тіло (php://input) або звичайна форма ($_POST).
 * Якщо клієнт заявив JSON, але тіло некоректне — відповідає 400.
 */
function readInput(): array
{
    $raw = (string) file_get_contents('php://input');
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if ($raw !== '' && stripos($contentType, 'application/json') !== false) {
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            sendError(400, 'Некоректний JSON у тілі запиту.');
        }
        return $data;
    }

    return $_POST;
}

/**
 * Перетворює значення параметра id на додатне ціле.
 * Якщо id не передано — повертає null; якщо некоректний — 400.
 */
function parseId(mixed $value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    if (!is_scalar($value) || !ctype_digit((string) $value) || (int) $value <= 0) {
        sendError(400, 'Параметр id має бути додатним цілим числом.');
    }

    return (int) $value;
}
