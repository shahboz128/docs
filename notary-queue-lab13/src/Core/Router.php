<?php
declare(strict_types=1);

/** Роутери хурд: GET/POST, параметри {id} (танҳо рақамҳо). */
final class Router
{
    /** @var array<int, array{string, string, array}> */
    private array $routes = [];

    public function get(string $path, array $handler): void  { $this->routes[] = ['GET', $path, $handler]; }
    public function post(string $path, array $handler): void { $this->routes[] = ['POST', $path, $handler]; }

    /** @return array{0: array, 1: array}|null  [handler, params] */
    public function match(string $method, string $path): ?array
    {
        foreach ($this->routes as [$m, $pattern, $handler]) {
            if ($m !== $method) {
                continue;
            }
            $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $pattern) . '$#';
            if (preg_match($regex, $path, $found)) {
                $params = [];
                foreach ($found as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = (int) $value;
                    }
                }
                return [$handler, $params];
            }
        }
        return null;
    }

    /** Оё ягон роут бо ҳамин роҳ (бо усули дигар) ҳаст? Барои 405. */
    public function pathExists(string $path): bool
    {
        foreach ($this->routes as [, $pattern]) {
            if (preg_match('#^' . preg_replace('#\{(\w+)\}#', '\d+', $pattern) . '$#', $path)) {
                return true;
            }
        }
        return false;
    }
}
