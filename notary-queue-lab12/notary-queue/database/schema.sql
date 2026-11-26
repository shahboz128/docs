-- =====================================================================
-- notary-queue: схемаи пойгоҳи додаҳо (schema.sql)
-- Веблоиҳаи навбати электронӣ барои идораи нотариалӣ
-- СУБД: MySQL 8.0+ ё MariaDB 10.6+, InnoDB, utf8mb4_unicode_ci, 3ШН
-- 7 ҷадвал: users, services, service_documents, work_schedules,
--           time_slots, appointments, tickets
-- Файл дубора иҷро шуда метавонад: ҷадвалҳои кӯҳна нест карда мешаванд.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS notary_queue
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE notary_queue;

SET NAMES utf8mb4;

-- Нест кардан бо тартиби баръакси вобастагиҳо (аввал "фарзандон")
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS time_slots;
DROP TABLE IF EXISTS work_schedules;
DROP TABLE IF EXISTS service_documents;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------------------
-- 1. users: мизоҷон, нотариусҳо ва маъмурон (нақш дар майдони role)
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(120) NOT NULL,
  phone         VARCHAR(20)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,                 -- password_hash() дар PHP, ҳеши bcrypt
  role          ENUM('client','notary','admin') NOT NULL DEFAULT 'client',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  failed_logins TINYINT UNSIGNED NOT NULL DEFAULT 0,   -- кӯшишҳои нодурусти воридшавӣ
  locked_until  DATETIME     NULL,                     -- то ин вақт воридшавӣ баста аст
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. services: намудҳои хизматрасонии нотариалӣ
-- ---------------------------------------------------------------------
CREATE TABLE services (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(150) NOT NULL,
  duration_min INT NOT NULL DEFAULT 20,
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  CHECK (duration_min > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. service_documents: ҳуҷҷатҳои ҳатмии ҳар хизматрасонӣ
-- ---------------------------------------------------------------------
CREATE TABLE service_documents (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT NOT NULL,
  title      VARCHAR(200) NOT NULL,
  CONSTRAINT fk_sdoc_service FOREIGN KEY (service_id) REFERENCES services(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. work_schedules: ҷадвали рӯзҳои корӣ (як сатр барои як рӯз)
--    capacity    - шумораи нотариусҳои кор дар ин рӯз (иқтидори як слот)
--    late_grace_min   - дер омадан то чанд дақиқа (пешфарз 15)
--    cancel_before_min - сабтро то чанд дақиқа пеш аз слот бекор кардан мумкин (пешфарз 120)
-- ---------------------------------------------------------------------
CREATE TABLE work_schedules (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  work_date         DATE NOT NULL,
  open_time         TIME NOT NULL,
  close_time        TIME NOT NULL,
  break_start       TIME NULL,
  break_end         TIME NULL,
  slot_minutes      INT  NOT NULL DEFAULT 30,
  capacity          INT  NOT NULL DEFAULT 1,
  late_grace_min    INT  NOT NULL DEFAULT 15,
  cancel_before_min INT  NOT NULL DEFAULT 120,
  is_day_off        TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_schedule_date (work_date),
  CHECK (close_time > open_time),
  CHECK (slot_minutes > 0),
  CHECK (capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. time_slots: бозаҳои вақт барои сабти пешакӣ
--    slot_date ба work_schedules(work_date) пайваст аст (1 : N)
-- ---------------------------------------------------------------------
CREATE TABLE time_slots (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  slot_date  DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time   TIME NOT NULL,
  capacity   INT  NOT NULL DEFAULT 1 CHECK (capacity > 0),
  UNIQUE KEY uq_slot_date_start (slot_date, start_time),
  CHECK (end_time > start_time),
  CONSTRAINT fk_slot_schedule FOREIGN KEY (slot_date) REFERENCES work_schedules(work_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. appointments: сабти пешакии мизоҷ ба слот ва хизматрасонӣ
--    ҳолатҳо: booked - сабтшуда, arrived - омада (талон гирифтааст),
--             cancelled - бекоршуда, no_show - наомада
--    Сабтҳо нест карда намешаванд, танҳо ҳолат иваз мешавад.
-- ---------------------------------------------------------------------
CREATE TABLE appointments (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  service_id   INT NOT NULL,
  slot_id      INT NOT NULL,
  status       ENUM('booked','arrived','cancelled','no_show') NOT NULL DEFAULT 'booked',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cancelled_at DATETIME NULL,
  KEY idx_appt_slot_status (slot_id, status),          -- ҳисоби ҷойҳои холӣ
  KEY idx_appt_user (user_id),
  CONSTRAINT fk_appt_user    FOREIGN KEY (user_id)    REFERENCES users(id),
  CONSTRAINT fk_appt_service FOREIGN KEY (service_id) REFERENCES services(id),
  CONSTRAINT fk_appt_slot    FOREIGN KEY (slot_id)    REFERENCES time_slots(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. tickets: талони электронӣ дар навбати имрӯза
--    appointment_id NULL    - талони бесабт (live)
--    client_id NULL         - талонро маъмур барои мизоҷи бақайдногирифта додааст
--    ticket_type            - афзалиятро муайян мекунад (booked / live)
-- ---------------------------------------------------------------------
CREATE TABLE tickets (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NULL,
  client_id      INT NULL,
  service_id     INT NOT NULL,
  notary_id      INT NULL,
  ticket_date    DATE NOT NULL,
  ticket_no      INT  NOT NULL,
  ticket_type    ENUM('booked','live') NOT NULL,
  status         ENUM('waiting','called','in_service','done','no_show','cancelled')
                 NOT NULL DEFAULT 'waiting',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  called_at      DATETIME NULL,
  started_at     DATETIME NULL,
  finished_at    DATETIME NULL,
  UNIQUE KEY uq_ticket_number (ticket_date, ticket_type, ticket_no),
  UNIQUE KEY uq_ticket_appointment (appointment_id),
  KEY idx_tickets_date_status (ticket_date, status),   -- интихоби талони навбатӣ
  CONSTRAINT fk_ticket_appt    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
  CONSTRAINT fk_ticket_client  FOREIGN KEY (client_id)      REFERENCES users(id),
  CONSTRAINT fk_ticket_service FOREIGN KEY (service_id)     REFERENCES services(id),
  CONSTRAINT fk_ticket_notary  FOREIGN KEY (notary_id)      REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
