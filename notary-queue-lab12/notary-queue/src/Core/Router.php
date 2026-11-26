<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Роутери хурд: GET/POST, параметри {name} (танҳо рақамҳо), 404 ва 405.
 * Ҷадвали роутҳоро public/index.php месозад; Router худ HTML намесозад — хатоҳоро View мекашад.
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, regex: string, handler: array{0: class-string, 1: string}}> */
    private array $routes = [];

    public function __construct(private View $view) {}

    /** @param array{0: class-string, 1: string} $handler [Контроллер::class, 'метод'] */
    public function get(string $path, array $handler): void  { $this->add('GET', $path, $handler); }
    public function post(string $path, array $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = ['method' => $method, 'pattern' => $path, 'regex' => self::toRegex($path), 'handler' => $handler];
    }

    /**
     * '/tickets/{id}/edit' → '#^/tickets/(?P<id>\d+)/edit$#'
     * Қисмҳои собит quote мешаванд, {name} → гурӯҳи номдор бо рақамҳо; пас '/tickets/abc' мувофиқ намеояд (404).
     */
    public static function toRegex(string $pattern): string
    {
        $parts = preg_split('#(\{\w+\})#', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $regex = '';
        foreach ($parts as $part) {
            $regex .= preg_match('#^\{(\w+)\}$#', $part, $m)
                ? '(?P<' . $m[1] . '>\d+)'
                : preg_quote($part, '#');
        }
        return '#^' . $regex . '$#';
    }

    /**
     * Роҳи асосии лоиҳа дар URL: '' (php -S, DocumentRoot=public) ё '/notary-queue/public' (XAMPP).
     * Аз SCRIPT_NAME гирифта мешавад — ҳамон роҳе, ки сервер то index.php тай кардааст.
     */
    public static function basePath(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php'); // Windows: '\\' → '/'
        $dir = dirname($script);
        return ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');
    }

    /** URI-и пурраро ба роҳи роутинг табдил медиҳад: бе ?query, бе basePath, бе '/'-и охирин */
    public static function normalizePath(string $uri): string
    {
        $path = rawurldecode((string) parse_url($uri, PHP_URL_PATH));
        $base = self::basePath();
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');
        return $path;
    }

    /** Дархостро иҷро мекунад: контроллер, ё саҳифаи 404/405 */
    public function dispatch(string $method, string $uri): void
    {
        $path = self::normalizePath($uri);
        $method = strtoupper($method);
        $allowed = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $found)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $allowed[$route['method']] = true; // роҳ ҳаст, усул не → 405
                continue;
            }
            $params = [];
            foreach ($found as $key => $value) {
                if (is_string($key)) {
                    $params[] = (int) $value;
                }
            }
            [$class, $action] = $route['handler'];
            (new $class())->$action(...$params);
            return;
        }

        if ($allowed !== []) {
            header('Allow: ' . implode(', ', array_keys($allowed)));
            $this->fail(405, 'Усули дархост иҷозат дода намешавад', 'Ин суроға усули ' . $method . '-ро қабул намекунад.');
            return;
        }
        $this->fail(404, 'Саҳифа ёфт нашуд', 'Чунин суроға вуҷуд надорад.');
    }

    private function fail(int $code, string $title, string $message): void
    {
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        echo $this->view->render('errors/error', ['title' => 'Хатои ' . $code, 'code' => $code, 'heading' => $title, 'message' => $message]);
    }
}
