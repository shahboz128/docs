<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * Шаблонҳо: views/<ном>.php дар дохили views/<макет>.php.
 * Шаблон дар контексти View иҷро мешавад: $this->e(), $this->url(), $this->partial() дастрасанд.
 */
final class View
{
    public function __construct(private string $dir) {}

    /** HTML-ро ҳимоя мекунад (XSS): ҳама маълумот аз $this->e() мегузарад */
    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Суроғаи пурра бо basePath: url('/tickets') → '/notary-queue/public/tickets'; $query → '?a=b' (холиҳо партофта мешаванд) */
    public function url(string $path = '/', array $query = []): string
    {
        $query = array_filter($query, fn ($v) => $v !== '' && $v !== null);
        return Router::basePath() . $path . ($query ? '?' . http_build_query($query) : '');
    }

    /**
     * Шаблонро мекашад ва дар макет мегузорад.
     * @param string|null $layout номи макет (views/<layout>.php); null — бе макет
     */
    public function render(string $template, array $data = [], ?string $layout = 'layout'): string
    {
        $content = $this->renderFile($template, $data);
        if ($layout === null || $layout === '') {
            return $content;
        }
        return $this->renderFile($layout, $data + ['content' => $content]);
    }

    /** Порча (views/partials/<ном>.php) */
    public function partial(string $template, array $data = []): string
    {
        return $this->renderFile('partials/' . $template, $data);
    }

    /** ob_start буферӣ мекунад, то HTML ҳамчун сатр баргардад (на ки фавран чоп шавад) */
    private function renderFile(string $template, array $data): string
    {
        $file = $this->dir . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Шаблон ёфт нашуд: {$template}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $file;
        } catch (Throwable $e) {
            ob_end_clean(); // буфери нимтамом ба корбар намеравад
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
