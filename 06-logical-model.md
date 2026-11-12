# Модели мантиқии додаҳо: tax_queue (Системаи электронии навбати андоз)

> Лоиҳа: системаи электронии навбат дар идораҳои андоз. Андозсупорандагон талон мегиранд (дар киоск, тавассути барномаи мобилӣ ё сомона), пешакӣ вақт месупоранд (бронкунӣ), кормандон дар равзанаҳо онҳоро даъват мекунанд ва хизмат мерасонанд. Роҳбарият омор ва арзёбии хизматрасониро мебинад.

## 1. Ҷадвали ибтидоӣ (Excel-и идораи андоз)

Дар ҳоли ҳозир идораҳо навбатро дастӣ дар Excel пеш мебаранд (додаҳо тахайюлӣ мебошанд). Ин шакли ғайринормалӣ аст.

| Талон | Андозсупоранда | РМА | Телефонҳо | Хизмат | Категорияи хизмат | Идора | Суроғаи идора | Вилоят | Равзана | Корманд | Ҳолат ва вақтҳо |
|---|---|---|---|---|---|---|---|---|---|---|---|
| A-001 | Каримов Баҳтиёр Шарифович | 010234567 | 92 111 22 33, 93 444 55 66 | Қабули ҳисобот (декларатсия) | Ҳисоботдиҳӣ | Идораи андози ноҳияи Сино | Душанбе, к. Сайидов 12 | Душанбе | 3 | Юсупова Н. | сохта 09:12; даъват 09:40; анҷом 09:55 |
| A-002 | Раҳматова Зарина Аъзамовна | 020345678 | 98 700 10 20 | Қабули ҳисобот (декларатсия) | Ҳисоботдиҳӣ | Идораи андози ноҳияи Сино | Душанбе, к. Сайидов 12 | Душанбе | 3 | Юсупова Н. | сохта 09:20; даъват 09:56; ғоиб |
| B-001 | ҶДММ «Хуршед» | 030456789 | 90 555 00 11, 91 555 00 22 | Бақайдгирии андозсупорандаи нав | Бақайдгирӣ | Идораи андози ноҳияи Сино | Душанбе, к. Сайидов 12 | Душанбе | 5 | Одинаев Ф. | сохта 09:31; даъват 10:05; хизмат 10:07; анҷом 10:40 |
| A-001 | Саидов Ҷамшед | 040567890 | 93 222 33 44 | Қабули ҳисобот (декларатсия) | Ҳисоботдиҳӣ | Идораи андози шаҳри Хуҷанд | Хуҷанд, к. Ленин 5 | Вилояти Суғд | 1 | Мирзоев Д. | сохта 08:50; даъват 09:15; анҷом 09:30 |

## 2. Нормализатсия

### 1ШН

Мушкилоти ҷадвали ибтидоӣ:

- «Андозсупоранда» — қимати таркибӣ (насаб + ном + номи падар) ва дар сатри 3 ном аз номи ташкилот иборат аст → `last_name`, `first_name`, `middle_name`, `organization_name` (дар `taxpayers`).
- «Телефонҳо» — якчанд қимат дар як ҳуҷайра («92 111 22 33, 93 444 55 66») → дар `taxpayers.phone` як рақами асосӣ нигоҳ дошта мешавад, рақамҳои иловагӣ дар ҷадвали алоҳида лозим нестанд (барои ҷустуҷӯ ва SMS як рақам кифоя аст).
- «Ҳолат ва вақтҳо» — гурӯҳи такроршавандаи рӯйдодҳо дар як майдон («сохта…; даъват…; анҷом…») → ҷадвали алоҳидаи `ticket_events`: як сатр = як рӯйдод, вақт дар майдони `occurred_at` (навъи DATETIME).
- Вақтҳои асосии талон (сохта, даъват, оғоз, анҷом) ба сутунҳои алоҳидаи `created_at`, `called_at`, `started_at`, `finished_at` (DATETIME) ҷудо карда шуданд.
- «Суроғаи идора» — қимати таркибӣ (шаҳр, кӯча, хона) → дар як майдони `tax_offices.address` нигоҳ дошта мешавад, зеро ҷудо кардан ба шаҳр/кӯча дар ин лоиҳа лозим нест; вилоят бошад — ҷадвали алоҳидаи `regions`.

### 2ШН

Калиди номзади ҷадвали ибтидоӣ — таркибӣ: (идора, санаи навбат, рақами талон). Рақами талон «A-001» дар ҳар идора ва ҳар рӯз аз сари нав оғоз мешавад (масалан, `A-001` ҳам дар Душанбе, ҳам дар Хуҷанд). Вобастагиҳои қисмӣ (атрибут аз як қисми калид вобаста аст):

| Вобастагии қисмӣ | Ҳал |
|---|---|
| идора → суроға, вилоят | ҷадвали `tax_offices` (суроға, `region_id`) |
| (идора, рақами талон) → равзана | равзана ба ҷадвали `windows` бароварда шуд, дар `tickets` танҳо `window_id` мемонад |

Баъди гузариш ба калиди суррогатии `id` дар ҳамаи ҷадвалҳо вобастагиҳои қисмӣ ғайриимкон мешаванд (калид аз як сутун иборат аст). Рақами талон ва идора бо маҳдудияти `UNIQUE (tax_office_id, queue_date, ticket_number)` ҳифз мешаванд.

### 3ШН

| Вобастагии транзитивӣ | Ҳал |
|---|---|
| талон → идора → вилоят | ҷадвалҳои `tax_offices` (`region_id`) ва `regions` |
| талон → идора → роҳбари идора | `tax_offices.head_employee_id` |
| талон → хизмат → категорияи хизмат | ҷадвалҳои `services` (`category_id`) ва `service_categories` |
| талон → андозсупоранда → РМА, телефон, ном | ҷадвали `taxpayers`, дар `tickets` танҳо `taxpayer_id` |
| талон → корманд → вазифа | ҷадвалҳои `employees` (`position_id`) ва `positions` |
| талон → равзана → рақами равзана, идора | ҷадвали `windows` |
| талон → хизмат → давомнокии миёна | `services.avg_duration_min` |

Санҷиш: иваз кардани номи категорияи хизмат (масалан, «Ҳисоботдиҳӣ» → «Ҳисоботи андоз») як сатрро дар `service_categories` тағйир медиҳад, на сатрҳои ҳамаи талонҳои он. Иваз кардани суроғаи идора як сатрро дар `tax_offices` тағйир медиҳад.

**Қиматҳои ҳисобшаванда нигоҳ дошта намешаванд:** вақти интизорӣ (`called_at − created_at`), давомнокии хизмат (`finished_at − started_at`), миёнаи баҳо, шумораи талонҳои рӯз — ҳама ҳангоми дархост бо SQL ҳисоб карда мешаванд.

## 3. Моҳиятҳо

Ҳамагӣ 15 ҷадвал. Номҳо — англисӣ, шакли ҷамъ, snake_case; FK — `<моҳият>_id`.

### regions — вилоятҳо

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| name | VARCHAR(100) | UQ | не | номи вилоят (Душанбе, Суғд, Хатлон, ВМКБ, НТҶ) |
| code | VARCHAR(10) | UQ | не | рамзи кӯтоҳ (масалан, DSH, SUG) |

### tax_offices — идораҳои андоз

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| region_id | INT | FK → regions.id | не | вилояте, ки идора дар он ҷойгир аст |
| name | VARCHAR(150) | | не | номи идора |
| address | VARCHAR(255) | | не | суроға |
| phone | VARCHAR(20) | | ҳа | телефони идора |
| work_start | TIME | | не | оғози рӯзи корӣ; work_end > work_start |
| work_end | TIME | | не | анҷоми рӯзи корӣ |
| head_employee_id | INT | FK → employees.id | ҳа | роҳбари идора (метавонад таъин нашуда бошад) |
| is_active | TINYINT(1) | | не | идора фаъол аст; бо пешфарз 1 |

### positions — вазифаҳо

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| title | VARCHAR(100) | UQ | не | номи вазифа (инспектор, оператор, роҳбар) |
| category | ENUM('management','inspector','operator','technical') | | не | категорияи вазифа |

### employees — кормандони идораҳо

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| tax_office_id | INT | FK → tax_offices.id | не | идораи корӣ |
| position_id | INT | FK → positions.id | не | вазифа |
| last_name | VARCHAR(60) | | не | насаб |
| first_name | VARCHAR(60) | | не | ном |
| middle_name | VARCHAR(60) | | ҳа | номи падар |
| phone | VARCHAR(20) | | ҳа | телефон |
| email | VARCHAR(120) | UQ | ҳа | почтаи электронӣ |
| hire_date | DATE | | не | санаи қабул ба кор |
| status | ENUM('active','on_leave','dismissed') | | не | ҳолати корманд; бо пешфарз active |
| created_at | DATETIME | | не | санаи сабт |
| updated_at | DATETIME | | не | санаи тағйироти охирин |

### users — ҳисобҳои корбарӣ (кормандон ва мудирон)

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| employee_id | INT | FK → employees.id, UQ | ҳа | корманди дахлдор (ҳисоби техникии админ метавонад корманд надошта бошад) |
| username | VARCHAR(50) | UQ | не | логин |
| password_hash | VARCHAR(255) | | не | ҳеши парол (парол дар намуди оддӣ нигоҳ дошта намешавад) |
| role | ENUM('admin','manager','operator','auditor') | | не | нақш дар система |
| is_active | TINYINT(1) | | не | ҳисоб фаъол аст; бо пешфарз 1 |
| last_login_at | DATETIME | | ҳа | вурууди охирин |

### windows — равзанаҳои хизматрасонӣ

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| tax_office_id | INT | FK → tax_offices.id | не | идора |
| number | TINYINT | | не | рақами равзана дар дохили идора |
| status | ENUM('open','break','closed') | | не | ҳолати равзана; бо пешфарз closed |

### service_categories — категорияҳои хизматҳо

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| name | VARCHAR(100) | UQ | не | номи категория (Ҳисоботдиҳӣ, Бақайдгирӣ, Пардохт, Машварат) |
| prefix | CHAR(1) | UQ | не | ҳарфи аввали рақами талон (A, B, C, D) |

### services — хизматҳо

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| category_id | INT | FK → service_categories.id | не | категория |
| name | VARCHAR(200) | | не | номи хизмат |
| description | VARCHAR(500) | | ҳа | тавсифи хизмат |
| avg_duration_min | SMALLINT | | не | давомнокии миёнаи пешбинишуда (дақиқа); > 0 |
| is_active | TINYINT(1) | | не | хизмат фаъол аст; бо пешфарз 1 |

### office_services — хизматҳои дастрас дар идораҳо (ҷадвали пайвасткунанда, M:N)

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| tax_office_id | INT | FK → tax_offices.id | не | идора |
| service_id | INT | FK → services.id | не | хизмат |
| max_daily_tickets | SMALLINT | | ҳа | ҳадди аксари талон дар рӯз (NULL — бе маҳдудият) |
| is_available | TINYINT(1) | | не | хизмат дар ин идора ҳоло дастрас аст; бо пешфарз 1 |

### taxpayers — андозсупорандагон

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| tin | VARCHAR(12) | UQ | не | рақами мушаххаси андозсупоранда (РМА) |
| taxpayer_type | ENUM('individual','sole_proprietor','legal_entity') | | не | навъ: шахси воқеӣ, соҳибкори инфиродӣ, шахси ҳуқуқӣ |
| last_name | VARCHAR(60) | | ҳа | насаб (ҳатмӣ барои шахси воқеӣ ва соҳибкор) |
| first_name | VARCHAR(60) | | ҳа | ном (ҳатмӣ барои шахси воқеӣ ва соҳибкор) |
| middle_name | VARCHAR(60) | | ҳа | номи падар |
| organization_name | VARCHAR(200) | | ҳа | номи ташкилот (ҳатмӣ барои шахси ҳуқуқӣ) |
| phone | VARCHAR(20) | | не | телефон барои огоҳӣ (SMS) |
| email | VARCHAR(120) | | ҳа | почтаи электронӣ |
| created_at | DATETIME | | не | санаи сабт |

### appointments — бронкунии пешакии вақт

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| taxpayer_id | INT | FK → taxpayers.id | не | кӣ вақт гирифтааст |
| tax_office_id | INT | FK → tax_offices.id | не | идора |
| service_id | INT | FK → services.id | не | хизмат |
| appointment_date | DATE | | не | санаи ташриф |
| slot_start | TIME | | не | оғози фосилаи вақт |
| status | ENUM('booked','confirmed','arrived','cancelled','missed') | | не | ҳолат; бо пешфарз booked |
| created_at | DATETIME | | не | санаи бронкунӣ |

### tickets — талонҳои навбат

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| queue_date | DATE | | не | санаи навбат |
| ticket_number | VARCHAR(10) | | не | рақами талон (масалан, A-015); ҳар рӯз дар ҳар идора аз сари нав |
| tax_office_id | INT | FK → tax_offices.id | не | идора |
| service_id | INT | FK → services.id | не | хизмати дархостшуда |
| taxpayer_id | INT | FK → taxpayers.id | ҳа | андозсупоранда (талон дар киоск метавонад беном гирифта шавад) |
| appointment_id | INT | FK → appointments.id, UQ | ҳа | бронкунии пешакӣ (агар талон аз рӯи бронкунӣ бошад) |
| window_id | INT | FK → windows.id | ҳа | равзана (ҳангоми даъват таъин мешавад) |
| employee_id | INT | FK → employees.id | ҳа | корманде, ки хизмат расонидааст |
| source | ENUM('kiosk','mobile','web','reception') | | не | чӣ тавр талон гирифта шудааст |
| priority | ENUM('normal','elderly','disabled','pregnant') | | не | афзалият; бо пешфарз normal |
| status | ENUM('waiting','called','serving','completed','no_show','cancelled') | | не | ҳолат; бо пешфарз waiting |
| created_at | DATETIME | | не | вақти гирифтани талон |
| called_at | DATETIME | | ҳа | вақти даъват |
| started_at | DATETIME | | ҳа | вақти оғози хизмат |
| finished_at | DATETIME | | ҳа | вақти анҷоми хизмат |

### ticket_events — таърихи рӯйдодҳои талон

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| ticket_id | INT | FK → tickets.id | не | талон |
| user_id | INT | FK → users.id | ҳа | кӣ амал кардааст (NULL — амали худкори система ё киоск) |
| event_type | ENUM('created','called','recalled','started','completed','no_show','cancelled','transferred') | | не | навъи рӯйдод |
| occurred_at | DATETIME | | не | вақти рӯйдод |
| note | VARCHAR(255) | | ҳа | эзоҳ |

### service_ratings — арзёбии хизматрасонӣ

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| ticket_id | INT | FK → tickets.id, UQ | не | талоне, ки арзёбӣ мешавад (як талон — як арзёбӣ) |
| rating | TINYINT | | не | баҳо аз 1 то 5 |
| comment | VARCHAR(500) | | ҳа | шарҳ |
| created_at | DATETIME | | не | санаи арзёбӣ |

### notifications — огоҳиҳо ба андозсупорандагон

| Атрибут | Навъ | Калид | NULL | Тавсиф |
|---|---|---|---|---|
| id | INT | PK | не | |
| taxpayer_id | INT | FK → taxpayers.id | не | гиранда |
| ticket_id | INT | FK → tickets.id | ҳа | талони дахлдор |
| channel | ENUM('sms','email','push') | | не | канали фиристодан |
| message | VARCHAR(500) | | не | матни огоҳӣ |
| status | ENUM('queued','sent','failed') | | не | ҳолат; бо пешфарз queued |
| created_at | DATETIME | | не | санаи сохтан |
| sent_at | DATETIME | | ҳа | санаи фиристодан |

## 4. Алоқаҳо

| Алоқа | Навъ | Калиди берунӣ |
|---|---|---|
| вилоят — идораҳо | 1:N | tax_offices.region_id |
| идора — кормандон | 1:N | employees.tax_office_id |
| идора — роҳбар | 1:1, ғайриҳатмӣ | tax_offices.head_employee_id |
| вазифа — кормандон | 1:N | employees.position_id |
| корманд — ҳисоби корбарӣ | 1:1, ғайриҳатмӣ | users.employee_id (UNIQUE) |
| идора — равзанаҳо | 1:N | windows.tax_office_id |
| категория — хизматҳо | 1:N | services.category_id |
| **идора — хизматҳо** | **M:N** (тавассути `office_services`) | office_services.tax_office_id, office_services.service_id |
| идора — талонҳо | 1:N | tickets.tax_office_id |
| хизмат — талонҳо | 1:N | tickets.service_id |
| андозсупоранда — талонҳо | 1:N, ғайриҳатмӣ | tickets.taxpayer_id |
| бронкунӣ — талон | 1:1, ғайриҳатмӣ | tickets.appointment_id (UNIQUE) |
| равзана — талонҳо | 1:N, ғайриҳатмӣ | tickets.window_id |
| корманд — талонҳои хизматрасонидашуда | 1:N, ғайриҳатмӣ | tickets.employee_id |
| андозсупоранда — бронкуниҳо | 1:N | appointments.taxpayer_id |
| идора — бронкуниҳо | 1:N | appointments.tax_office_id |
| хизмат — бронкуниҳо | 1:N | appointments.service_id |
| талон — рӯйдодҳо | 1:N | ticket_events.ticket_id |
| корбар — рӯйдодҳои анҷомдодашуда | 1:N, ғайриҳатмӣ | ticket_events.user_id |
| талон — арзёбӣ | 1:1, ғайриҳатмӣ | service_ratings.ticket_id (UNIQUE) |
| андозсупоранда — огоҳиҳо | 1:N | notifications.taxpayer_id |
| талон — огоҳиҳо | 1:N, ғайриҳатмӣ | notifications.ticket_id |

**Алоқаи M:N.** Як идора якчанд хизмат мерасонад, як хизмат дар якчанд идора дастрас аст. Алоқа тавассути ҷадвали пайвасткунандаи `office_services` ба ду алоқаи 1:N ҷудо шудааст: `tax_offices 1:N office_services` ва `services 1:N office_services`. Ҷуфти (`tax_office_id`, `service_id`) ягона аст. Ҷадвал атрибутҳои худро дорад (`max_daily_tickets`, `is_available`), ки ба алоқа тааллуқ доранд, на ба яке аз ду моҳият.

## 5. Маҳдудиятҳои бутунӣ

### UNIQUE

| Ҷадвал | Сутун(ҳо) | Маънӣ |
|---|---|---|
| regions | name; code | вилояти такрорӣ нест |
| tax_offices | (region_id, name) | дар як вилоят ду идораи ҳамном нест |
| positions | title | вазифаи такрорӣ нест |
| employees | email | як почта — як корманд |
| users | username | логини ягона |
| users | employee_id | як корманд — на зиёда аз як ҳисоб (алоқаи 1:1) |
| windows | (tax_office_id, number) | дар як идора ду равзанаи ҳамрақам нест |
| service_categories | name; prefix | ном ва ҳарфи талон ягона аст |
| services | (category_id, name) | дар як категория хизмати ҳамном нест |
| office_services | (tax_office_id, service_id) | хизмат дар идора як маротиба сабт мешавад (асоси M:N) |
| taxpayers | tin | як РМА — як андозсупоранда |
| appointments | (taxpayer_id, appointment_date, slot_start) | як андозсупоранда ду бронро дар як вақт гирифта наметавонад |
| tickets | (tax_office_id, queue_date, ticket_number) | рақами талон дар идора дар як рӯз ягона аст |
| tickets | appointment_id | як бронкунӣ — на зиёда аз як талон |
| service_ratings | ticket_id | як талон — як арзёбӣ |

*Эзоҳ:* `users.employee_id` ва `tickets.appointment_id` NULL-ро иҷозат медиҳанд ва UNIQUE мебошанд. Дар MySQL ҳангоми UNIQUE якчанд NULL иҷозат аст, пас ҳисобҳои бе корманд (админи техникӣ) ва талонҳои бе бронкунӣ мумкин аст, лекин қимати воқеӣ такрор намешавад. Ин алоқаи «1:1, ғайриҳатмӣ»-ро медиҳад.

### CHECK

| Ҷадвал | Шарт | Маънӣ |
|---|---|---|
| tax_offices | `work_end > work_start` | рӯзи корӣ дуруст аст |
| services | `avg_duration_min > 0` | давомнокӣ мусбат аст |
| service_ratings | `rating BETWEEN 1 AND 5` | баҳо аз 1 то 5 |
| taxpayers | `(taxpayer_type = 'legal_entity' AND organization_name IS NOT NULL) OR (taxpayer_type <> 'legal_entity' AND last_name IS NOT NULL AND first_name IS NOT NULL)` | барои шахси ҳуқуқӣ — номи ташкилот, барои шахси воқеӣ ва соҳибкор — насаб ва ном |
| tickets | `called_at IS NULL OR called_at >= created_at` | даъват пеш аз сохтани талон набуда наметавонад |
| tickets | `started_at IS NULL OR (called_at IS NOT NULL AND started_at >= called_at)` | хизмат пас аз даъват оғоз мешавад |
| tickets | `finished_at IS NULL OR (started_at IS NOT NULL AND finished_at >= started_at)` | хизмат пас аз оғоз анҷом меёбад |

### ON DELETE (барои ҳар FK)

Ҳамаи калидҳо суррогатӣ ҳастанд ва тағйир намеёбанд, бинобар ин `ON UPDATE` барои ҳамаи FK — `RESTRICT` (пешфарз).

| Калиди берунӣ | ON DELETE | Сабаб |
|---|---|---|
| tax_offices.region_id → regions | RESTRICT | вилоятро то даме ки идора дорад, нест кардан мумкин нест |
| tax_offices.head_employee_id → employees | SET NULL | роҳбарро нест кунанд, идора бе роҳбар мемонад |
| employees.tax_office_id → tax_offices | RESTRICT | идораро бо кормандон нест кардан мумкин нест |
| employees.position_id → positions | RESTRICT | вазифаро то даме ки дар он кормандон ҳастанд, нест кардан мумкин нест |
| users.employee_id → employees | RESTRICT | корманд бо ҳисоб нест намешавад; ба ҷои ин `status = 'dismissed'` ва `is_active = 0` гузошта мешавад |
| windows.tax_office_id → tax_offices | RESTRICT | идора бо равзанаҳояш нест намешавад |
| services.category_id → service_categories | RESTRICT | категорияро бо хизматҳо нест кардан мумкин нест |
| office_services.tax_office_id → tax_offices | CASCADE | пайвандҳо бо идора нест мешаванд (маълумоти ёрирасон) |
| office_services.service_id → services | CASCADE | пайвандҳо бо хизмат нест мешаванд |
| appointments.taxpayer_id → taxpayers | RESTRICT | андозсупорандаро бо бронкуниҳояш нест кардан мумкин нест |
| appointments.tax_office_id → tax_offices | RESTRICT | таърихи бронкуниҳо ҳифз мешавад |
| appointments.service_id → services | RESTRICT | таърихи бронкуниҳо ҳифз мешавад; хизмат ғайрифаъол карда мешавад (`is_active = 0`) |
| tickets.tax_office_id → tax_offices | RESTRICT | омори талонҳо гум намешавад |
| tickets.service_id → services | RESTRICT | омори талонҳо гум намешавад |
| tickets.taxpayer_id → taxpayers | SET NULL | талон ҳамчун омор мемонад, маълумоти шахсӣ нест мешавад |
| tickets.appointment_id → appointments | SET NULL | талон бе пайванд мемонад |
| tickets.window_id → windows | SET NULL | равзанаро нест кунанд, талонҳои қаблӣ мемонанд |
| tickets.employee_id → employees | SET NULL | таърихи талонҳо мемонад |
| ticket_events.ticket_id → tickets | CASCADE | таърих бе талон маъно надорад |
| ticket_events.user_id → users | SET NULL | рӯйдод мемонад, муаллиф маълум нест |
| service_ratings.ticket_id → tickets | CASCADE | арзёбӣ бе талон маъно надорад |
| notifications.taxpayer_id → taxpayers | CASCADE | огоҳиҳо бо андозсупоранда нест мешаванд |
| notifications.ticket_id → tickets | SET NULL | огоҳӣ бе пайванд ба талон мемонад |

### Қоидаҳои иловагӣ (дар кори лаб. 7 бо триггер ё амал дар барнома ҳал мешаванд)

- Равзана ва корманди талон бояд ба ҳамон идора тааллуқ дошта бошанд, ки талон (`tickets.window_id`, `tickets.employee_id`).
- Хизмати талон бояд дар идора дастрас бошад (ҷуфти (`tax_office_id`, `service_id`) дар `office_services` бо `is_available = 1`).
- Шумораи талонҳои як рӯз аз `office_services.max_daily_tickets` зиёд нашавад.
- Индексҳои пешниҳодшуда барои тезкории навбат: `tickets (tax_office_id, queue_date, status)`, `appointments (tax_office_id, appointment_date)`, `ticket_events (ticket_id, occurred_at)`.
