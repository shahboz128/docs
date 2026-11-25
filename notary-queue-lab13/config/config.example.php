<?php
// Нусха кунед:  cp config/config.example.php config/config.php   — config.php ба Git НАМЕРАСАД
// Ҳар қимат бо тағйирёбандаи муҳит (environment) иваз мешавад — барои сервер (лаб 16) қулай аст.
return [
    'db' => [
        // 'mysql' — ҳолати воқеӣ. 'sqlite' — ТАНҲО барои санҷишҳои автоматӣ (tests/), на барои истеҳсолот.
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host'   => getenv('DB_HOST') ?: '127.0.0.1',
        'port'   => (int) (getenv('DB_PORT') ?: 3306),
        'name'   => getenv('DB_NAME') ?: 'notary_queue',
        'user'   => getenv('DB_USER') ?: 'root',
        'pass'   => getenv('DB_PASS') ?: '',        // дар XAMPP парол барои root холӣ аст
        'path'   => getenv('DB_PATH') ?: '',        // танҳо барои sqlite
    ],
    // true — тафсилоти хатоҳоро нишон медиҳад (ТАНҲО ҳангоми коркард!)
    // Диққат: getenv('APP_DEBUG') ?: '1' хато аст — сатри '0' дар PHP «дурӯғ» ҳисоб мешавад ва ?: онро ба '1' иваз мекард.
    'debug' => (getenv('APP_DEBUG') === false ? '1' : getenv('APP_DEBUG')) === '1',
];
