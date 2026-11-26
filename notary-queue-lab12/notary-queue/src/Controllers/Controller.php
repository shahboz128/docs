<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/** Асоси ҳамаи контроллерҳо: render / redirect / json / abort */
abstract class Controller
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View(BASE_PATH . '/views');
    }

    protected function render(string $template, array $data = [], int $status = 200, ?string $layout = 'layout'): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $this->view->render($template, $data, $layout);
    }

    /** PRG: баъд аз POST-и муваффақ — редиректи 302 ба саҳифаи GET (бо basePath) */
    protected function redirect(string $path): never
    {
        header('Location: ' . $this->view->url($path), true, 302);
        exit;
    }

    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Саҳифаи хато (404, 403, …) дар макети умумӣ ва анҷоми скрипт */
    protected function abort(int $code, string $message = ''): never
    {
        $this->render('errors/error', [
            'title'   => 'Хатои ' . $code,
            'code'    => $code,
            'heading' => $code === 404 ? 'Саҳифа ёфт нашуд' : 'Хато',
            'message' => $message,
        ], $code);
        exit;
    }
}
