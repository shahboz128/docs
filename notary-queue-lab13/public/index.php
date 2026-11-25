<?php
// public/index.php — front controller: ҳамаи дархостҳо аз ин ҷо мегузаранд.
// Сервер:  php -S localhost:8000 -t public
declare(strict_types=1);

// Агар файли статикӣ бошад (assets) ва сервери PHP бо скрипти роутер оғоз шуда бошад — худи файлро медиҳем
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    if (is_file($file) && basename($file) !== 'index.php') {
        return false;
    }
}

define('BASE_PATH', dirname(__DIR__));

// Бор кардани синфҳо: src/Core, src/Repositories, src/Controllers
spl_autoload_register(function (string $class): void {
    foreach (['Core', 'Repositories', 'Controllers'] as $dir) {
        $file = BASE_PATH . "/src/{$dir}/{$class}.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

// Танзимот: config.php (дар Git нест), вагарна config.example.php (қиматҳои пешфарзи XAMPP)
$configFile = BASE_PATH . '/config/config.php';
if (!is_file($configFile)) {
    $configFile = BASE_PATH . '/config/config.example.php';
}
$config = require $configFile;

ini_set('display_errors', $config['debug'] ? '1' : '0');
error_reporting(E_ALL);
Database::configure($config['db']);

// Ҳар истисноии нагирифташуда -> саҳифаи 500 (тафсилот — танҳо дар ҳолати debug)
set_exception_handler(function (Throwable $e) use ($config): void {
    error_log((string) $e);
    http_response_code(500);
    echo (new View(BASE_PATH . '/views'))->render('errors/error', [
        'title'   => 'Хатои дохилии сервер',
        'code'    => 500,
        'message' => $config['debug'] ? $e->getMessage() : 'Баъдтар кӯшиш кунед ё ба маъмур муроҷиат намоед.',
    ]);
});

// Ҷадвали роутҳо. /tickets/create пеш аз /tickets/{id} навишта шудааст.
$router = new Router();
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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

$found = $router->match($method, $path);
if ($found === null) {
    $isKnown = $router->pathExists($path);
    http_response_code($isKnown ? 405 : 404);
    echo (new View(BASE_PATH . '/views'))->render('errors/error', [
        'title'   => $isKnown ? 'Хатои 405' : 'Хатои 404',
        'code'    => $isKnown ? 405 : 404,
        'message' => $isKnown ? 'Ин амал бо ин усул иҷозат дода намешавад.' : 'Саҳифа ёфт нашуд.',
    ]);
    exit;
}

[[$class, $action], $params] = $found;
(new $class())->$action(...array_values($params));
