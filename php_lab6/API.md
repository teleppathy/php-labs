# API блогу (варіант 3) — контракт

Єдина точка входу: `api.php`. Усі відповіді — JSON (`Content-Type: application/json; charset=utf-8`) в узгодженому форматі:

```json
{ "success": true,  "data": ... }
{ "success": false, "error": "опис помилки" }
```

Ресурс: `resource=articles` — поля `id`, `title`, `content`, `author`, `published_at`.

## Маршрути

| Метод | Запит | Опис | Успіх | Помилки |
|-------|-------|------|-------|---------|
| GET | `api.php?resource=articles` | Список статей (найновіші першими) | 200 | — |
| GET | `api.php?resource=articles&author=Ім'я` | Список статей автора | 200 | — |
| GET | `api.php?resource=articles&id=N` | Одна стаття | 200 | 400 (некоректний id), 404 (немає запису) |
| POST | `api.php?resource=articles` | Створення статті | 201 | 400 (бракує/некоректні поля) |
| POST | `api.php?resource=articles&action=publish&id=N` | Доменна дія: `published_at = NOW()` | 200 | 400 (немає/некоректний id), 404 (немає запису) |
| інший метод | `api.php?resource=articles` | — | — | 405 + заголовок `Allow: GET, POST` |
| будь-який | невідомий `resource` | — | — | 404 |
| POST | невідома `action` | — | — | 400 |

## Параметри створення (POST без `action`)

Дані передаються JSON-тілом (`Content-Type: application/json`) або звичайною формою (`application/x-www-form-urlencoded`).

| Поле | Обов'язкове | Правила |
|------|-------------|---------|
| `title` | так | до 255 символів |
| `content` | так | непорожній текст |
| `author` | так | до 100 символів |
| `published_at` | ні | дата `рррр-мм-дд`; за замовчуванням — сьогодні |

## Приклади відповідей

`GET api.php?resource=articles&id=1` → `200`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Вступ до PHP для початківців",
    "content": "У цій статті розглядаємо базовий синтаксис PHP...",
    "author": "Олена Коваленко",
    "published_at": "2026-09-05"
  }
}
```

`GET api.php?resource=articles&id=999999` → `404`

```json
{ "success": false, "error": "Статтю з id=999999 не знайдено." }
```

`POST api.php?resource=articles` (порожній `author`) → `400`

```json
{ "success": false, "error": "Відсутні обов'язкові поля: author." }
```

`PUT api.php?resource=articles` → `405`

```json
{ "success": false, "error": "Метод PUT не підтримується для ресурсу articles." }
```
