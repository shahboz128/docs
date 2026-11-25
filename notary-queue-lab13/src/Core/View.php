<?php
declare(strict_types=1);

/** Шаблонҳо: views/<ном>.php дар дохили views/layout.php. */
final class View
{
    public function __construct(private string $dir) {}

    /** HTML-ро ҳимоя мекунад (XSS): ҳама маълумот аз $this->e() мегузарад */
    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function url(string $path, array $query = []): string
    {
        $query = array_filter($query, fn ($v) => $v !== '' && $v !== null);
        return $path . ($query ? '?' . http_build_query($query) : '');
    }

    public function render(string $template, array $data = [], bool $withLayout = true): string
    {
        $content = $this->capture($template, $data);
        if (!$withLayout) {
            return $content;
        }
        return $this->capture('layout', $data + ['content' => $content]);
    }

    public function partial(string $template, array $data = []): string
    {
        return $this->capture('partials/' . $template, $data);
    }

    private function capture(string $template, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $this->dir . '/' . $template . '.php';
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
