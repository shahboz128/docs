-- =====================================================================
-- notary-queue: дархостҳои асосӣ (queries.sql)
-- Пас аз schema.sql ва seed.sql иҷро кунед. Дархостҳоро яке-яке иҷро кунед.
-- Барои намоиш "вақти ҳозира"-ро дастӣ мемонем (дар истеҳсолот NOW()):
--     SET @now = TIMESTAMP(CURDATE(), '10:20:00');
-- Дар барнома (PHP/PDO) ба ҷои @-тағйирёбандаҳо параметрҳои "?" истифода мешаванд.
-- =====================================================================

USE notary_queue;
SET NAMES utf8mb4;
SET @now = TIMESTAMP(CURDATE(), '10:20:00');

-- ---------------------------------------------------------------------
-- Q1. Слотҳои холии рӯз (ТФ-04): GET api.php?action=slots&date=...
--     Ҷои ишғолшуда = сабтҳои "booked" ва "arrived" (мизоҷи омада ҷойро озод намекунад).
--     Слотҳои гузашта нишон дода намешаванд.
--     Натиҷа дар сценарии seed (@now = 10:20): 10:30 нест (2 аз 2 банд аст),
--     09:00-10:00 нест (гузашта), 11:00 ҳаст (сабти бекоршуда ҷойро озод кардааст).
-- ---------------------------------------------------------------------
SELECT s.id, s.start_time, s.end_time,
       s.capacity - COUNT(a.id) AS free_places
FROM time_slots s
LEFT JOIN appointments a
       ON a.slot_id = s.id AND a.status IN ('booked', 'arrived')
WHERE s.slot_date = DATE(@now)
  AND TIMESTAMP(s.slot_date, s.start_time) > @now
GROUP BY s.id, s.start_time, s.end_time, s.capacity
HAVING free_places > 0
ORDER BY s.start_time;

-- ---------------------------------------------------------------------
-- Q2. Сабти пешакӣ дар як транзаксия (ТФ-04, ТҒФ-01)
--     Қулфи сатри слот (FOR UPDATE) ду нафарро ҳамзамон ба як ҷои охирин намегузорад:
--     транзаксияи дуюм интизор мешавад ва баъди COMMIT шумораи нав мебинад.
--     Шарти "ҷой ҳаст" дар худи INSERT аст, бинобар ин санҷиш ва сабт ҷудо нестанд.
--     inserted = 1 -> сабт шуд (200); inserted = 0 -> слот банд аст (409).
--     Мисол: мизоҷи 4 ба слоти 11:30 (id=6) барои хизмати 1.
-- ---------------------------------------------------------------------
SET @user_id = 4, @service_id = 1, @slot_id = 6;

START TRANSACTION;
SELECT id FROM time_slots WHERE id = @slot_id FOR UPDATE;
INSERT INTO appointments (user_id, service_id, slot_id, status)
SELECT @user_id, @service_id, s.id, 'booked'
  FROM time_slots s
 WHERE s.id = @slot_id
   AND (SELECT COUNT(*) FROM appointments a
         WHERE a.slot_id = s.id AND a.status IN ('booked', 'arrived')) < s.capacity;
SELECT ROW_COUNT() AS inserted;
COMMIT;

-- Санҷиши 409: слоти 10:30 (id=4) пур аст (2 аз 2) -> inserted = 0
SET @user_id = 9, @service_id = 1, @slot_id = 4;

START TRANSACTION;
SELECT id FROM time_slots WHERE id = @slot_id FOR UPDATE;
INSERT INTO appointments (user_id, service_id, slot_id, status)
SELECT @user_id, @service_id, s.id, 'booked'
  FROM time_slots s
 WHERE s.id = @slot_id
   AND (SELECT COUNT(*) FROM appointments a
         WHERE a.slot_id = s.id AND a.status IN ('booked', 'arrived')) < s.capacity;
SELECT ROW_COUNT() AS inserted;
ROLLBACK;

-- ---------------------------------------------------------------------
-- Q3. Интихоби талони навбатӣ - навбати омехта (ТФ-07), Расми 6
--     Гурӯҳ 0: талони booked, ки вақти слоташ расидааст (бо тартиби вақти слот)
--     Гурӯҳ 1: талонҳои live (бо тартиби сохта шудан); аз ҷумла мизоҷи дер омада,
--              ки ҳангоми гирифтани талон ба live табдил дода шудааст
--     Гурӯҳ 2: талони booked, ки пеш аз вақт омадааст (танҳо агар дигар касе набошад)
--     Натиҷа дар seed (@now = 10:20): талони id=6 (B-2, слоти 10:00).
--     Бо @now = 10:35 аввал id=6, баъд id=7 (слоти 10:30), баъд live-ҳо.
-- ---------------------------------------------------------------------
SELECT t.id, t.ticket_type, t.ticket_no,
       CASE WHEN t.ticket_type = 'booked'
             AND TIMESTAMP(s.slot_date, s.start_time) <= @now THEN 0
            WHEN t.ticket_type = 'live' THEN 1
            ELSE 2 END AS grp
FROM tickets t
LEFT JOIN appointments a ON a.id = t.appointment_id
LEFT JOIN time_slots   s ON s.id = a.slot_id
WHERE t.ticket_date = DATE(@now) AND t.status = 'waiting'
ORDER BY
  CASE WHEN t.ticket_type = 'booked'
        AND TIMESTAMP(s.slot_date, s.start_time) <= @now THEN 0
       WHEN t.ticket_type = 'live' THEN 1
       ELSE 2 END,
  CASE WHEN t.ticket_type = 'booked' THEN TIMESTAMP(s.slot_date, s.start_time) END,
  t.created_at
LIMIT 1
FOR UPDATE SKIP LOCKED;

-- Даъват (дар барнома дар ҳамон транзаксия бо Q3). Мисол: нотариус 2 талони 6-ро мегирад.
SET @ticket_id = 6, @notary_id = 2;
UPDATE tickets
   SET status = 'called', notary_id = @notary_id, called_at = @now
 WHERE id = @ticket_id AND status = 'waiting';
SELECT ROW_COUNT() AS called;   -- 1 = даъват шуд, 0 = талон аллакай аз ҷониби дигар гирифта шудааст

-- ---------------------------------------------------------------------
-- Q4. Омори рӯзона (ТФ-14): қабулшудагон, вақти миёнаи интизорӣ ва қабул (дақиқа)
--     Натиҷа дар seed: хизмати 1 -> 1 нафар, интизорӣ 7, қабул 22;
--                      хизмати 2 -> 1 нафар, интизорӣ 15, қабул 9.
-- ---------------------------------------------------------------------
SELECT t.service_id, sv.name AS service_name,
       COUNT(*) AS served,
       ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.called_at)), 1)   AS avg_wait_min,
       ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.started_at, t.finished_at)), 1) AS avg_service_min
FROM tickets t
JOIN services sv ON sv.id = t.service_id
WHERE t.ticket_date = DATE(@now) AND t.status = 'done'
GROUP BY t.service_id, sv.name
ORDER BY t.service_id;

-- Шумораи талонҳои имрӯза аз рӯи ҳолат (ғоибон ҳам ҳисоб мешаванд)
SELECT status, COUNT(*) AS cnt
FROM tickets
WHERE ticket_date = DATE(@now)
GROUP BY status
ORDER BY status;

-- ---------------------------------------------------------------------
-- Q5. Таърихи қабулҳои мизоҷ (ТФ-13): барои мизоҷи 4 (дар seed 2 талон)
--     Нотариус: ... WHERE t.notary_id = ?;  маъмур: бе шарт.
-- ---------------------------------------------------------------------
SELECT t.ticket_date,
       CONCAT(CASE WHEN t.ticket_type = 'booked' THEN 'B' ELSE 'L' END,
              '-', LPAD(t.ticket_no, 3, '0')) AS ticket_label,
       sv.name AS service_name, t.status,
       t.created_at, t.called_at, t.finished_at
FROM tickets t
JOIN services sv ON sv.id = t.service_id
WHERE t.client_id = 4
ORDER BY t.created_at DESC;

-- ---------------------------------------------------------------------
-- Q6. Лавҳаи толор (ТФ-09): ҳолати лавҳа БЕ маълумоти шахсӣ (танҳо рақам ва ҳолат)
-- ---------------------------------------------------------------------
SELECT CONCAT(CASE WHEN t.ticket_type = 'booked' THEN 'B' ELSE 'L' END,
              '-', LPAD(t.ticket_no, 3, '0')) AS ticket_label,
       t.status, t.called_at
FROM tickets t
WHERE t.ticket_date = DATE(@now) AND t.status IN ('called', 'in_service', 'waiting')
ORDER BY (t.status = 'waiting'), t.called_at DESC, t.created_at;

-- ---------------------------------------------------------------------
-- Q7. Санҷиши якпорчагӣ (меъёри қабули ТҒФ-01): бояд 0 сатр баргардонад
--     (a) слоте, ки аз иқтидор зиёд ишғол шудааст;
--     (b) рақами такрории талон дар як рӯз ва намуд.
-- ---------------------------------------------------------------------
SELECT s.id AS slot_id, s.capacity, COUNT(a.id) AS busy
FROM time_slots s
JOIN appointments a ON a.slot_id = s.id AND a.status IN ('booked', 'arrived')
GROUP BY s.id, s.capacity
HAVING COUNT(a.id) > s.capacity;

SELECT ticket_date, ticket_type, ticket_no, COUNT(*) AS dup
FROM tickets
GROUP BY ticket_date, ticket_type, ticket_no
HAVING COUNT(*) > 1;
