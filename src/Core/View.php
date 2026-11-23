<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Moteur de templates PHP natif. Toute donnée dynamique est échappée via e().
 */
final class View
{
    public static function render(string $template, array $data = [], string $layout = 'layout'): void
    {
        $content = self::fetch($template, $data);
        echo self::fetch($layout, $data + ['content' => $content]);
    }

    public static function fetch(string $template, array $data = []): string
    {
        $__file = BASE_PATH . '/templates/' . $template . '.php';
        if (!is_file($__file)) {
            throw new \RuntimeException('Template introuvable : ' . $template);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $__file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
