<?php
/**
 * Генерация WebP-копии загруженной обложки (GD).
 * Вызывается после move_uploaded_file; ошибки не фатальны —
 * без WebP сайт отдаст оригинал через <picture>-фолбэк.
 */
function make_webp_copy(string $image_path, int $quality = 82): bool {
    if (!function_exists('imagewebp') || !is_file($image_path)) {
        return false;
    }
    $ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $img = @imagecreatefromjpeg($image_path);
    } elseif ($ext === 'png') {
        $img = @imagecreatefrompng($image_path);
        if ($img) {
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
        }
    } else {
        return false;
    }
    if (!$img) {
        return false;
    }
    $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $image_path);
    $ok = imagewebp($img, $webp_path, $quality);
    imagedestroy($img);
    return (bool)$ok;
}
