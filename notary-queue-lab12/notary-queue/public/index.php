<?php
// public/index.php — front controller: ҳамаи дархостҳо аз ин ҷо мегузаранд.
// Дар ин файл на HTML ҳаст, на маълумот: танҳо бор кардан, танзим ва ҷадвали роутҳо.
// Сервер:  php -S localhost:8000 -t public      (XAMPP: http://localhost/notary-queue/public/)
declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Controllers\ReportController;
use App\Controllers\ServiceController;
use App\Controllers\TicketController;
use App\Core\Database;
use App\Core\Router;
use App\Core\View;

// Бо `php -S`: файли статикӣ (css/js/расм)-ро худи сервер медиҳад
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    if (is_file($file) && basename($file) !== 'index.php') {
        return false;
    }
}

define('BASE_PATH', dirname(__DIR__));

// Автозагрузчик: Composer (vendor/autoload.php), вагарна запасной бо ҳамон қоидаи PSR-4: App\ → src/
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

// Танзимот: config.php (дар Git нест), вагарна config.example.php (қиматҳои пешфарзи XAMPP)
$configFile = BASE_PATH . '/config/config.php';
if (!is_file($configFile)) {
    $configFile = BASE_PATH . '/config/config.example.php';
}
$config = require $configFile;

ini_set('display_errors', $config['debug'] ? '1' : '0');
error_reporting(E_ALL);
Database::configure($config['db']);

$view = new View(BASE_PATH . '/views');

// Ҳар истисноии нагирифташуда -> саҳифаи 500 (тафсилот — танҳо дар ҳолати debug)
set_exception_handler(static function (Throwable $e) use ($config, $view): void {
    error_log((string) $e);
    http_response_code(500);
    echo $view->render('errors/error', [
        'title'   => 'Хатои 500',
        'code'    => 500,
        'heading' => 'Хатои дохилии сервер',
        'message' => $config['debug'] ? $e->getMessage() : 'Баъдтар кӯшиш кунед ё ба маъмур муроҷиат намоед.',
    ]);
});

// Ҷадвали роутҳо (мувофиқи ТТ). /tickets/create пеш аз /tickets/{id} навишта шудааст.
$router = new Router($view);
$router->get('/',                       [DashboardController::class, 'index']);
$router->get('/tickets',                [TicketController::class, 'index']);
$router->get('/tickets/create',         [TicketController::class, 'create']);
$router->post('/tickets/create',        [TicketController::class, 'store']);
$router->get('/tickets/{id}',           [TicketController::class, 'show']);
$router->get('/tickets/{id}/edit',      [TicketController::class, 'edit']);
$router->post('/tickets/{id}/edit',     [TicketController::class, 'update']);
$router->post('/tickets/{id}/cancel',   [TicketController::class, 'cancel']);
$router->post('/tickets/{id}/delete',   [TicketController::class, 'delete']);
$router->get('/services',               [ServiceController::class, 'index']);
$router->post('/services/create',       [ServiceController::class, 'store']);
$router->post('/services/{id}/delete',  [ServiceController::class, 'delete']);
$router->get('/reports/queue',          [ReportController::class, 'queue']);
$router->get('/api/tickets',            [TicketController::class, 'apiList']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
