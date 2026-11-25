<?php
declare(strict_types=1);

abstract class Controller
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View(BASE_PATH . '/views');
    }

    protected function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $this->view->render($template, $data);
    }

    /** PRG: баъд аз POST-и муваффақ — редиректи 302 ба саҳифаи GET */
    protected function redirect(string $path): never
    {
        header('Location: ' . $path, true, 302);
        exit;
    }

    protected function notFound(string $message = 'Саҳифа ёфт нашуд'): never
    {
        $this->render('errors/error', ['title' => 'Хатои 404', 'code' => 404, 'message' => $message], 404);
        exit;
    }
}
