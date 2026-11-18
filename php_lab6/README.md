# Практична робота №6. Розробка власного прикладного програмного інтерфейсу (API)

**Варіант 3 — Блог/CMS для статей.**

## Про проєкт

Простий прикладний програмний інтерфейс (API) для статей блогу, написаний на PHP без фреймворків. Усі запити надходять в єдину точку входу `api.php`, яка за HTTP-методом і параметрами URL визначає маршрут, виконує запит до бази `practicum4` (таблиця `articles` з практичної роботи №4) і повертає відповідь у форматі JSON з коректним кодом статусу. Для зручної перевірки додано сторінку-клієнт `index.html`, що показує код статусу й тіло кожної відповіді. Дизайн перевикористано з попередніх практичних робіт (`style.css`).

## Основний функціонал

- **Єдина точка входу:** `api.php` одразу встановлює `header('Content-Type: application/json')`, зчитує `$_SERVER['REQUEST_METHOD']` та `$_GET['resource']` і маршрутизує запити через `if/elseif`.
- **Контракт API:** повний перелік маршрутів, параметрів і прикладів відповідей описано в `API.md` та в коментарі на початку `api.php`.
- **GET — список і одиничний запис:** `GET api.php?resource=articles` повертає список (`200`), `GET ...&id=N` повертає одну статтю або `404`, якщо запису немає; додатково підтримується фільтр `&author=`.
- **POST — створення:** `POST api.php?resource=articles` приймає дані з JSON-тіла (`php://input`) або `$_POST`, перевіряє обов'язкові поля `title`, `content`, `author` (`400` із переліком відсутніх полів), виконує підготовлений `INSERT` і повертає `201` із даними створеної статті.
- **Доменна дія `publish`:** `POST api.php?resource=articles&action=publish&id=N` встановлює `published_at = NOW()` і повертає оновлену статтю (`200`; `400` без id; `404`, якщо статті немає).
- **Некоректні запити:** непідтримуваний метод → `405` із заголовком `Allow`; невідомий ресурс → `404`; некоректний `id`, дата чи невідома `action` → `400` з описом, яке саме поле хибне; помилка БД → `500`.
- **Узгоджений формат відповіді:** `{"success": true, "data": ...}` або `{"success": false, "error": "..."}` — для всіх маршрутів однаково (функції `sendSuccess()` / `sendError()` у `lib/response.php`).
- **Безпека:** усі SQL-запити підготовлені (захист від SQL-ін'єкцій); клієнтська сторінка екранує дані перед вставкою в `innerHTML` (захист від XSS).
- **Клас-репозиторій:** робота з таблицею винесена в `ArticleRepository` (продовження ООП-підходу з попередніх практикумів).

## Структура проєкту

```
practicum06/
├── classes/
│   └── ArticleRepository.php  # запити до таблиці articles (PDO)
├── lib/
│   ├── functions.php          # допоміжні функції (isValidDate() та ін.)
│   └── response.php           # sendSuccess(), sendError(), readInput(), parseId()
├── api.php                    # єдина точка входу API: маршрутизація й обробники
├── API.md                     # опис контракту API
├── db.php                     # підключення до БД (PDO)
├── schema.sql                 # створення БД practicum4, таблиці articles і тестових даних
├── index.html                 # клієнт для перевірки API
├── script.js                  # fetch-запити клієнта, показ статусу й JSON-відповіді
├── style.css                  # оформлення вебсторінки
└── README.md                  # звіт
```

## Підготовка бази даних

Якщо базу `practicum4` з таблицею `articles` уже створено в практичній роботі №4 або №5, цей крок можна пропустити. Інакше:

1. Запустити модуль **MySQL** (у XAMPP Control Panel — **Start** навпроти MySQL).
2. Відкрити phpMyAdmin (`http://localhost/phpmyadmin`), перейти на вкладку **Імпорт**, вибрати файл `schema.sql` і натиснути **Вперед**.
3. Переконатися, що створено базу `practicum4` з таблицею `articles`.
4. За потреби змінити ім'я користувача й пароль у файлі `db.php` (за замовчуванням `root` без пароля).

## Як запустити локально

### Спосіб 1. Через вбудований сервер PHP

1. Розпакувати архів і відкрити папку `practicum06` у VS Code.
2. Запустити MySQL (наприклад, через XAMPP) і переконатися, що база створена та розширення `pdo_mysql` увімкнено (`php -m | findstr pdo` у Windows, `php -m | grep pdo` у Linux/macOS).
3. У терміналі перейти до папки проєкту:

```
cd practicum06
```

4. Запустити локальний сервер PHP:

```
php -S localhost:8000
```

5. Відкрити у браузері клієнт для перевірки або сам API:

```
http://localhost:8000/index.html
http://localhost:8000/api.php?resource=articles
```

### Спосіб 2. Через XAMPP (Apache)

1. Скопіювати папку проєкту в каталог `htdocs` у папці XAMPP (наприклад, `<шлях до XAMPP>\htdocs\practicum06`).
2. Запустити `xampp-control.exe` і натиснути **Start** навпроти модулів **Apache** та **MySQL**.
3. За потреби імпортувати `schema.sql` через phpMyAdmin (див. розділ «Підготовка бази даних»).
4. Відкрити у браузері:

```
http://localhost/practicum06/
http://localhost/practicum06/api.php?resource=articles
```

## Перевірка API вручну

Для прикладів використано адресу вбудованого сервера (`localhost:8000`); для XAMPP замініть її на `localhost/practicum06`. Команди `curl -i` показують код статусу й заголовки (у Windows використовуйте `curl.exe` та лапки навколо URL, бо символ `&` має спеціальне значення в командному рядку).

| Сценарій | Команда | Очікуваний код |
|----------|---------|----------------|
| Список статей | `curl -i "http://localhost:8000/api.php?resource=articles"` | 200 |
| Одна стаття | `curl -i "http://localhost:8000/api.php?resource=articles&id=1"` | 200 |
| Немає запису | `curl -i "http://localhost:8000/api.php?resource=articles&id=999999"` | 404 |
| Некоректний id | `curl -i "http://localhost:8000/api.php?resource=articles&id=abc"` | 400 |
| Створення | `curl -i -X POST "http://localhost:8000/api.php?resource=articles" -d "title=Нова стаття&content=Текст статті&author=Ірина Бондар"` | 201 |
| Бракує поля | `curl -i -X POST "http://localhost:8000/api.php?resource=articles" -d "title=Без автора&content=Текст"` | 400 |
| Дія publish | `curl -i -X POST "http://localhost:8000/api.php?resource=articles&action=publish&id=1"` | 200 |
| Publish без id | `curl -i -X POST "http://localhost:8000/api.php?resource=articles&action=publish"` | 400 |
| Publish, немає запису | `curl -i -X POST "http://localhost:8000/api.php?resource=articles&action=publish&id=999999"` | 404 |
| Невідома дія | `curl -i -X POST "http://localhost:8000/api.php?resource=articles&action=foo"` | 400 |
| Непідтримуваний метод | `curl -i -X DELETE "http://localhost:8000/api.php?resource=articles&id=1"` | 405 |
| Невідомий ресурс | `curl -i "http://localhost:8000/api.php?resource=books"` | 404 |

GET-запити можна перевіряти й просто в адресному рядку браузера, а POST — у Postman/Insomnia або через клієнтську сторінку `index.html`: у ній є форма створення, кнопки «Опублікувати» біля кожної статті, отримання статті за ID та блок «Відповідь API» з кодом статусу й JSON.

## Висновок

У ході роботи спроєктовано й реалізовано власний API: єдину точку входу `api.php` з маршрутизацією за методом і параметрами URL, узгоджений JSON-формат відповідей, коректні коди статусу (200, 201, 400, 404, 405, 500) та доменну дію `publish`. Контракт задокументовано в `API.md`, а роботу всіх маршрутів можна перевірити браузером, `curl` або Postman.
