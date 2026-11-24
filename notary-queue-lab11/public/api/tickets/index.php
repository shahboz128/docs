<?php
// public/api/tickets/index.php — ВАҚТИНА заглушкаи API барои рӯзи 11.
// Дар лаб 14 масири /api/tickets дар front controller пайдо мешавад, ин папкаро нест кунед.
// Шакли ҷавоб (id, ticket_label, service, ticket_type, client, created_at, status) — ШАРТНОМА:
// API-и воқеӣ ҳамин майдонҳоро бармегардонад, app.js тағйир намеёбад.
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$today = date('Y-m-d');
$tickets = [
    ['id' => 1, 'ticket_label' => 'B-001', 'service' => 'Ваколатнома', 'ticket_type' => 'booked',
     'client' => 'Мизоҷи санҷишӣ 1', 'created_at' => "$today 08:55:00", 'status' => 'done'],
    ['id' => 2, 'ticket_label' => 'L-001', 'service' => 'Тасдиқи нусхаи ҳуҷҷат', 'ticket_type' => 'live',
     'client' => 'Рахимов Фарход', 'created_at' => "$today 09:10:00", 'status' => 'done'],
    ['id' => 3, 'ticket_label' => 'L-002', 'service' => 'Шартнома', 'ticket_type' => 'live',
     'client' => 'Хусейнов Бахтиёр', 'created_at' => "$today 09:40:00", 'status' => 'no_show'],
    ['id' => 4, 'ticket_label' => 'L-003', 'service' => 'Шартнома', 'ticket_type' => 'live',
     'client' => 'Мизоҷи санҷишӣ 2', 'created_at' => "$today 10:00:00", 'status' => 'waiting'],
    ['id' => 5, 'ticket_label' => 'L-004', 'service' => 'Ваколатнома', 'ticket_type' => 'live',
     'client' => 'Назарова Меҳрангез', 'created_at' => "$today 10:05:00", 'status' => 'waiting'],
    ['id' => 6, 'ticket_label' => 'B-002', 'service' => 'Тасдиқи нусхаи ҳуҷҷат', 'ticket_type' => 'booked',
     'client' => 'Мизоҷи санҷишӣ 3', 'created_at' => "$today 09:55:00", 'status' => 'waiting'],
    // Сабт барои санҷиши XSS: чунин «ном» набояд ҳамчун HTML иҷро шавад
    ['id' => 7, 'ticket_label' => 'B-003', 'service' => 'Тасдиқи нусхаи ҳуҷҷат', 'ticket_type' => 'booked',
     'client' => '<img src=x onerror="alert(\'XSS\')"> Тестов', 'created_at' => "$today 10:12:00", 'status' => 'waiting'],
];

// Ҷустуҷӯ бе назардошти регистр: mb_strtolower (strtolower кириллицаро паст намекунад)
$q = mb_strtolower(trim((string) ($_GET['q'] ?? '')));
if ($q !== '') {
    $tickets = array_values(array_filter(
        $tickets,
        fn (array $t): bool => str_contains(mb_strtolower($t['ticket_label'] . ' ' . $t['service'] . ' ' . $t['client']), $q)
    ));
}

echo json_encode($tickets, JSON_UNESCAPED_UNICODE);
