-- =====================================================================
-- notary-queue: маълумоти санҷишӣ (seed.sql)
-- Пас аз schema.sql иҷро кунед:  mysql -u root -p < database/schema.sql
--                                mysql -u root -p < database/seed.sql
-- Ҳамаи санаҳо нисбат ба CURDATE() ҳисоб мешаванд, бинобар ин маълумот
-- ҳамеша барои "имрӯз" дуруст аст. Барои ҳамаи корбарони санҷишӣ парол:
--     Secret123!
-- Логин = рақами телефон (ниг. ҷадвали users).
-- Ид-ҳо ошкоро гузошта шудаанд, бинобар ин файлро танҳо баъди schema.sql
-- (пойгоҳи холӣ) иҷро кунед.
-- Сценарийи "навбати омехта" (имрӯз): вақти ҳисобкардашуда 10:20
--   SET @now = TIMESTAMP(CURDATE(), '10:20:00');  -- ниг. queries.sql
-- =====================================================================

USE notary_queue;
SET NAMES utf8mb4;

-- ---- 1. users: 1 маъмур, 2 нотариус, 6 мизоҷ (id 1..9)
INSERT INTO users (id, full_name, phone, password_hash, role) VALUES
  (1, 'Маъмури система (санҷишӣ)', '+992900000001', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'admin'),
  (2, 'Нотариус 1 (санҷишӣ)', '+992900000002', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'notary'),
  (3, 'Нотариус 2 (санҷишӣ)', '+992900000003', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'notary'),
  (4, 'Мизоҷи санҷишӣ 1', '+992900000011', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'client'),
  (5, 'Мизоҷи санҷишӣ 2', '+992900000012', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'client'),
  (6, 'Мизоҷи санҷишӣ 3', '+992900000013', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'client'),
  (7, 'Мизоҷи санҷишӣ 4', '+992900000014', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'client'),
  (8, 'Мизоҷи санҷишӣ 5', '+992900000015', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'client'),
  (9, 'Мизоҷи санҷишӣ 6', '+992900000016', '$2y$10$EWAHRjeSvCcJjbogfO3R4eVLVw8ysyJd1FkSLppLDWfHZv88QzNnG', 'client');

-- ---- 2. services: 3 намуди хизматрасонӣ (давомнокӣ ≤ андозаи слот 30 дақ.)
INSERT INTO services (id, name, duration_min, is_active) VALUES
  (1, 'Ваколатнома', 25, 1),
  (2, 'Тасдиқи нусхаи ҳуҷҷат', 10, 1),
  (3, 'Шартнома', 30, 1);

-- ---- 3. service_documents: рӯйхати НАМУНАВИИ ҳуҷҷатҳои ҳатмӣ (идора онро тавассути панели маъмур танзим мекунад)
INSERT INTO service_documents (id, service_id, title) VALUES
  (1, 1, 'Шиносномаи шахси ваколатдиҳанда (асл)'),
  (2, 1, 'Маълумоти шахси ваколатдоршуда (ному насаб, рақами шиноснома)'),
  (3, 1, 'Ҳуҷҷати моликият ё санади дахлдор (агар ваколатнома ба молу мулк тааллуқ дошта бошад)'),
  (4, 2, 'Ҳуҷҷати аслӣ, ки нусхаи он тасдиқ карда мешавад'),
  (5, 2, 'Нусхаи ҳуҷҷат'),
  (6, 2, 'Шиносномаи довталаб (асл)'),
  (7, 3, 'Шиносномаи ҳарду тараф (асл)'),
  (8, 3, 'Ҳуҷҷатҳои моликияти объекти шартнома'),
  (9, 3, 'Матни лоиҳаи шартнома (агар аллакай тайёр бошад)');

-- ---- 4. work_schedules: имрӯз, пагоҳ, пасфардо - рӯзи корӣ; рӯзи чорум - рӯзи истироҳат
INSERT INTO work_schedules
  (id, work_date, open_time, close_time, break_start, break_end, slot_minutes, capacity, late_grace_min, cancel_before_min, is_day_off) VALUES
  (1, CURDATE(), '09:00:00', '17:00:00', '13:00:00', '14:00:00', 30, 2, 15, 120, 0),
  (2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '17:00:00', '13:00:00', '14:00:00', 30, 2, 15, 120, 0),
  (3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '17:00:00', '13:00:00', '14:00:00', 30, 2, 15, 120, 0),
  (4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '17:00:00', NULL, NULL, 30, 2, 15, 120, 1);

-- ---- 5. time_slots: 14 слот дар рӯз (09:00-13:00 ва 14:00-17:00), иқтидор 2 нотариус; id: имрӯз 1-14, пагоҳ 15-28, пасфардо 29-42
INSERT INTO time_slots (id, slot_date, start_time, end_time, capacity) VALUES
  (1, CURDATE(), '09:00:00', '09:30:00', 2),
  (2, CURDATE(), '09:30:00', '10:00:00', 2),
  (3, CURDATE(), '10:00:00', '10:30:00', 2),
  (4, CURDATE(), '10:30:00', '11:00:00', 2),
  (5, CURDATE(), '11:00:00', '11:30:00', 2),
  (6, CURDATE(), '11:30:00', '12:00:00', 2),
  (7, CURDATE(), '12:00:00', '12:30:00', 2),
  (8, CURDATE(), '12:30:00', '13:00:00', 2),
  (9, CURDATE(), '14:00:00', '14:30:00', 2),
  (10, CURDATE(), '14:30:00', '15:00:00', 2),
  (11, CURDATE(), '15:00:00', '15:30:00', 2),
  (12, CURDATE(), '15:30:00', '16:00:00', 2),
  (13, CURDATE(), '16:00:00', '16:30:00', 2),
  (14, CURDATE(), '16:30:00', '17:00:00', 2),
  (15, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '09:30:00', 2),
  (16, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:30:00', '10:00:00', 2),
  (17, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '10:30:00', 2),
  (18, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:30:00', '11:00:00', 2),
  (19, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '11:30:00', 2),
  (20, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:30:00', '12:00:00', 2),
  (21, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', '12:30:00', 2),
  (22, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:30:00', '13:00:00', 2),
  (23, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '14:30:00', 2),
  (24, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:30:00', '15:00:00', 2),
  (25, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '15:30:00', 2),
  (26, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:30:00', '16:00:00', 2),
  (27, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '16:30:00', 2),
  (28, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:30:00', '17:00:00', 2),
  (29, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '09:30:00', 2),
  (30, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:30:00', '10:00:00', 2),
  (31, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '10:30:00', 2),
  (32, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:30:00', '11:00:00', 2),
  (33, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '11:30:00', 2),
  (34, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:30:00', '12:00:00', 2),
  (35, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '12:00:00', '12:30:00', 2),
  (36, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '12:30:00', '13:00:00', 2),
  (37, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '14:30:00', 2),
  (38, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:30:00', '15:00:00', 2),
  (39, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '15:30:00', 2),
  (40, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:30:00', '16:00:00', 2),
  (41, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '16:30:00', 2),
  (42, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:30:00', '17:00:00', 2);

-- ---- 6. appointments: ҳамаи ҳолатҳо (booked, arrived, cancelled, no_show)
INSERT INTO appointments (id, user_id, service_id, slot_id, status, created_at, cancelled_at) VALUES
  (1, 4, 1, 1, 'arrived', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), NULL),
  (2, 5, 3, 2, 'arrived', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), NULL),
  (3, 6, 2, 3, 'arrived', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), NULL),
  (4, 7, 2, 4, 'arrived', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), NULL),
  (5, 8, 1, 4, 'booked', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), NULL),
  (6, 9, 1, 5, 'cancelled', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), TIMESTAMP(CURDATE(), '08:00:00')),
  (7, 9, 3, 2, 'no_show', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), NULL),
  (8, 4, 1, 15, 'booked', TIMESTAMP(CURDATE(), '08:30:00'), NULL),
  (9, 5, 2, 17, 'booked', TIMESTAMP(CURDATE(), '08:40:00'), NULL);

-- ---- 7. tickets: имрӯз 7 талон (аз ҷумла 1 талони "дер омада" -> live бо appointment_id), дирӯз 2 талон + 21 талони таърихӣ (рӯзҳои -2..-5), ҳамагӣ 30
INSERT INTO tickets (id, appointment_id, client_id, service_id, notary_id, ticket_date, ticket_no, ticket_type, status, created_at, called_at, started_at, finished_at) VALUES
  (1, 1, 4, 1, 2, CURDATE(), 1, 'booked', 'done', TIMESTAMP(CURDATE(), '08:55:00'), TIMESTAMP(CURDATE(), '09:02:00'), TIMESTAMP(CURDATE(), '09:03:00'), TIMESTAMP(CURDATE(), '09:25:00')),
  (2, NULL, NULL, 2, 2, CURDATE(), 1, 'live', 'done', TIMESTAMP(CURDATE(), '09:10:00'), TIMESTAMP(CURDATE(), '09:25:00'), TIMESTAMP(CURDATE(), '09:26:00'), TIMESTAMP(CURDATE(), '09:35:00')),
  (3, NULL, NULL, 3, 3, CURDATE(), 2, 'live', 'no_show', TIMESTAMP(CURDATE(), '09:40:00'), TIMESTAMP(CURDATE(), '09:50:00'), NULL, NULL),
  (4, 2, 5, 3, NULL, CURDATE(), 3, 'live', 'waiting', TIMESTAMP(CURDATE(), '10:00:00'), NULL, NULL, NULL),
  (5, NULL, NULL, 1, NULL, CURDATE(), 4, 'live', 'waiting', TIMESTAMP(CURDATE(), '10:05:00'), NULL, NULL, NULL),
  (6, 3, 6, 2, NULL, CURDATE(), 2, 'booked', 'waiting', TIMESTAMP(CURDATE(), '09:55:00'), NULL, NULL, NULL),
  (7, 4, 7, 2, NULL, CURDATE(), 3, 'booked', 'waiting', TIMESTAMP(CURDATE(), '10:12:00'), NULL, NULL, NULL),
  (8, NULL, 4, 1, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:20:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:21:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:45:00')),
  (9, NULL, 5, 3, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:15:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:16:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:50:00')),
  (10, NULL, NULL, 2, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '09:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:16:00')),
  (11, NULL, 5, 3, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '11:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '11:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '11:36:00')),
  (12, NULL, NULL, 1, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 3, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '11:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '12:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '12:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '12:31:00')),
  (13, NULL, 6, 2, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 4, 'live', 'no_show', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '12:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '13:10:00'), NULL, NULL),
  (14, NULL, 7, 3, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 5, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '13:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '14:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '14:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '14:36:00')),
  (15, NULL, NULL, 1, NULL, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, 'live', 'cancelled', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:50:00'), NULL, NULL, NULL),
  (16, NULL, 8, 2, 2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 2, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '10:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '11:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '11:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '11:16:00')),
  (17, NULL, 9, 3, 3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 3, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '11:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '12:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '12:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '12:36:00')),
  (18, NULL, NULL, 1, 2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 4, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '12:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '13:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '13:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '13:31:00')),
  (19, NULL, 5, 2, 3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 5, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '13:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '14:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '14:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '14:16:00')),
  (20, NULL, NULL, 3, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 1, 'live', 'no_show', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '09:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '10:10:00'), NULL, NULL),
  (21, NULL, 6, 1, 3, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 2, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '10:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '11:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '11:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '11:31:00')),
  (22, NULL, 7, 2, NULL, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 3, 'live', 'cancelled', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '11:50:00'), NULL, NULL, NULL),
  (23, NULL, NULL, 3, 3, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 4, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '12:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '13:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '13:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '13:36:00')),
  (24, NULL, 8, 1, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 5, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '13:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '14:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '14:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '14:31:00')),
  (25, NULL, 9, 2, 3, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 1, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '09:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '10:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '10:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '10:16:00')),
  (26, NULL, NULL, 3, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 2, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '10:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '11:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '11:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '11:36:00')),
  (27, NULL, 5, 1, 3, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 3, 'live', 'no_show', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '11:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '12:10:00'), NULL, NULL),
  (28, NULL, NULL, 2, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 4, 'live', 'done', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '12:50:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '13:05:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '13:06:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '13:16:00')),
  (29, NULL, 6, 3, NULL, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 5, 'live', 'cancelled', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '13:50:00'), NULL, NULL, NULL);

-- Анҷом. Санҷиш: SELECT COUNT(*) FROM tickets;  -> 29
