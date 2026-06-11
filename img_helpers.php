<?php
/**
 * Помощники для адаптивных картинок.
 *
 * webp_source($url) — вернёт <source> с WebP-версией картинки,
 * если файл существует рядом с оригиналом; иначе пустую строку.
 * Использование:
 *   <picture>
 *     <?= webp_source($item['image_url']) ?>
 *     <img src="<?= htmlspecialchars($item['image_url']) ?>" ...>
 *   </picture>
 */
if (!function_exists('webp_source')) {
    function webp_source(string $url): string {
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $url);
        if ($webp === $url) {
            return '';
        }
        $path = __DIR__ . '/' . ltrim($webp, '/');
        if (!is_file($path)) {
            return '';
        }
        return '<source srcset="' . htmlspecialchars($webp) . '" type="image/webp">';
    }
}
