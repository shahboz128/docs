# notary-queue — навбати электронии идораи нотариалӣ

## Роҳандозӣ (лаб 13)
1. XAMPP: Apache ва MariaDB-ро ба кор дароред (`php -m | grep pdo_mysql` бояд `pdo_mysql` нишон диҳад).
2. Пойгоҳ:
   ```
   mysql -u root --default-character-set=utf8mb4 < database/schema.sql
   mysql -u root --default-character-set=utf8mb4 notary_queue < database/seed.sql
   mysql -u root --default-character-set=utf8mb4 notary_queue -e "SELECT COUNT(*) FROM tickets;"   # 29
   ```
3. Танзимот: `cp config/config.example.php config/config.php` (парол/корбарро дар config.php нависед; он дар Git нест).
4. Сервер: `php -S localhost:8000 -t public` → http://localhost:8000/tickets

## Сохтор
- `public/index.php` — front controller ва роутҳо; `public/assets` — CSS/JS; `public/static` — саҳифаҳои статикии лаб 10–11
- `src/Core` — Database (PDO), Router, View, Validator, Controller, Format
- `src/Repositories` — TicketRepository, ServiceRepository, UserRepository (тамоми SQL дар ҳамин ҷо)
- `src/Controllers`, `views/`, `database/` (schema, seed, queries), `docs/`

## Эзоҳ
Воридшавӣ ва CSRF-ҳимоя ҳанӯз нест (лаб 14): ҳар кас метавонад талонро нест кунад.


## Лаб 12 — сохтор ва роҳандозӣ
```
composer.json            PSR-4: App\ -> src/
public/index.php         front controller (роутҳо), public/.htaccess
src/Core                 Router, View, Database, Validator, Format
src/Controllers          Controller (асос) + контроллерҳо
src/Repositories         дастрасӣ ба БД (лаб 13)
views/                   layout.php, саҳифаҳо, partials/, errors/
```
```
composer dump-autoload          # ихтиёрӣ: бе vendor/ автозагрузчики запасной кор мекунад
php -S localhost:8000 -t public # http://localhost:8000/
```
XAMPP: `http://localhost/<папка>/public/` (mod_rewrite + AllowOverride All). Ҳисоботи curl: `docs/12-routes.txt`, санҷишҳо: `docs/12-checks.md`.
