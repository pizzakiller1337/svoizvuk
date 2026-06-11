<?php
/**
 * Open Graph / Twitter-превью для шаринга в соцсетях и мессенджерах.
 * Страница может переопределить переменные ДО подключения:
 *   $og_title, $og_description, $og_image (абсолютный URL), $og_url
 */
$og_base        = 'https://www.svoizvuk.online';
$og_title       = $og_title       ?? 'Свой звук — магазин виниловых пластинок';
$og_description = $og_description ?? 'Винил с аудио-предпросмотром: послушайте пластинку до покупки. Оригинальные прессы, доставка по России.';
$og_image       = $og_image       ?? $og_base . '/assets/logo/android-chrome-512x512.png';
$og_url         = $og_url         ?? $og_base . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Свой звук">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_description) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($og_url) ?>">
    <meta name="twitter:card" content="summary">
    <meta name="description" content="<?= htmlspecialchars($og_description) ?>">
