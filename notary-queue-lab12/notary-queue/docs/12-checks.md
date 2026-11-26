# Лаб 12 — Composer, front controller, роутер, шаблонҳо

## Ҷадвали талабот (Қисми Б)
| Талабот | Дар лоиҳа | Санҷиш |
|---|---|---|
| Composer, PSR-4 `App\` → `src/` | `composer.json`; `vendor/` дар `.gitignore` | `composer dump-autoload` → «Generated autoload files» |
| Front controller | `public/index.php` + `public/.htaccess`; автозагрузчики запасной; дар index.php на HTML, на маълумот | `php -l public/index.php` |
| ≥ 5 роути GET, ≥ 1 бо `{id}` | 8 GET (`/`, `/tickets`, `/tickets/create`, `/tickets/{id}`, `/tickets/{id}/edit`, `/services`, `/reports/queue`, `/api/tickets`) + 6 POST | `docs/12-routes.txt` |
| 404 / 405 / 404 барои `{id}` | `Router::dispatch` (405 бо `Allow`), `Controller::abort(404)` | `/nope`, `/tickets/abc`, `/tickets/99999` → 404; `POST /tickets` → 405 |
| Шаблонҳо | `views/layout.php`; саҳифаҳо: dashboard, tickets/{index,show,form}, services/index, reports/queue, errors/error (7); порчаҳо: notice, confirm-modal, pagination (3) | — |
| Маълумот | Аз лаб 13 — PDO/репозиторӣ (калидҳо = номи сутунҳои `schema.sql`) | `docs/13-checks.md` |
| Амният | ҳар қимат аз `$this->e()`; ҳамаи суроғаҳо аз `$this->url()`; JS роҳи асосиро аз `<body data-base>` мегирад | санҷиши XSS ва basePath (поён) |
| Ҳисоботи curl | `docs/12-routes.txt` | — |

> Эзоҳ: PDF дар лаб 12 маълумотро дар массивҳо мегузорад ва дар лаб 13 бо SQL иваз мекунад. Дар лоиҳаи мо лаб 13 пештар иҷро шуда буд, бинобар ин контроллерҳо аллакай аз репозиторӣ мехонанд; шаблонҳо ва роутҳо ҳамон аз лаб 12 мебошанд.

## Санҷиши автоматикӣ (`work/e2e12.py`, 67 санҷиш — ҳама гузаштанд)
- ҷадвали роутҳо бо кодҳои 200/404/405 дар ду ҳолат: `php -S` (basePath = «») ва симулатсияи XAMPP (`/notary-queue/public/…`);
- сарлавҳаи `Allow`; слеши охирин `/tickets/` → 200; `?page=2` ба роутинг халал намерасонад;
- саҳифаи 404 дар макети умумӣ; `<script>` дар номи мизоҷ экран мешавад (`&lt;script&gt;`);
- автозагрузчики запасной (бе `vendor/`) кор мекунад;
- XAMPP: css/js/пайвандҳо/redirect бо `/notary-queue/public`;
- `Router::toRegex`, `basePath`, `normalizePath` (аз ҷумла Windows `\`).
Лаб 13 (43 санҷиш + Playwright) баъди рефакторинг низ мегузарад.

**Ҳудуд:** санҷишҳо бо SQLite ва `php -S` гузашта шудаанд. Apache/XAMPP воқеӣ ва MariaDB дар ин муҳит нестанд — `.htaccess` бо симулятор (роутер-скрипт) санҷида шудааст, на бо Apache.

## Ҷавобҳо ба саволҳои ҳимоя
1. **Front controller** — як нуқтаи воридшавӣ (`public/index.php`), ки ҳамаи дархостҳоро қабул мекунад ва ба роутер медиҳад. `DocumentRoot = public/`, то `src/`, `config/`, `database/`, `.git` аз браузер дастнорас бошанд (парол, SQL, код).
2. **PSR-4:** `"App\\": "src/"` → `App\Controllers\TicketController` = `src/Controllers/TicketController.php` (номи файл = номи синф, регистр муҳим). `vendor/autoload.php` ин қоидаро иҷро мекунад; бе vendor — `spl_autoload_register` дар index.php ҳамон қоидаро дорад.
3. **`/tickets/{id}/edit`** → `#^/tickets/(?P<id>\d+)/edit$#`. `/tickets/abc`: `\d+` мувофиқ намеояд → ягон роут не → **404**.
4. **404** — чунин роҳ нест. **405** — роҳ ҳаст, вале усул нодуруст (`POST /tickets`), бо `Allow: GET`. Нишон: `curl -i -X POST localhost:8000/tickets` ва `curl -i localhost:8000/nope`.
5. **`ob_start()`** — шаблон чоп мекунад; буфер онро ҳамчун сатр мегирад, то дар макет гузорем (`$content`). `$content` — HTML-и худамон (аллакай тавассути `e()` гузашта), дубора `e()` кунем, тег-ҳо матн мешаванд. Номи мизоҷ — маълумоти корбар, пас `e()` ҳатмист (XSS).
6. **`basePath()`** — дар XAMPP сайт дар `/notary-queue/public/` аст; `href="/tickets"` ба `localhost/tickets` (на ба лоиҳа) мебарад. `url()` + `basePath()` префиксро худаш илова мекунад.
